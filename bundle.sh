#!/bin/sh
# composer update --prefer-dist --no-dev

_DIR='mootapay.ocmod'

rm -rf `pwd`/$_DIR/ > /dev/null 2>&1

mkdir $_DIR
mkdir $_DIR/upload

cp -an ./{admin,catalog,system} $_DIR/upload/

[[ -f $_DIR.zip ]] && rm -f $_DIR.zip
zip -q9 -r $_DIR.zip ./$_DIR/

rm -rf `pwd`/$_DIR/ > /dev/null 2>&1
