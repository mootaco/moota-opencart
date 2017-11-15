#!/bin/sh
# composer update --prefer-dist --no-dev

_name='mootapay.ocmod'
_DIR='upload'

rm -rf `pwd`/$_DIR/ > /dev/null 2>&1

mkdir $_DIR

cp -an ./{admin,catalog,system} $_DIR/

rm -f ./$_DIR/LICENSE > /dev/null 2>&1

rm -f $_name.zip > /dev/null 2>&1

zip -qr9 $_name.zip ./$_DIR

rm -rf `pwd`/$_DIR/ > /dev/null 2>&1

echo 'Bundle is here: "'`pwd`'/mootapay.ocmod.zip"'
