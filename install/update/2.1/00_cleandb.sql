DROP TABLE `_mask`;
DROP TABLE `user`;

ALTER TABLE `statements` DROP INDEX `idx_statements_subject` ;
ALTER TABLE `statements` DROP INDEX `idx_statements_predicate`;
ALTER TABLE `statements` DROP INDEX `idx_statements_object`;

ALTER TABLE `statements` ADD INDEX `k_sp` (subject(156), predicate(156));
ALTER TABLE `statements` ADD INDEX `k_po` (predicate(156), object(156));

OPTIMIZE TABLE `statements`;
FLUSH TABLE `statements`;