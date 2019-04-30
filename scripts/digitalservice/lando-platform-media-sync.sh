#!/usr/bin/env bash

# go get our color definitions
if [[ -z ${CENTRY+x} ]]; then
    DIR="${BASH_SOURCE%/*}"
    if [[ ! -d "$DIR" ]]; then DIR="$PWD"; fi

    #pull in our global vars
    . "${DIR}/globvars.sh"
fi
shopt -s nocasematch
PLATFORMPRROJFILE="/app/.platform/local/project.yaml"
PLATFORM=$(which platform)

if [ ! -f "${PLATFORMPRROJFILE}" ]; then
    printf "${CENTRY}This lando project is not associated with a platform project. Would you like to do that now? [y\\N]: ${CRESET}"
    read SETPLATFORMPROJECT
    if [[ "y" == "${SETPLATFORMPROJECT}" ]]; then
        . "${DIR}/lando-project-set.sh y"
        PLATFORMPROJECTID=$($PLATFORM p:info id)
    else
        if [[ ! -z ${PRIMARY_DOMAIN+x}  ]]; then
            #ok, we'll use primary domain
            PLATFORMPROJECTID="${PRIMARY_DOMAIN}"
        else
            #well, crud. we dont have a projectid or a primary domain so they'll have to manage this on their own
            printf "${CINFO}Since this lando project isn't associated with a platform project, and PRIMARY_DOMAIN is not"
            printf " you'll need to select the correct project in the next step.${CRESET}\n"
            PLATFORMPROJECTID=""
        fi
    fi
else
    #they already have the project set
    PLATFORMPROJECTID=$($PLATFORM p:info id)
fi

#This assumes these locations are static. We *can* look up platform mounts but we'd still have to grep on "uploads"
PLATFORMMOUNTLOC="web/wp-content/uploads"
LANDOMOUNTLOC="${LANDO_WEBROOT}/wp-content/uploads"
#can we use the project id?
if [[ ! -z "${PLATFORMPROJECTID}" ]]; then
    PROJECT=" --project=${PLATFORMPROJECTID} -y"
else
    PROJECT=""
fi

printf "${CINFO}Starting media sync...${CRESET}\n"
#for some reason, passing in the parameters was causing platform to not see the parameters correctly
MOUNTCOMMAND="mount:download${PROJECT} -e master --target=${LANDOMOUNTLOC} --mount=${PLATFORMMOUNTLOC}"
#printf "${CBOLD}${PLATFORM} ${MOUNTCOMMAND}${CRESET}"
$PLATFORM $MOUNTCOMMAND

shopt -u nocasematch