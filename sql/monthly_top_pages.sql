-- CREATE TABLE monthly_top_pages (
--   mtp_year INT NOT NULL,
--   mtp_month INT NOT NULL,
--   page_id INT NOT NULL,
--   page_title VARCHAR(255) NOT NULL,
--   edit_count INT NOT NULL,
--   rank INT NOT NULL,

--   PRIMARY KEY (mtp_year, mtp_month, rank),
--   INDEX mtp_page_idx (page_id)
-- );

CREATE TABLE monthly_top_pages (
  mtp_year INTEGER NOT NULL,
  mtp_month INTEGER NOT NULL,
  page_id INTEGER NOT NULL,
  page_title TEXT NOT NULL,
  edit_count INTEGER NOT NULL,
  rank INTEGER NOT NULL,

  PRIMARY KEY (mtp_year, mtp_month, rank)
);

CREATE INDEX mtp_month_idx ON monthly_top_pages (mtp_year, mtp_month);
