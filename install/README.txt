## Configuration ##

# 
# The full documentation to install or update TAO 
# is available at http://forge.tao.lu/projects/tao/wiki/Installation_and_Upgrading
#

Apache web server configuration:
 - rewrite module enabled 
 - php5 module enabled 
 - "Allowoverride All" instruction on the DOCUMENT_ROOT 
 
 PHP server configuration:
  - required version >= 5.2.6, < 5.3 
  - register globals Off
  - short_tag_open On
  - magic_quotes_gpc Off
  - required extension: mysql, mysqli, curl, json, gd, tidy, zip (or compiled with zip support on Linux)
  
 MySql server cofiguration:
  - version >= 5.0  
  
 
  
## INSTALL TAO ##
 - copy TAO distribution in your web folder (the DOCUMENT ROOT of your virtual host is recommended)
 - check the web sever permission.
 	For Apache the usual user www-data should be able to read, execute (and write for some folders listed at the end of this file)
 - In your web browser open the page http://your-host/tao/install/ and fill out the form
 
 
 
## UPDATE AN EXISTING TAO ##
  - backup the files from the folders listed at the end this file.
  - copy the TAO distribution over the previous.
  - copy the backed-up files in their respectives folders 
  - from the command line: 
  $ cd tao/install && php update.php version 
   where "version" is version to update to, for example to update from version 1.2 to 1.3 :
  $ cd tao/install && php update.php 1.3
   the process should be repeated for each intermediate version, for example to update from version 1.1 to 1.3 :
  $ cd tao/install && php update.php 1.2
  $ cd tao/install && php update.php 1.3
  
  - form the web browser(beta), you can do the same process as bellow but call the script:
  http://your-host/tao/install/update.php?version=version
   where "version" is version to update to, for example to update from version 1.2 to 1.3 :
  http://your-host/tao/install/update.php?version=1.3
   
 
 
 ## FOLDERS needs the write permission ##
 
  - During the install the config file is created inside them (you can change the permissions once the install is finished) 
 generis/common
 filemanager/includes
 tao/includes
 taoDelivery/includes
 taoGroups/includes
 taoItems/includes
 taoSubjects/includes
 taoTests/includes
 taoResults/includes
 wfEngine/includes
 
 - The following folder contains data created by the user or by the system. 
   (In case of an update, backup and then copy the content of the following folders inside their updated directory)
 filemanager/views/data
 taoItems/data (Recursively)
 taoDelivery/compiled
 tao/views/export
 tao/update/patches
 tao/update/bash
 taoDelivery/views/export
 taoGroups/views/export
 taoItems/views/export
 taoSubjects/views/export
 taoTests/views/export
 taoResults/views/export
 generis/data
 generis/data/cache
 version