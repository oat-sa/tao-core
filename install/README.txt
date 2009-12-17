## Configuration ##

Apache server configuration:
 - rewrite module enabled 
 - php5 module enabled 
 - "Allowoverride All" instruction on the DOCUMENT_ROOT 
 
 PHP server configuration:
  - required version >= 5.2.6, < 5.3 
  - register globals Off
  - short_tag_open On
  - magic_quotes_gpc Off
  - required extension: mysql, mysqli, curl, json, gd 
  
 MySql server cofiguration:
  - version >= 5.0  