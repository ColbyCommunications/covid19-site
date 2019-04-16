#!/usr/bin/env bash
# Are we on a site using the MVC where we need to delete the cache on deploy
wp cli has-command mizzoumvc
MVC=$?

if (( 0 == $MVC )) ; then
    wp mizzoumvc clear-cache
fi