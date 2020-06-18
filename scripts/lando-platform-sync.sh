#!/usr/bin/env bash
# This script accepts four arguments:
# - should we prompt the user if they want to sync the database?
# - should we prompt the user to sync media?
# - the environment they want to sync from for the db (it's assumed the media will always sync from master)
#
# $PWD is the Path for the Working Directory,
# BASH_SOURCE is an array variable whose members are the source filenames
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

# Are we prompting them to sync the db?
if [[ ! -z $1 && "y" == "${1}" ]]; then
    UPDATEDB="${1}"
else
    printf "${CENTRY}Would you like to import the platform database (master environment) into this lando project? [y\\N]: ${CRESET}"
    read UPDATEDB
fi


# which CMS are we working with?
if [[ ! -z  $3 ]] && [[ ( "w" == "$3" ||  "d" == $3 ) ]]; then
	CMS=$3
else
	#@todo what do we do? Let's exit for now
	printf "\n${CWARN}Missing CMS Argument!${CRESET}\n"
	printf "${CINFO}I need to know which CMS I'm dealing with. Make sure this script is given\n "
	print "a 'w' or 'd' as a third parameter when called so I can instruct the db sync commands correctly. ${CBOLD}Exiting.${CRESET}\n"
	exit 1
fi

# have they specified an environment?
if [[ ! -z  $4 ]]; then
	ENV="$4"
else
    ENV=""
fi

shopt -s nocasematch
if [[ "y" == "${UPDATEDB}" ]]; then
    . "${DIR}/lando-platform-db-pull.sh" "$CMS"

    if [[ ! -z ${MULTISITE+x} ]]; then
        printf "\n${CENTRY}It appears this is a multisite. Would you like to update the domains in the database to the local lando domains? [y\\N]: ${CRESET}"
        read UPDATEMS
        if [[ "y" == "${UPDATEMS}" ]]; then
            PHP=$(which php)
            $PHP "${DIR}"/update-multisite-db.php
        else
            printf "\n${CINFO}You can update the database later by running ${CBOLD}lando platform-db-ms-update.${CRESET}\n"
        fi
    fi
else
    printf "\n${CINFO}You can import the database later by running ${CBOLD}lando platform-db-pull.${CRESET}\n"
fi

# Are we prompting them to sync media?
if [[ ! -z $2 && "y" == "${2}" ]]; then
    UPDATEMEDIA="${2}"
else
    printf "${CENTRY}Would you like to sync the media/upload files from platform into this lando project? [y\\N]: ${CRESET}"
    read UPDATEMEDIA
fi

if [[ "y" == "${UPDATEMEDIA}" ]]; then
    . "${DIR}/lando-platform-media-sync.sh"
else
    printf "\n${CINFO}You can sync the media at any time by running ${CBOLD}lando platform-media-sync.${CRESET}\n"
fi
shopt -u nocasematch