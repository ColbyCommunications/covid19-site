#!/usr/bin/env bash
#all of our run steps
#         - "./scripts/digitalservice/lando-check-ssh-keys.sh"
#        - "./scripts/digitalservice/lando-project-set.sh"
#        - "./scripts/digitalservice/lando-platform-sync.sh"
#        - "cd /app && composer install"

#found out where we are so we can include the other files
DIR="${BASH_SOURCE%/*}"
#if BASH_SOURCE didn't return what we want, try PWD
if [[ ! -d "$DIR" ]]; then DIR="$PWD"; fi
#We're going to need some colors
if [[ -z ${CENTRY+x} ]]; then
    #pull in our global vars
    . "${DIR}/globvars.sh"
fi

#allow later calls to this file to bypass the question
if [[ ! -z $1 && "y" == "${1}" ]]; then
    RUNSETUP="${1}"
else
    printf "${CENTRY}Do you want to run the platform set-up steps? If this is the first\n"
    printf "time you are starting lando for this project, you should answer 'y'. [y/N]: ${CRESET}"
    read RUNSETUP
fi

#set to case insensitive
shopt -s nocasematch

if [[ "y" == "${RUNSETUP}" ]]; then
    printf "${CINFO}Beginning platform setup steps...${CRESET}\n"
    . "${DIR}/lando-check-ssh-keys.sh"
    . "${DIR}/lando-project-set.sh"
    printf "${CINFO}Beginning composer install...${CRESET}\n"
    cd /app && composer install
    . "${DIR}/lando-platform-sync.sh"
    printf "${CINFO}Platform should now be connected to this lando project.${CRESET}\n"
else
    printf "${CINFO}If you decide later you want to run these steps, use the command "
    printf "${CBOLD}lando platform-setup${CRESET}\n"
fi

#return it back to case sensitive
shopt -u nocasematch
