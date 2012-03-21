DROP PROCEDURE IF EXISTS generis_proc_sequence_uri_provider;
CREATE PROCEDURE generis_proc_sequence_uri_provider(IN modelUri VARCHAR(255), OUT uri VARCHAR(255))
BEGIN
	INSERT INTO sequence_uri_provider (uri_sequence) VALUES ('');
	SELECT CONCAT(modelUri, UNIX_TIMESTAMP(), FLOOR(RAND() * 1000), LAST_INSERT_ID()) INTO uri;
	DELETE FROM sequence_uri_provider;
END;