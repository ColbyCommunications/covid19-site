#!/usr/bin/env bash
#Figure out where we are
DIR="${BASH_SOURCE%/*}"
if [[ ! -d "$DIR" ]]; then DIR="$PWD"; fi
#Has our colors file already been pulled in
if [[ -z ${CENTRY+x} ]]; then
    #pull in our global vars
    . "${DIR}/globvars.sh"
fi

function createsshkeys {
    printf "\n${CWORKING}Beginning ssh key generation...${CRESET}\n"
    platform ssh-key:add
    if [[ -f ~/.ssh/id_rsa.pub ]]; then
        mv ~/.ssh/id_rsa.pub /user/.ssh/platform.pub
        mv ~/.ssh/id_rsa /user/.ssh/platform
    else
        printf "\n${CWARN}Key Files Missing!${CRESET}\n${CWORKING}The ssh key files I was expecting platform to create are"
        printf " not there. Try running this command again. If that doesn't fix it, contact digitalservice@missouri.edu.${CRESET}\n"
    fi
}

function skipmessage {
    printf "\n${CWORKING}Skipping ssh key set up. Be aware that without an associated ssh key file on platform, you will"
    printf " be unable to sync database and media files from platform. If you need to set up keys later, run "
    printf "${CBOLD}lando platform-setup-ssh-keys${CRESET}.\n\n"
}

if [[ ! -z $1 && "y" == "${1}" ]]; then
    SETUPSSH="${1}"
else
    printf "${CENTRY}Would you like me to set up an ssh key to and associate it with your platform account? If you have never done this, you should select 'y' [y\\N]: ${CRESET}"
    read SETUPSSH
fi

#we dont want to have to worry about casing
shopt -s nocasematch
if [[ "y" == "${SETUPSSH}" ]]; then
    #before we begin, let's make sure they don't already have a platform.pub key
    if [[ -f /user/.ssh/platform.pub ]]; then
        printf "\n${CWARN}Platform.pub key already exists!${CRESET}\n"
        printf "\n${CINFO}A platform.pub key already exists on your computer. It is possible you already have an ssh key"
        printf " associated with your platform account. Would you like me to see if you alread have a local key associated"
        printf " with your platform account? [y/N]:${CRESET}"
        read CHECKKEYS

        if [[ "y" == "${CHECKKEYS}" ]]; then
            . "${DIR}/lando-check-ssh-keys.sh" "y"
        else
            # @todo we could ask if they want to reuse the platform.pub key or create a new one
            printf "${CINFO}Would you like to continue generating a new ssh key? Please note that if you continue, your "
            printf "platform.pub key ${CBOLD}will be overwritten${CRESET}${CINFO}. If you would like to continue, you "
            printf "must type in 'continue': ${CRESET}"
            read CONTINUE

            if [[ "continue" == "${CONTINUE}" ]]; then
                createsshkeys
            else
                skipmessage
            fi
        fi
    else
        createsshkeys
    fi
else
    skipmessage
fi

#reset casing
shopt -u nocasematch