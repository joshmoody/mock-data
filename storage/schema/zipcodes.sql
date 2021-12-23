DROP TABLE IF EXISTS "zipcodes";

CREATE TABLE "zipcodes" (
    "id" integer PRIMARY KEY AUTOINCREMENT NOT NULL,
    "zip" varchar NOT NULL,
    "type" varchar NOT NULL,
    "city" varchar NOT NULL,
    "acceptable_cities" text NOT NULL,
    "unacceptable_cities" text NOT NULL,
    "state_code" varchar NOT NULL,
    "state" varchar NOT NULL,
    "county" varchar NOT NULL,
    "timezone" varchar NOT NULL,
    "area_codes" varchar NOT NULL,
    "latitude" float NOT NULL,
    "longitude" float NOT NULL,
    "world_region" varchar NOT NULL,
    "country" varchar NOT NULL,
    decommissioned integer NOT NULL,
    "estimated_population" integer NOT NULL,
    "notes" varchar NOT NULL
);

CREATE INDEX IF NOT EXISTS zipcodes_county_index ON "zipcodes" ("county");
CREATE INDEX IF NOT EXISTS zipcodes_zip_index ON "zipcodes" ("zip");
CREATE INDEX IF NOT EXISTS zipcodes_state_index ON "zipcodes" ("state");
CREATE INDEX IF NOT EXISTS zipcodes_state_code_index ON "zipcodes" ("state_code");