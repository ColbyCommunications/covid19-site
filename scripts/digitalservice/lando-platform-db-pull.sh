#!/usr/bin/env bash
# Where are we?
if [[ -z ${DIR+x} ]]; then
    # No? ok, figure out where we are
    DIR="${BASH_SOURCE%/*}"
    if [[ ! -d "$DIR" ]]; then DIR="$PWD"; fi
fi

#Has our colors file already been pulled in
if [[ -z ${CENTRY+x} ]]; then
    #pull in our global vars
    . "${DIR}/globvars.sh"
fi

# which CMS are we working with?
if [[ ! -z  $1 ]] && [[ ( "w" == "$1" ||  "d" == $1 ) ]]; then
	CMS=$1
else
	#@todo what do we do? Let's exit for now
	printf "\n${CWARN}Missing CMS Argument!${CRESET}\n"
	printf "${CINFO}I need to know which CMS I'm dealing with. Make sure this script is given\n "
	print "a 'w' or 'd' when called so I can assign commands correctly. ${CBOLD}Exiting.${CRESET}\n"
	exit 1
fi

# have they specified an environment?
if [[ ! -z  $2 ]]; then
	ENV=" -e $2"
else
    ENV=""
fi

# have they specified a project?
if [[ ! -z  $3 ]]; then
	PROJECTID=" -p $3"
else
    PROJECTID=""
fi


printf "${CINFO}Retrieving sql file from platform...\n"
platform db:dump -f /app/platform.sql "${ENV}${PROJECTID}"
DBDUMPSUCCESS=$?
if (( 0 != ${DBDUMPSUCCESS} )); then
    printf "${CWARN}Database Sync Failed!\n${CRESET}"
    printf "${CENTRY}The database export failed. See any errors above. ${CBOLD}Exiting.${CRESET}\n"
    exit 1
else
    printf " ${CBOLD}Success!${CRESET}\n"
fi

printf "${CINFO}Beginning database import...${CRESET}\n"

if [[ "w" == "${CMS}" ]]; then
    wp db import /app/platform.sql
elif [[ "d" == "${CMS}" ]]; then
    mysql -h database -u drupal8 --password=drupal8 drupal8 < /app/platform.sql
    drush -y cache-rebuild
    drush -y updatedb
else
    printf ""
fi

IMPORTSUCCESS=$?
if (( 0 != ${IMPORTSUCCESS} )); then
    printf " ${CWARN}Database Import Failed!\n${CRESET}"
    printf "${CENTRY}The database import failed. See any errors above. ${CBOLD}Exiting.${CRESET}\n"
    exit 1
else
    printf " ${CBOLD}Success!${CRESET}\n"
    if [[ "d" == "${CMS}" ]]; then
        printf "${CINFO}Running drush cache-rebuild and updatedb...${CRESET}\n"
        drush -y cache-rebuild
        drush -y updatedb
    fi
fi

printf "${CINFO}Removing SQL file..."
rm /app/platform.sql
printf " ${CBOLD}Removed.${CRESET}\n"