#!/usr/bin/env bash
#we need to know where we're working from
DIR="${BASH_SOURCE%/*}"
if [[ ! -d "$DIR" ]]; then DIR="$PWD"; fi

# go get our color definitions
if [[ -z ${CENTRY+x} ]]; then
    #pull in our global vars
    . "${DIR}/globvars.sh"
fi

function createsymlink {
    local FROM=$1
    local TO=$2

    . "${DIR}/symlink.sh" "${1}" "${2}"
}


#Where's our stuff located?
CONTENTLOC=$(wp config get WP_CONTENT_DIR)
#printf "${CINFO}CONTENT_DIR is ${CONTENTLOC}.\n";
MUPLUGINLOC="${CONTENTLOC}/mu-plugins"
#make sure we actually have a mu-plugins directory
if [ -d "${MUPLUGINLOC}" ]; then
    for RESOURCE in $MUPLUGINLOC/*; do
      if [ -d "${RESOURCE}" ]; then
         #RESOURCE now is the full path to the directory
         #Let's make sure there are no trailing slashes. See ${parameter##word} at https://www.gnu.org/software/bash/manual/html_node/Shell-Parameter-Expansion.html
         DIRNAME="${RESOURCE%"${RESOURCE##*[!/]}"}"
         #Now we need just the name so get rid of everything before the last slash
         DIRNAME="${DIRNAME##*/}"
         #Do We already have a symlink that matches the directory?
         if [[ ! -L "${MUPLUGINLOC}/${DIRNAME}.php" ]]; then
            #Is there a php file in the directory that has the same name _as_ the directory?
            if [[ -f "${RESOURCE}/${DIRNAME}.php" ]]; then
                # we have a file that matches the name of the directory but isnt already a symklink. Create it
                createsymlink "${RESOURCE}/${DIRNAME}.php" "${MUPLUGINLOC}/${DIRNAME}.php"
            else
                # Do we have *ANY* php files in the directory?
                PHPCOUNT=$(ls -1q ${RESOURCE}/*.php | wc -l)
                #Is there one php file in the directory where we can safely assume is the one to symlink?
                if [[ $PHPCOUNT == 1 ]]; then
                    #oh, good. let's get its name
                    FILENAMEPATH=$(ls -1q ${RESOURCE}/*.php)
                    #now we need just the file name
                    FILENAME="${FILENAMEPATH##*/}"
                    # now that we have a file we can symlink, has it already been symlinked?
                    # _Technically_ we could skip this step because ./symlink will make sure it doesnt exist before creating
                    if [[ ! -L "${MUPLUGINLOC}/${FILENAME}" ]]; then
                        createsymlink "${FILENAMEPATH}" "${MUPLUGINLOC}/${FILENAME}"
                    else
                        printf "${CINFO}A symlink with the name of ${CBOLD}${DIRNAME}${CRESET}${CINFO} already exists in ${MUPLUGINLOC}. ${CBOLD}Skipping.${CRESET}\n"
                    fi
                else
                    printf "\n${CWARN}    WARNING!!!    ${CRESET}\n\n${CWORKING} We have a directory ${CBOLD}${DIRNAME}${CRESET} ${CWORKING}\n"
                    printf " inside of mu-plugins that does not contain a php file with the same name, AND has either zero\n"
                    printf " php files or multiple php files in the directory. I am therefore unable to determine which file should\n"
                    printf " be symlinked. Here is the listing of files in this directory.  Select the \"main\" file that \n"
                    printf "should be symlinked and add the following line: \n\n"
                    printf "./scripts/symlink.sh /app/web/wp-content/mu-plugins/${DIRNAME}/<correctfile>.php /app/web/wp-content/mu-plugins/<correctfile>.php\n\n"
                    printf "In /.platform/hooks.build.yaml in the Project Specific section and in lando.yaml\n"
                    printf "as a new line in the services.appserver.run section. Be sure to replace <correctfile> with the name\n"
                    printf "of the correct file to use.${CRESET}\n"
                fi
            fi
         else
            printf "${CINFO}A symlink with a name of ${CBOLD}${DIRNAME}${CRESET}${CINFO} already exists. ${CBOLD}Skipping.${CRESET}\n"
         fi
     elif [[ -L "${RESOURCE}" ]] && [[ ! -a "${RESOURCE}" ]]; then
        #test if the symlink is broken
        printf "${CWARN}Broken symlink!${CRESET}"
        printf "${CINFO} The symlink ${RESOURCE} is broken. Removing...${CRESET} "
        rm "${RESOURCE}"
        printf "${CBOLD}Removed.${CRESET}\n"
      fi
    done
fi

printf "${CRESET}"
