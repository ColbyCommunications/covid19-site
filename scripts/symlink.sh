#!/usr/bin/env bash
DESTINATION=$1
TARGET=$2
if [[ ! -e "$TARGET" || ! -L "$TARGET" ]] ; then
	echo "symlink for $TARGET doesn't exist. I'll attempt to create it."
	ln -s -f "$DESTINATION" "$TARGET"
else
	echo "symlink for $TARGET already exists. skipping."
fi