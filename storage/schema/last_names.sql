DROP TABLE IF EXISTS "last_names";

CREATE TABLE "last_names" (
    "id" integer PRIMARY KEY AUTOINCREMENT NOT NULL,
    "name" varchar NOT NULL,
    "rank" integer NOT NULL
);

CREATE INDEX IF NOT EXISTS last_names_rank_index ON "last_names" ("rank");