#!/bin/bash

##Load the script's location
pushd . > /dev/null
BASEDIR="${BASH_SOURCE[0]}"
if ([ -h "${BASEDIR}" ]); then
  while([ -h "${BASEDIR}" ]); do cd `dirname "$BASEDIR"`; 
  BASEDIR=`readlink "${BASEDIR}"`; done
fi
cd `dirname ${BASEDIR}` > /dev/null
BASEDIR=`pwd`;
popd  > /dev/null


##Aliases definition
alias phpunit='${BASEDIR}/vendor/phpunit/phpunit/phpunit'
alias dumpRouteLoader='pushd . > /dev/null && cd ${BASEDIR}/demo_dev && ./dumpRouteLoader.sh && popd > /dev/null'

