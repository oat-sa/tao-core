ALTER TABLE `extensions` ADD COLUMN `ghost` tinyint(1) NOT NULL DEFAULT 0;


UPDATE `tao`.`extensions` SET `version` = '1.2' WHERE `extensions`.`id` = 'tao' LIMIT 1; 
UPDATE `tao`.`extensions` SET `version` = '1.2' WHERE `extensions`.`id` = 'taoGroups' LIMIT 1;
UPDATE `tao`.`extensions` SET `version` = '1.2' WHERE `extensions`.`id` = 'taoItems' LIMIT 1;
UPDATE `tao`.`extensions` SET `version` = '1.2' WHERE `extensions`.`id` = 'taoResults' LIMIT 1; 
UPDATE `tao`.`extensions` SET `version` = '1.2' WHERE `extensions`.`id` = 'taoTests' LIMIT 1; 
UPDATE `tao`.`extensions` SET `version` = '1.2' WHERE `extensions`.`id` = 'taoDelivery' LIMIT 1; 
UPDATE `tao`.`extensions` SET `version` = '1.2' WHERE `extensions`.`id` = 'wfEngine' LIMIT 1;