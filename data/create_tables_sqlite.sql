CREATE TABLE firstnames (
  name varchar(15) NOT NULL,
  gender char(1) NOT NULL,
  rank integer(11) NOT NULL
);

CREATE INDEX rank ON firstnames (rank ASC);
CREATE INDEX gender ON firstnames (gender ASC);

CREATE TABLE lastnames (
  name varchar(15) NOT NULL,
  rank integer(11) NOT NULL
);

CREATE INDEX ranklastname ON lastnames (rank ASC);

CREATE TABLE streets (
  name varchar(50) NOT NULL
);

CREATE TABLE zipcodes (
  id integer PRIMARY KEY AUTOINCREMENT NOT NULL,
  zip varchar(10) NOT NULL,
  type varchar(20) NOT NULL,
  city varchar(50) NOT NULL,
  acceptable_cities text NOT NULL,
  unacceptable_cities text NOT NULL,
  state_code varchar(2) NOT NULL,
  state varchar(50) NOT NULL,
  county varchar(50) NOT NULL,
  timezone varchar(50) NOT NULL,
  area_codes varchar(50) NOT NULL,
  latitude float NOT NULL,
  longitude float NOT NULL,
  world_region varchar(50) NOT NULL,
  country varchar(50) NOT NULL,
  decomissioned tinyint(4) NOT NULL,
  estimated_population bigint(20) NOT NULL,
  notes varchar(50) NOT NULL
);
CREATE INDEX county ON zipcodes (county ASC);
CREATE INDEX zip ON zipcodes (zip ASC);
CREATE INDEX state ON zipcodes (state ASC);
CREATE INDEX state_code ON zipcodes (state_code ASC);