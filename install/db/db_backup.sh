#!/bin/bash

BACKUPFILE="tao_db_backup_"`date +%Y%m%d`
mysqldump -uroot -p  --add-drop-database --add-drop-table  --databases generis forum taogroups taoitems taoresults taosubjects taotests resultsmodule > "$BACKUPFILE" 

exit 0
