#!/usr/bin/env bash
#Has DIR already been set up?
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
        PREVHOME="${DEFAULTHOME}"
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
    DORESETHOME="n"
    #see if HOME is already set to the "Correct" location for keys
    if [[ "${NEWHOME}" != "${HOME}" ]]; then
        #printf "\n${CWORKING}HOME is not set correctly. Fixing... ${CRESET}"
        OLDHOME="${HOME}"
        DORESETHOME="y"
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
            if [[ "y" == "${DORESETHOME}" ]]; then resethome "${OLDHOME}"; fi
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
    # the platform cli uses stderr for messaging during interactive operations. But we need to capture that interaction
    # since there isn't another way to see if the user created a key, or started to and canceled. We also can't find out
    # *which* key they might have created. So we're going to essentially route stderr to stdout and then stdout back to
    # stderr so we can capture the stderr but still display it.
    # not going to lie, I found this version on stack overflow: https://stackoverflow.com/a/45798436
    KEYADDRESULT=$(platform ssh-key:add 2> >(tee >(cat 1>&2)));
    KEYADDCMDSUCCESS=$?

    if (( 0 != $KEYADDCMDSUCCESS )); then
        #they said no adding a key. warn them
        skipmessage
        if [[ "y" == "${DORESETHOME}" ]]; then resethome "${OLDHOME}"; fi
        return 0;
    else
        #did they successfully add a key?
        KEYADDEDPATTERN="([^ ]+)\.pub has been successfully added to your Platform\.sh account"
        KEYEXISTSPATTERN="SSH key already exists in your"

        if [[ ${KEYADDRESULT} =~ ${KEYADDEDPATTERN} ]]; then
            #ok, they DID add a key! YAY! now we need to get the key
            KEYNAME="${BASH_REMATCH[1]}"
            NEWSSHKEY="${NEWHOME}/.ssh/${KEYNAME}"
            # we might need this later when we check vcs.missouri.edu
            export PLATFORMSSHKEY="${NEWSSHKEY}"
            # now, we want to create a config file in /var/www/.ssh to point it to the pub key in /user/.ssh
            printf "Host *\n    IdentityFile ${NEWSSHKEY}" > "${DEFAULTHOME}/.ssh/config"
            eval $(ssh-agent)
            ssh-add "${NEWSSHKEY}"

        elif [[ ! ${KEYADDRESULT} =~ ${KEYEXISTSPATTERN} ]]; then
            #ok, they didnt add a key, and it wasnt because they already had a key. Warn them?
            skipmessage
            if [[ "y" == "${DORESETHOME}" ]]; then resethome "${OLDHOME}"; fi
            return 0;
        fi
    fi

    #ok, we should now be done with the ssh stuff. reset HOME if we need to
    if [[ "y" == "${DORESETHOME}" ]]; then resethome "${OLDHOME}"; fi

    printf "\n${CINFO}If you set up a new ssh key on your account, you will be unable to sync the \n"
    printf "database or media files from the platform environment to this lando project \n"
    printf "until the master environment has been redeployed. Redploying the master \n"
    printf "environment will cause a momentary unresponsiveness from your ${CBOLD}production\n"
    printf "website${CRESET}${CINFO}. Do you want to redeploy the "
    printf "master environment? [y/N]:${CRESET}"
    read REDEPLOY

    if [[ "y" == "${REDEPLOY}" ]]; then
        #we need the project ID before we can try to redeploy
        PROJECTID=$(platform p:info id 2> /dev/null)
        PROJECTSUCCESS=$?
        if (( 0 == $PROJECTSUCCESS )); then
            printf "${CWORKING}Beginning redeploy process...${CRESET}\n"
            platform redeploy -p "${PROJECTID}" -e master
            printf "${CINFO}Checking to see if platform now recognizes your new key. This may take a minute...\n"
            #we need to grab the platform ssh address
            PLATFORMSSHADDRESS=$(platform ssh -p "${PROJECTID}" -e master --pipe)
            #@todo we should check to make sure we received an address
            MAXSSHWAITCOUNT=24
            SSHWAITCOUNTER=0
            SSHCONNECTIONTEST=1
            # After adding a key and redeploying the environment, it can _still_ take a little bit for the changes to
            # propagate everywhere. This can cause later steps to fail as they rely on being able to connect to platform
            # via ssh.
            while (( ${MAXSSHWAITCOUNT} > ${SSHWAITCOUNTER} )) && (( 0 != ${SSHCONNECTIONTEST} )); do
                sleep 5
                printf "* "
                ssh -q -o BatchMode=yes -o ConnectTimeout=10 "${PLATFORMSSHADDRESS}" exit
                SSHCONNECTIONTEST=$?
                SSHWAITCOUNTER=$((SSHWAITCOUNTER+1))
            done

            #now we need to see if we suceeded, or timed out
            if (( 0 == ${SSHCONNECTIONTEST})); then
                printf " ${CBOLD}Connected!${CRESET}\n"
            else
                printf "\n${CWARN}SSH Connection Test Timed Out!!!${CRESET}\n"
                printf "${CINFO}The connection test to platform timed out. You will be unable to complete the remaining steps.\n"
                printf "Please contact ${DIGITALSERVICECONTACT} with the following information:\n"
                printf "${CBOLD}Failure${CRESET}${CINFO}: SSH Key connection test.${CRESET}\n"
                printf "${CBOLD}Project ID${CRESET}${CINFO}: ${PROJECTID}${CRESET}\n"
                printf "${CBOLD}SSH Address${CRESET}${CINFO}: ${PLATFORMSSHADDRESS}${CRESET}\n"
                printf "\n${CWARN}Exiting...${CRESET}\n"
                exit 1
            fi


        else
            printf "${CWARN}Platform Project Associated${CRESET}\n"
            printf "${CINFO}You do not have a Platform project associated with this lando project. Other steps that "
            printf "depend on the project being associated will fail (db sync and media sync). Once lando has finished "
            printf "starting, please run\n${CBOLD}lando platform-set-project${CRESET}\nto associate a Platform project"
            printf " with this lando project. You can then run ${CBOLD}lando platform-media-sync${CRESET}${CINFO} to "
            printf "sync the database and media files.${CRESET}\n"
        fi
    else
        printf "${CWORKING}"
    fi
else
    skipmessage
fi

shopt -u nocasematch