#!/usr/bin/env bash
# $PWD is the Path for the Working Directory,
# BASH_SOURCE is an array variable whose members are the source filenames
# go get our color definitions
if [[ -z ${CENTRY+x} ]]; then
    DIR="${BASH_SOURCE%/*}"
    if [[ ! -d "$DIR" ]]; then DIR="$PWD"; fi

    #pull in our global vars
    . "${DIR}/globvars.sh"
fi

if [[ ! -z $1 && "y" == "${1}" ]]; then
    UPDATEDB="${1}"
else
    printf "${CENTRY}Would you like to import the platform database (master environment) into this lando project? [y\\N]: ${CRESET}"
    read UPDATEDB
fi

shopt -s nocasematch
if [[ "y" == "${UPDATEDB}" ]]; then
    . "${DIR}/lando-platform-db-pull.sh"

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