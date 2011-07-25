CREATE TABLE "class_to_table"(
	"id" serial,
	"uri" VARCHAR(255) NOT NULL,
	"table" VARCHAR(64) NOT NULL,
	"topClass" VARCHAR(255) NOT NULL,
	PRIMARY KEY ("id")
)DEFAULT CHARSET=utf8;
CREATE INDEX "idx_class_to_table_uri" ON "class_to_table" ("uri");

CREATE TABLE "class_additional_properties" (
  	"class_id" int NOT NULL,
  	"property_uri" varchar(255) NOT NULL,
  	PRIMARY KEY ("class_id","property_uri")
)DEFAULT CHARSET=utf8;

CREATE TABLE "resource_to_table"(
	"id" serial,
	"uri" VARCHAR(255) NOT NULL,
	"table" VARCHAR(64) NOT NULL,
	PRIMARY KEY ("id")
)DEFAULT CHARSET=utf8;
CREATE INDEX "idx_resource_to_table_uri" ON "resource_to_table" ("uri");

CREATE TABLE "resource_has_class"(
	"resource_id" int NOT NULL,
	"class_id" int NOT NULL,
	PRIMARY KEY ("resource_id", "class_id")
)DEFAULT CHARSET=utf8;
