#!/usr/bin/env bash

DEV_HOSTNAME=$(echo $PLATFORM_ROUTES | base64 --decode | jq 'keys[0]' | tr -d \")

if [ "${PLATFORM_BRANCH}" != master ]; then
  echo "Running: wp search-replace 'https://${PRIMARY_DOMAIN}/' '${DEV_HOSTNAME}' wp_posts wp_postmeta wp_options"
  wp search-replace "https://${PRIMARY_DOMAIN}/" "${DEV_HOSTNAME}" wp_posts wp_postmeta wp_options wp_yoast_indexable
  mysql -h database.internal -P 3306 -u user -e "TRUNCATE TABLE main.wp_yoast_indexable"
fi