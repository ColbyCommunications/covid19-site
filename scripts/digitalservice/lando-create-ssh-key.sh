#!/usr/bin/env bash
#Figure out where we are
DIR="${BASH_SOURCE%/*}"
if [[ ! -d "$DIR" ]]; then DIR="$PWD"; fi
#Has our colors file already been pulled in
if [[ -z ${CENTRY+x} ]]; then
    #pull in our global vars
    . "${DIR}/globvars.sh"
fi

if [[ ! -z $1 && "y" == "${1}" ]]; then
    SKIPCHECK="${1}"
fi

function createsshkeys {
    printf "\n${CWORKING}Beginning ssh key generation...${CRESET}\n"
    platform ssh-key:add
    if [[ -f ~/.ssh/id_rsa.pub ]]; then
        mv ~/.ssh/id_rsa.pub /user/.ssh/platform.pub
        mv ~/.ssh/id_rsa /user/.ssh/platform
        echo "    IdentityFile /user/.ssh/platform" >> /etc/.ssh/ssh_config
    else
        printf "\n${CWARN}Key Files Missing!${CRESET}\n${CWORKING}The ssh key files I was expecting platform to create are"
        printf " not there. Try running this command again. If that doesn't fix it, contact digitalservice@missouri.edu.${CRESET}\n"
    fi
}

function skipmessage {
    printf "\n${CWORKING}Skipping ssh key set up. Be aware that without an associated ssh key file on platform, you will"
    printf " be unable to sync database and media files from platform. If you need to set up keys later, run "
    printf "${CBOLD}lando platform-setup-ssh-key${CRESET}.\n\n"
}

function resethome {
    if [[ ! -z $1 ]]; then
        PREVHOME="$1"
    else
        PREVHOME="/var/www"
    fi

    export HOME="${PREVHOME}"
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
    #see if HOME is already set to the "Correct" location for keys
    if [[ "/user" != "${HOME}" ]]; then
        printf "\n${CWORKING}HOME is not set correctly. Fixing... ${CRESET}"
        OLDHOME="${HOME}"
        export HOME="/user"
        printf "${CBOLD}Fixed.${CRESET}\n"
    fi


    KEYCOUNT=$(ls -lR "${HOME}"/.ssh/*.pub | wc -l)

    if (( $KEYCOUNT > 0 )) && [[ -z ${SKIPCHECK+x} ]]; then
        printf "${CWARN}Existing Keys Detected${CRESET}\n"
        printf "${CWORKING}It appears you have existing ssh keys. Would you like for me to check to see if you have already "
        printf "associated one of these keys with your platform account? [y/N]:${CRESET}"
        read CHECKACCOUNT
        if [[ "y" == "${CHECKACCOUNT}" ]]; then
            resethome "${OLDHOME}"
            . "${DIR}/lando-check-ssh-keys.sh" "y"
            # I don't like exiting but not sure how to restructure
            exit 0
        else
            printf "${CINFO}If you have existing keys ${CBOLD}other than id_rsa.pub${CRESET} that you want to associate "
            printf "then you will need to answer 'no' to the remaining questions, and then run the command ${CRESET}\n"
            printf "${CBOLD}lando platform ssh-key:add /user/<keyname>.pub${CRESET}\n"
            printf "replacing <keyname> with the name of the key you want to use.\n"
        fi
    fi

    printf "\n${CWORKING}Beginning ssh key generation...${CRESET}\n"
    platform ssh-key:add

    resethome "${OLDHOME}"

fi
shopt -u nocasematch