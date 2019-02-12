#!/bin/bash

#
# Simplesamlphp needs a snakeoil cert to decrypt shibboleth assertions. This script
# checks for existence of the cert in the directory defined in the default Digital
# Service simplesamlphp config and creates one if it is not there.
#
# Openssl needs access to a .rnd file to complete with out error. bashrc value is set
# in the .environment config.
#

if [ ! -f /app/simplesaml/cert/saml.crt ]
then
    rm /app/simplesaml/cert/.rnd
    touch /app/simplesaml/cert/.rnd
    openssl req -newkey rsa:2048 -new -x509 -days 3652 -nodes -out /app/simplesaml/cert/saml.crt -keyout /app/simplesaml/cert/saml.pem -subj '/CN=SAMLONLY-DONTUSE.missouri.edu/O=University of Missouri/C=US/ST=Missouri/L=Columbia'
fi
