-- CREATE TABLE monthly_top_audio (
--   mta_year INT NOT NULL,
--   mta_month INT NOT NULL,
--   file_id INT NOT NULL,
--   listens INT NOT NULL,
--   seconds_listened BIGINT NOT NULL,
--   rank INT NOT NULL,

--   PRIMARY KEY (mta_year, mta_month, rank),
--   INDEX mta_file_idx (file_id)
-- );

CREATE TABLE monthly_top_audio (
  mta_year INTEGER NOT NULL,
  mta_month INTEGER NOT NULL,
  file_id INTEGER NOT NULL,
  listens INTEGER NOT NULL,
  seconds_listened INTEGER NOT NULL,
  rank INTEGER NOT NULL,

  PRIMARY KEY (mta_year, mta_month, rank)
);

CREATE INDEX mta_file_idx ON monthly_top_audio (file_id);
