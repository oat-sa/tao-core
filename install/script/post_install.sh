#!/bin/bash

SCRIPT_PATH=`dirname $0`
TAO_PATH="$SCRIPT_PATH"/../../../
cd $TAO_PATH
chmod g-w `cat ./tao/install/script/config_files.txt`
