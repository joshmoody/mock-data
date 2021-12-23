DROP TABLE IF EXISTS "first_names";

CREATE TABLE "first_names" (
    "id" integer PRIMARY KEY AUTOINCREMENT NOT NULL,
    "name" varchar NOT NULL,
    "gender" varchar NOT NULL,
    "rank" integer NOT NULL
);

CREATE INDEX IF NOT EXISTS first_names_rank_index ON "first_names" ("rank");
CREATE INDEX IF NOT EXISTS first_names_gender_index ON "first_names" ("gender");