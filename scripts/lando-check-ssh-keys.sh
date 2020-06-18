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
    CHECKSSH="${1}"
else
    printf "${CENTRY}Would you like me to check your ssh keys on platform? If you have never done \n"
    printf "this before, you should select 'y' [y\\N]: ${CRESET}"
    read CHECKSSH
fi

#we dont want to have to worry about casing
shopt -s nocasematch
if [[ "y" == ${CHECKSSH} ]]; then

    # go get the fingerprints of the user's current ssh keys
    # 1. find all of the .pub keys in the /user/.ssh directory,
    # 2. create an md5 hash of the the keys : 2048 MD5:8a:b8:6c:de:d1:3a:ad:cf:ba:69:f2:19:c4:c4:28:73 gilzowp@mu-039068.dhcp.missouri.edu (RSA)
    # 3. Grab just the second column : MD5:8a:b8:6c:de:d1:3a:ad:cf:ba:69:f2:19:c4:c4:28:73
    # 4. Grab the fingerprint after the 'MD5:' : 8a:b8:6c:de:d1:3a:ad:cf:ba:69:f2:19:c4:c4:28:73
    # 5. Remove the colons (:) : 6b36e84a4a69a282dcc893fab02dc92d
    # The final resulting hash will match the format platform is using to store hashes
    # 6. Last, convert the multiline collection of hashes into a space separated string
    LOCALSSHKEYFINGERPRINTS=$(find /user/.ssh -type f -name '*.pub' -exec ssh-keygen -E md5 -lf {} \; | awk '{print $2}' | sed -E 's/MD5:(([a-z0-9:]+):)/\1/g' | sed 's/\://g' | paste -s -d' '  -)

    # go get our remote keys
    # 1. Return the ssh fingerprints from platform, but only the Fingerprint columm, returning in csv format
    # 2. Platform returns the column title, so use tail +2 to remove that header
    # 3. Last, convert the multiline collection of hashes into a space separated string
    REMOTESSHKEYFINGERPRINTS=$(platform ssh-keys --columns Fingerprint --format=csv --no-header 2>&1 | paste -s -d' '  -)

    MATCHINGHASH=""
    for HASH in ${LOCALSSHKEYFINGERPRINTS[@]}; do
        if [[ ${REMOTESSHKEYFINGERPRINTS} =~ "${HASH}" ]]; then
            MATCHINGHASH+={${HASH}}
            #we have a match, so no use continuing the loop
            break
        fi
    done

    if [[ -z "${MATCHINGHASH}" ]]; then
        printf "${CWORKING}It appears you do not currently have an ssh key on this machine associated \nwith your platform account.${CRESET}\n"
        . "${DIR}/lando-create-ssh-key.sh" y
    else
        printf "\n${CWORKING}It appears you already have a local ssh key associated with your \nplatform account. ${CBOLD}Skipping key set up.${CRESET}\n\n"
    fi
else
    printf "\n${CINFO}Skipping. If you need to check your keys at a later time, run ${CBOLD}lando platform-check-keys.${CRESET}\n\n"
fi

#return it back to case sensitive
shopt -u nocasematch