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
	printf "a 'w' or 'd' when called so I can assign commands correctly. ${CBOLD}Exiting.${CRESET}\n"
	exit 1
fi

# have they specified an environment?
if [[ ! -z  $2 ]]; then
	ENV="$2"
fi
#no default because we want it to prompt the user for the environment


# have they specified a project? Should be rarely used
if [[ ! -z  $3 ]]; then
	PROJECTID="$3"
else
    PROJECTID=""
fi


printf "${CINFO}Retrieving sql file from platform...\n"
platform db:dump -f /app/platform.sql ${ENV:+-e "$ENV"} ${PROJECTID:+-p "$PROJECTID"}
DBDUMPSUCCESS=$?
if (( 0 != ${DBDUMPSUCCESS} )); then
    printf "${CWARN}Database Sync Failed!\n${CRESET}"
    printf "${CENTRY}The database export failed. See any errors above. ${CBOLD}Exiting.${CRESET}\n"
    exit 1
else
    printf "\n${CBOLD}Success!${CRESET}\n"
fi

printf "${CINFO}Beginning database import...${CRESET}\n"

if [[ "w" == "${CMS}" ]]; then
    wp db import /app/platform.sql
elif [[ "d" == "${CMS}" ]]; then
    mysql -h database -u drupal8 --password=drupal8 drupal8 < /app/platform.sql
fi
# else intentionally left out since we should have already exited if CMS isn't w|d

IMPORTSUCCESS=$?
if (( 0 != ${IMPORTSUCCESS} )); then
    printf "${CWARN}Database Import Failed!\n${CRESET}"
    printf "${CENTRY}The database import failed. See any errors above. ${CBOLD}Exiting.${CRESET}\n"
    exit 1
else
    printf " ${CBOLD}Success!${CRESET}\n"
    if [[ "d" == "${CMS}" ]]; then
        printf "${CINFO}Running drush ${CBOLD}cache-rebuild${CRESET}${CINFO} and ${CBOLD}updatedb${CRESET}${CINFO}...${CRESET}\n"
        drush -y cache-rebuild
        drush -y updatedb
    fi
fi

printf "${CINFO}Removing SQL file created from the import... ${CRESET}"
rm /app/platform.sql
REMOVESUCCESS=$?
if (( 0 != $REMOVESUCCESS )); then
    printf "\n${CWARN}File Removal FAILED!${CRESET}\n"
    printf "${CINFO}The removal of the /app/platform.sql file failed. ${CBOLD}You will need to remove this  file\n"
    printf "manually.${CRESET}${CINFO}Make sure you remove the  file and do ${CBOLD}not${CRESET}${CINFO} commit\n"
    printf "this file to your repository.${CRESET}\n"
    exit 1
else
    printf "${CBOLD}Removed.${CRESET}\n"
fi

# if [[ "w" == "$CMS" ]]; then
#   . "${DIR}/wp-shib-allow-local-auth.sh"
# fi