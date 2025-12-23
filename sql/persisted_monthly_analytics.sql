CREATE TABLE persisted_monthly_analytics (
  pma_id INTEGER PRIMARY KEY AUTOINCREMENT,
  pma_year INTEGER NOT NULL,
  pma_month INTEGER NOT NULL,
  pma_timestamp BLOB NOT NULL,

  page_count INTEGER NOT NULL,
  article_count INTEGER NOT NULL,
  edit_count INTEGER NOT NULL,
  user_count INTEGER NOT NULL,
  active_user_count INTEGER NOT NULL,
  file_count INTEGER NOT NULL,
  category_count INTEGER NOT NULL,
  template_count INTEGER NOT NULL,

  UNIQUE (pma_year, pma_month)
);

