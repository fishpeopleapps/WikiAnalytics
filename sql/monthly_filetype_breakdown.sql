CREATE TABLE monthly_filetype_breakdown (
  mfb_year INT NOT NULL,
  mfb_month INT NOT NULL,
  file_type VARCHAR(50) NOT NULL,
  upload_count INT NOT NULL,
  total_bytes BIGINT NOT NULL,

  PRIMARY KEY (mfb_year, mfb_month, file_type)
);
