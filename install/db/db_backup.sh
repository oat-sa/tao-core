#!/bin/bash

BACKUPFILE="tao_db_backup_"`date +%Y%m%d`".sql"
mysqldump -uroot -p  --add-drop-database --add-drop-table  --databases generis forum taogroups taoitems taoresults taosubjects taotests > "$BACKUPFILE" 

exit 0
