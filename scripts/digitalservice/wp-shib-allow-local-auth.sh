#!/usr/bin/env bash
# Where are we?

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

# Was the question already asked?
if [[ (-n  $1) && ("y" == "$1" || "n" == "$1") ]]; then
	CHECKAUTHOPTION="$1"
else
  #then let's ask
  printf "\n%sDo you want me to check to see if the shibboleth plugin is configured to allow local authentication? [Y/n]:%s " "$CENTRY" "$CRESET"
  read CHECKAUTHOPTION
fi

if [[ 'y' == "$CHECKAUTHOPTION" ]] || [[ 'Y' == "$CHECKAUTHOPTION" ]]; then
  printf "\n%sChecking to see if local authentication is already enabled...%s " "$CINFO" "$CRESET"
  # if we're local, and this is a multisite, then we won't have performed the database update yet to the new local
  # domains. That means we need to use the --url parameter with the primary domain. This isn't an issue if we're on a
  # platform environment as the database update has already been performed
  if [[ -z ${PLATFORM_BRANCH+x} ]] && [[ -n ${MULTISITE+x} ]]; then
    LOCALMULTI='y'
  fi
  #multisite requires us to set the option via `site option` instead of option
  LOCALAUTH=$(wp ${MULTISITE:+site }option get "${WPSHIBAUTHONLYKEY}" ${LOCALMULTI:+ --url="$PRIMARY_DOMAIN"})
  LASTCOMMAND=$?

  #if wp was successful in retrieving the option, AND it's currently set to 1
  if (( 0 == $LASTCOMMAND )) && (( 1 == $LOCALAUTH )); then
    printf "\n%sPlugin is set for shibboleth authentication only. Adjusting...%s" "$CWORKING" "$CRESET"
    ADJUSTAUTH=$(wp ${MULTISITE:+site }option update "${WPSHIBAUTHONLYKEY}" 0 ${LOCALMULTI:+ --url="$PRIMARY_DOMAIN"})
    LASTCOMMAND=$?
    if (( 1 == $LASTCOMMAND)); then
      printf "\n%sUpdating Authentication Option Failed!%s\n" "$CWARN" "$CRESET"
      printf "%sUpdating the plugin option to allow local authentication failed. You will need to run the following command \n" "$CINFO"
      printf "in order to enable local authentication:\n\n%swp option update $WPSHIBAUTHONLYKEY 0%s\n\n" "$CBOLD" "$CRESET"
      printf "%sExiting.\%sn" "$CINFO" "$CRESET"
      exit 1
    else
      printf " %sDone.%s\n" "$CBOLD" "$CRESET"
    fi
  elif (( 1 == $LASTCOMMAND )); then
  printf "\n%sError retrieving option setting!%s\n\n" "$CWARN" "$CRESET"
  printf "%sThere was an error retrieving the local authentication option for the shibboleth plugin. You'll need to retrieve\n" "$CINFO"
  printf "it manually by running the following command:%s\n\n" "$CRESET"
  printf "%swp %soption get %s%s\n\n" "$CBOLD" "$MULTISITECLI" "$WPSHIBAUTHONLYKEY" "$CRESET"
  printf "%sExiting.%s\n" "$CINFO" "$CRESET"
  else
    printf "%sAlready set for local authentication.%s" "$CBOLD" "$CRESET"
  fi
fi