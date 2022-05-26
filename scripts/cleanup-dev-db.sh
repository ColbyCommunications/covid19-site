#!/usr/bin/env bash

DEV_HOSTNAME=$(echo $PLATFORM_ROUTES | base64 --decode | jq 'keys[0]' | tr -d \")

if [ "${PLATFORM_BRANCH}" != master ]; then
  echo "Running: wp search-replace 'https://${PRIMARY_DOMAIN}/' '${DEV_HOSTNAME}' --all-tables"
  wp search-replace "https://${PRIMARY_DOMAIN}/" "${DEV_HOSTNAME}" --all-tables
fi