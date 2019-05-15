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

function skipmessage {
    printf "\n${CWORKING}Skipping ssh key set up. Be aware that without an associated ssh key file\n"
    printf "on platform, you will be unable to sync database and media files from platform. If you \n"
    printf "need to set up keys later, run ${CBOLD}lando platform-setup-ssh-key${CRESET}.\n\n"
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
    printf "${CENTRY}Would you like me to create and associate an ssh key with your platform\n"
    printf "account? If you have never done this, you should select 'y' [y\\N]: ${CRESET}"
    read SETUPSSH
fi

#we dont want to have to worry about casing
shopt -s nocasematch
if [[ "y" == "${SETUPSSH}" ]]; then
    #see if HOME is already set to the "Correct" location for keys
    if [[ "/user" != "${HOME}" ]]; then
        #printf "\n${CWORKING}HOME is not set correctly. Fixing... ${CRESET}"
        OLDHOME="${HOME}"
        export HOME="/user"
        #printf "${CBOLD}Fixed.${CRESET}\n"
    fi


    KEYCOUNT=$(find "${HOME}"/.ssh/*.pub 2> /dev/null | wc -l)

    if (( $KEYCOUNT > 0 )) && [[ -z ${SKIPCHECK+x} ]]; then
        printf "${CWARN}Existing Keys Detected${CRESET}\n"
        printf "${CWORKING}It appears you have existing ssh keys on this machine. Would you like \n"
        printf "for me to check to see if you have already associated one of these keys with your \n"
        printf "platform account? [y/N]:${CRESET}"
        read CHECKACCOUNT
        if [[ "y" == "${CHECKACCOUNT}" ]]; then
            resethome "${OLDHOME}"
            . "${DIR}/lando-check-ssh-keys.sh" "y"
            # I don't like exiting but not sure how to restructure
            exit 0
        else
            printf "${CINFO}If you have existing keys ${CBOLD}other than id_rsa.pub${CRESET} that you want to associate \n"
            printf "then you will need to answer 'no' to the remaining questions, and then run the command ${CRESET}\n"
            printf "${CBOLD}lando platform ssh-key:add /user/<keyname>.pub${CRESET}\n"
            printf "replacing <keyname> with the name of the key you want to use.\n"
        fi
    fi

    printf "${CWORKING}Beginning ssh key generation...${CRESET}\n"
    platform ssh-key:add
    resethome "${OLDHOME}"

    printf "\n${CINFO}If you set up a new ssh key on your account, you will be unable to sync the \n"
    printf "database or media files from the platform environment to this lando project \n"
    printf "until the master environment has been redeployed. Redploying the master \n"
    printf "environment will cause a momentary unresponsiveness from your ${CBOLD}production\n"
    printf "website${CRESET}${CINFO}. Do you want to redeploy the \n"
    printf "master environment? [y/N]:${CRESET}"
    read REDEPLOY

    if [[ "y" == "${REDEPLOY}" ]]; then
        #we need the project ID before we can try to redeploy
        PROJECTID=$(platform p:info id 2> /dev/null)
        PROJECTSUCCESS=$?
        if (( 0 == $PROJECTSUCCESS )); then
            printf "${CWORKING}Beginning redeploy process...${CRESET}\n"
            platform redeploy -p "${PROJECTID}" -e master

        else
            printf "${CWARN}Platform Project Associated${CRESET}\n"
            printf "${CINFO}You do not have a Platform project associated with this lando project. Once lando has finished "
            printf "starting, please run\n${CBOLD}lando platform-set-project${CRESET}\nto associate a Platform project"
            printf " with this lando project.${CRESET}\n"
        fi
    else
        printf "${CWORKING}"
    fi
else
    skipmessage
fi

shopt -u nocasematch