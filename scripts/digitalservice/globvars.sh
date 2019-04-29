#!/usr/bin/env bash
### COLORS! ###
# Contains a collection of color types we can quickly use in our scripts
# color used when we ask for an entry
CENTRY=`tput setaf 3`
# color for when we are communicating that we are working on something
CWORKING=`tput setaf 6`
#The 'color' we use to reset the colors
CRESET=`tput sgr0`
#bold, duh
CBOLD=`tput bold`
#color we use for informational messages
CINFO=`tput setaf 2`
#color we use for warnings
CWARN=`tput setaf 7;tput setab 1`