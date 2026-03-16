
-- CREATE TABLE monthly_audio_analytics (
--   maa_year INT NOT NULL,
--   maa_month INT NOT NULL,
--   total_listens INT NOT NULL,
--   total_seconds BIGINT NOT NULL,
--   completion_rate FLOAT NOT NULL,

--   PRIMARY KEY (maa_year, maa_month)
-- );

-- this is global monthly metric for all audio files COMBINED
-- How much audio content is being consumed overall?

CREATE TABLE monthly_audio_analytics (
  maa_year INTEGER NOT NULL,
  maa_month INTEGER NOT NULL,
  total_listens INTEGER NOT NULL,
  total_seconds INTEGER NOT NULL,
  completion_rate REAL NOT NULL,

  PRIMARY KEY (maa_year, maa_month)
);
