#!/usr/bin/env bash

printf "Installing NPM dependencies for Colby dependencies \n"

shopt -s extglob # Turns on extended globbing

export NVM_DIR="$HOME/.nvm"
[ -s "$NVM_DIR/nvm.sh" ] && . "$NVM_DIR/nvm.sh"  # This loads nvm

NPM_DIRS=`ls web/wp-content/@(plugins|themes)/colby-*/src/@(index.js)` # Saves it to a variable
for NPMDIR in $NPM_DIRS; do
  NPMDIR=`dirname $NPMDIR`
  NPMDIR_PRUNED=${NPMDIR:0:$((${#NPMDIR}-3))}
  cd $NPMDIR_PRUNED
  printf "Installing NPM dependencies for ${NPMDIR_PRUNED}... \n"
  npm install
  printf "Running build for ${NPMDIR_PRUNED}... \n"
  npm run build
  cd -
done

# install npm dependencies in the app root
npm install

# npm install
shopt -u extglob