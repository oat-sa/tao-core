#!/bin/bash
set -e

FILE=./taoAdvancedSearch/scripts/tools/IndexPopulator.sh

if [ -f "$FILE" ]; then
  echo "##############################################################################"
  echo "#                          DEPRECATION WARNING!"
  echo "#"
  echo "# Please use this $FILE instead"
  echo "##############################################################################"

  sh $FILE

  exit 0;
fi

EXPORTER_LOCK_FILE="/tmp/.export.lock"
TAO_ROOT_PATH=$1
LIMIT=100
OFFSET=0;
CLASS=

rm -f $EXPORTER_LOCK_FILE && touch $EXPORTER_LOCK_FILE

while [ "$(awk 'FNR==2' ${EXPORTER_LOCK_FILE})" != 'FINISHED' ]; do
  LOCK_CLASS=$(awk 'FNR==1' ${EXPORTER_LOCK_FILE})

  if [ "$CLASS" != "$LOCK_CLASS" ]; then
    CLASS=$LOCK_CLASS
    OFFSET=0;
  fi

  php -d memory_limit=512M index.php "oat\tao\scripts\tools\index\IndexPopulator" \
  --limit $LIMIT \
  --offset $OFFSET \
  --lock $EXPORTER_LOCK_FILE \
  --class $CLASS

  OFFSET=$(($OFFSET + $LIMIT))
done