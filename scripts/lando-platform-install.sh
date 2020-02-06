#!/usr/bin/env bash
set -e
# go get our color definitions
if [[ -z ${CENTRY+x} ]]; then
    DIR="${BASH_SOURCE%/*}"
    if [[ ! -d "$DIR" ]]; then DIR="$PWD"; fi

    #pull in our global vars
    . "${DIR}/globvars.sh"
fi
printf "${CINFO}Installing platform CLI...${CRESET}\n"
printf "${CINFO}Downloading the plaform cli binary..."
curl -fsSL -o /usr/local/bin/platform https://github.com/platformsh/platformsh-cli/releases/download/v3.37.1/platform.phar
printf "${CBOLD} done.${CRESET}\n${CINFO}Chmod'ing the binary..."
chmod ug+x /usr/local/bin/platform
printf " ${CBOLD}done.${CRESET}\n"
