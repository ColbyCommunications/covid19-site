#!/usr/bin/env bash
### COLORS! ###
# Contains a collection of color types we can quickly use in our scripts
# color used when we ask for an entry
CENTRY=`tput setaf 3 -T ${TERM}`
# color for when we are communicating that we are working on something
CWORKING=`tput setaf 6 -T ${TERM}`
#The 'color' we use to reset the colors
CRESET=`tput sgr0 -T ${TERM}`
#bold, duh
CBOLD=`tput bold -T ${TERM}`
#color we use for informational messages
CINFO=`tput setaf 2 -T ${TERM}`
#color we use for warnings
CWARN=`tput setaf 7 -T ${TERM};tput setab 1 -T ${TERM}`

# Location in lando where the host HOME is mapped to inside of the lando container
# We need it mapped here because this is where the ssh keys are located
NEWHOME="/user"
#default location for home in lando
DEFAULTHOME="/var/www"
DIGITALSERVICECONTACT="webmaster@colby.edu"