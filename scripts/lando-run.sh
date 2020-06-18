#!/usr/bin/env bash
#all of our run steps
#         - "./scripts/lando-check-ssh-keys.sh"
#        - "./scripts/lando-project-set.sh"
#        - "./scripts/lando-platform-sync.sh"
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
if [[ ! -z $1 ]] && [[ "y" == "${1}" ]]; then
    RUNSETUP="${1}"
else
    printf "${CENTRY}Do you want to run the platform set-up steps? If this is the first\n"
    printf "time you are starting lando for this project, you should answer 'y'. [y/N]: ${CRESET}"
    read RUNSETUP
fi

# which CMS are we working with?
if [[ ! -z  $2 ]] && [[ ( "w" == "$2" ||  "d" == $2 ) ]]; then
	CMS=$2
else
	#@todo what do we do? Let's exit for now
	printf "\n${CWARN}Missing CMS Argument!${CRESET}\n"
	printf "${CINFO}I need to know which CMS I'm dealing with. Make sure this script is given\n "
	print "a 'w' or 'd' when called so I can assign commands correctly. ${CBOLD}Exiting.${CRESET}\n"
	exit 1
fi


#set to case insensitive
shopt -s nocasematch

if [[ "y" == "${RUNSETUP}" ]]; then
    #if HOME isn't set to the new location, store the old location,
    if [[ "${NEWHOME}" != "${HOME}" ]]; then
        OLDHOME="${HOME}"
        export HOME="/user"
    else
        OLDHOME="${HOME}"
    fi
    
    printf "${CINFO}Beginning platform setup steps...${CRESET}\n"
    # Make sure platform project is connected to this project
    . "${DIR}/lando-project-set.sh"
    # Make sure the user has ssh keys associated with their platform account
    . "${DIR}/lando-check-ssh-keys.sh"
    # Make sure the user can connect to vcs
    #. "${DIR}/lando-test-vcs.sh"
    #set the HOME directory back
    export HOME="${OLDHOME}"
    # Install the user's dependencies
    printf "${CINFO}Beginning composer install...${CRESET}\n"
    cd /app && composer install
    #now set it back AGAIN
    export HOME="${NEWHOME}"
    # Now import the database and media files from platform. 'n' because we want them to be prompted for db and media.
    # $2 to tell them which CMS, master is the initial environment to sync from
    . "${DIR}/lando-platform-sync.sh" n n "$2" master
    #and finally, set it back one more time
    export HOME="${OLDHOME}"
    printf "${CINFO}Platform should now be connected to this lando project.${CRESET}\n"
else
    printf "${CINFO}If you decide later you want to run these steps, use the command "
    printf "${CBOLD}lando platform-setup${CRESET}\n"
fi

#return it back to case sensitive
shopt -u nocasematch
