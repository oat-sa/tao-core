#!/bin/bash

BACKUPFILE="tao_db_backup_"`date +%Y%m%d`".sql"
mysqldump -uroot -p  --add-drop-database --add-drop-table  --databases taotrans_demo > "$BACKUPFILE" 

exit 0
