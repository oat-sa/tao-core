CREATE TABLE `tmp_table` (
`subject` VARCHAR(256) DEFAULT NULL
);

INSERT INTO `tmp_table` (`subject`)
SELECT DISTINCT `subject` FROM `statements` WHERE
`predicate` = "http://www.w3.org/1999/02/22-rdf-syntax-ns#type"
AND (`object` = "http://www.tao.lu/Ontologies/taoFuncACL.rdf#Module"
OR `object` = "http://www.tao.lu/Ontologies/taoFuncACL.rdf#ACTION");

DELETE FROM `statements`
WHERE `subject`IN
(SELECT `subject` FROM `tmp_table`);

DROP TABLE `tmp_table`;