#!/bin/sh

if [ ! -z $1 ]
then
  export TARGET=$1

  function _link() {
    _dir=`dirname ${1}`
    _target="${TARGET}/$_dir"

    if [ ! -d $_target ]
    then
      echo "mkdir: $_target"
      echo "    path: $_target"
      echo
      mkdir -p $_target
    fi

    if [ ! -e $_target/${1} ]
    then
      # make it absolute symlink
      echo "link:"
      echo "    src: ${1}"
      echo "    dest: $_target"
      echo
      ln -s ${PWD}/${1} $_target
    fi
  }

  export -f _link

  find admin -maxdepth 10 -type f \( ! -iname ".*" \) \
    -exec bash -c '_link "$0"' {} \;
  find catalog -maxdepth 10 -type f \( ! -iname ".*" \) \
    -exec bash -c '_link "$0"' {} \;

  _link system/library/moota-pay
else
  echo "Usage: ./link.sh <TARGET_DIR>"
fi
