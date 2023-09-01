CREATE TABLE IF NOT EXISTS fixture_table_name (
	col_test_taker_id VARCHAR(16000),
	col_compilation_time INT,
	col_field_without_type VARCHAR(16000)
);

INSERT INTO fixture_table_name (
	   col_test_taker_id,
	   col_compilation_time,
	   col_field_without_type
) VALUES (
	   'http://nec-pr.docker.localhost/tao.rdf#i5f16bd028eb6e202ad4b5d184"f67e22',
	   1594828375,
	   '12345'
	),
	(
	   'http://nec-pr.docker.localhost/tao.rdf#i5f16bd028eb6e202ad4b5d43f67e24',
	   1594828388,
	   '33333'
	);