#!/usr/bin/env bash
# go get our color definitions
if [[ -z ${CENTRY+x} ]]; then
    DIR="${BASH_SOURCE%/*}"
    if [[ ! -d "$DIR" ]]; then DIR="$PWD"; fi

    #pull in our global vars
    . "${DIR}/globvars.sh"
fi

printf "${CINFO}Retrieving sql file from platform...${CRESET}\n"
/var/www/.platformsh/bin/platform db:dump -e master -f /app/platform.sql
printf "${CINFO}Retrieved sql file from platform. Beginning import...${CRESET}\n"
wp db import /app/platform.sql
printf "${CINFO}Removing SQL file..."
rm /app/platform.sql
printf " ${CBOLD}Removed.${CRESET}\n"