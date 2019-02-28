#!/usr/bin/env bash
/var/www/.platformsh/bin/platform db:dump -f /app/platform.sql
printf "retrieved sql file from platform.\n"
wp db import /app/platform.sql
printf "imported sql file into lando.\n"
rm /app/platform.sql
printf "removed sql file.\n"