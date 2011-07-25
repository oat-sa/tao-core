/*Change all activities ACL with INSTANCE_ACL_ROLE to INSTANCE_ACL_ROLE_RESTRICTED_USER_INHERITED*/
/*The affected property is PROPERTY_ACTIVITIES_ACL_MODE */
UPDATE statements SET "object" = 'http://www.tao.lu/middleware/taoqual.rdf#12748853932510' WHERE "predicate" = 'http://www.tao.lu/middleware/taoqual.rdf#127488549329046' AND "object" = 'http://www.tao.lu/middleware/taoqual.rdf#127488532261318';
