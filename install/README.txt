## Configuration ##

Apache server configuration:
 - rewrite module enabled 
 - php5 module enabled 
 - "Allowoverride All" instruction on the DOCUMENT_ROOT 
 - Allow www-data user write permission to taoDelivery/compiled, 
 taoDelivery/views/deliveryServer/resultServer/partialResults and 
 taoDelivery/views/deliveryServer/resultServer/received
 
 PHP server configuration:
  - required version >= 5.2.6, < 5.3 
  - register globals Off
  - short_tag_open On
  - magic_quotes_gpc Off
  - required extension: mysql, mysqli, curl, json, gd 
  
 MySql server cofiguration:
  - version >= 5.0  