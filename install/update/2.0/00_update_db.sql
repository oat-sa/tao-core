DROP TABLE cache;
DROP TABLE grouplocaluser;
DROP TABLE log_action_descr;
DROP TABLE log_actions;
DROP TABLE subscribee;
DROP TABLE subscriber;
DROP TABLE subscribersgroup;
DROP TABLE settings;
ALTER IGNORE TABLE statements ADD INDEX `idx_statements_object` ( `object` ( 128 ) );