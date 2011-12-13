#!/bin/bash

SCRIPT_PATH=`dirname $0`
TAO_PATH="$SCRIPT_PATH"/../../../
cd $TAO_PATH

CONFIG_FILES=`cat ./tao/install/script/config_files.txt`
FILES=`cat ./tao/install/script/files.txt`

chown "$(whoami)":www-data $CONFIG_FILES $FILES
chmod ug+rwx $CONFIG_FILES $FILES
