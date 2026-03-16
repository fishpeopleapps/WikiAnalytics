-- CREATE TABLE persisted_monthly_analytics (
--   pma_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
--   pma_year INT NOT NULL,
--   pma_month INT NOT NULL,
--   pma_timestamp DATETIME NOT NULL,

--   page_count INT NOT NULL,
--   article_count INT NOT NULL,
--   edit_count INT NOT NULL,
--   user_count INT NOT NULL,
--   active_user_count INT NOT NULL,
--   file_count INT NOT NULL,
--   category_count INT NOT NULL,
--   template_count INT NOT NULL,
--   page_views INTEGER NOT NULL,
--   upload_bytes INTEGER NOT NULL,
--   content_bytes INTEGER NOT NULL,
--   word_count INTEGER NOT NULL,

--   pages_created INT NOT NULL,
--   edits_this_month INT NOT NULL,
--   uploads_this_month INT NOT NULL,
--   upload_bytes_this_month INTEGER NOT NULL,

--   new_users INT NOT NULL,
--   returning_users INT NOT NULL,
--   active_editors INT NOT NULL,

--   UNIQUE (pma_year, pma_month)
-- );

CREATE TABLE persisted_monthly_analytics (
  pma_id INTEGER PRIMARY KEY AUTOINCREMENT,
  pma_year INTEGER NOT NULL,
  pma_month INTEGER NOT NULL,
  pma_timestamp TEXT NOT NULL,

  page_count INTEGER NOT NULL,
  article_count INTEGER NOT NULL,
  edit_count INTEGER NOT NULL,
  user_count INTEGER NOT NULL,
  active_user_count INTEGER NOT NULL,
  file_count INTEGER NOT NULL,
  category_count INTEGER NOT NULL,
  template_count INTEGER NOT NULL,
  page_views INTEGER NOT NULL,
  upload_bytes INTEGER NOT NULL,
  content_bytes INTEGER NOT NULL,
  word_count INTEGER NOT NULL,

  pages_created INTEGER NOT NULL,
  edits_this_month INTEGER NOT NULL,
  uploads_this_month INTEGER NOT NULL,
  upload_bytes_this_month INTEGER NOT NULL,

  new_users INTEGER NOT NULL,
  returning_users INTEGER NOT NULL,
  active_editors INTEGER NOT NULL,

  UNIQUE (pma_year, pma_month)
);
