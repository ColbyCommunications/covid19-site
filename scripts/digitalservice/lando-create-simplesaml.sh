#!/usr/bin/env bash

################################################################################
# This script creates simplesaml directories on the local Lando instance if they
# don't already exist. If these files are not there, Drupal will give a "White
# Screen of Death".
#

LANDO_SAML_DIRS=(/app/simplesaml /app/simplesaml/logs /app/simplesaml/cert /app/simplesaml/data /app/simplesaml/tmp)

printf "Creating simplesaml directories... \n"
for i in "${LANDO_SAML_DIRS[@]}"
do
  if [ -d $i ]; then
    echo "$i exists!"
  else
    echo "'$i' is not found - creating"
    mkdir $i
  fi
done
