-- CREATE TABLE monthly_top_users (
--   mtu_year INT NOT NULL,
--   mtu_month INT NOT NULL,
--   user_id INT NOT NULL,
--   user_name VARCHAR(255) NOT NULL,
--   edit_count INT NOT NULL,
--   rank INT NOT NULL,

--   PRIMARY KEY (mtu_year, mtu_month, rank),
--   INDEX mtu_user_idx (user_id)
-- );

CREATE TABLE monthly_top_users (
  mtu_year INTEGER NOT NULL,
  mtu_month INTEGER NOT NULL,
  user_id INTEGER NOT NULL,
  user_name TEXT NOT NULL,
  edit_count INTEGER NOT NULL,
  rank INTEGER NOT NULL,

  PRIMARY KEY (mtu_year, mtu_month, rank)
);

CREATE INDEX mtu_month_idx ON monthly_top_users (mtu_year, mtu_month);
