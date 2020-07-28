#!/usr/bin/env bash

printf "Installing NPM dependencies for Colby dependencies \n"

shopt -s extglob # Turns on extended globbing

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh"  # This loads nvm

# base-install
cd web/wp-content/themes/baseinstall
npm install
gulp
cd -

printf "Plugins... \n"
NPM_PLUGIN_DIRS=`ls web/wp-content/plugins/colby-*/src/@(index.js)` # Saves it to a variable
for NPMPLUGINDIR in $NPM_PLUGIN_DIRS; do
  NPMPLUGINDIR=`dirname $NPMPLUGINDIR`
  NPMPLUGINDIR_PRUNED=${NPMPLUGINDIR:0:$((${#NPMPLUGINDIR}-3))}
  cd $NPMPLUGINDIR_PRUNED
  printf "Installing NPM dependencies for ${NPMPLUGINDIR_PRUNED}... \n"
  npm install
  printf "Running build for ${NPMPLUGINDIR_PRUNED}... \n"
  npm run build
  cd -
done


printf "Sage Themes... \n"
NPM_THEME_DIRS=`ls web/wp-content/themes/colby-*/resources/assets/scripts/@(main.js)` # Saves it to a variable
for NPMTHEMEDIR in $NPM_THEME_DIRS; do
  NPMTHEMEDIR=`dirname $NPMTHEMEDIR`
  # NPMTHEMEDIR_PRUNED=${NPMTHEMEDIR:0:$((${#NPMTHEMEDIR}-3))}
  cd $NPMTHEMEDIR
  printf "Installing Composer dependencies for ${NPMTHEMEDIR_PRUNED}... \n"
  composer install
  composer dump-autoload
  printf "Installing NPM dependencies for ${NPMTHEMEDIR_PRUNED}... \n"
  npm install
  printf "Running build for ${NPMTHEMEDIR_PRUNED}... \n"
  npm run build:production
  cd -
done

# npm install
shopt -u extglob