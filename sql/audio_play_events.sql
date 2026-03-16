
-- CREATE TABLE audio_play_events (
--   ape_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
--   ape_user_id INT NULL,
--   ape_file_id INT NOT NULL,
--   ape_timestamp DATETIME NOT NULL,
--   ape_seconds_listened INT NOT NULL,
--   ape_completed TINYINT(1) NOT NULL,

--   INDEX ape_user_idx (ape_user_id),
--   INDEX ape_file_idx (ape_file_id),
--   INDEX ape_timestamp_idx (ape_timestamp)
-- );

CREATE TABLE audio_play_events (
  ape_id INTEGER PRIMARY KEY AUTOINCREMENT,
  ape_user_id INTEGER,
  ape_file_id INTEGER NOT NULL,
  ape_timestamp TEXT NOT NULL,
  ape_seconds_listened INTEGER NOT NULL,
  ape_completed INTEGER NOT NULL
);

CREATE INDEX ape_user_idx ON audio_play_events (ape_user_id);
CREATE INDEX ape_file_idx ON audio_play_events (ape_file_id);
CREATE INDEX ape_timestamp_idx ON audio_play_events (ape_timestamp);