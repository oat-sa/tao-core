DROP FUNCTION IF EXISTS generis_sequence_uri_provider;
CREATE FUNCTION generis_sequence_uri_provider (modelUri VARCHAR(255))
RETURNS VARCHAR(255)
DETERMINISTIC
READS SQL DATA
BEGIN
	DECLARE uri VARCHAR(255);
	INSERT INTO sequence_uri_provider (uri_sequence) VALUES ('');
	SELECT CONCAT(modelUri, 'i' , UNIX_TIMESTAMP(), FLOOR(RAND() * 10000), LAST_INSERT_ID()) INTO uri;
	DELETE FROM sequence_uri_provider;
	RETURN uri;
END;