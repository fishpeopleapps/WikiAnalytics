-- Optional: clear existing data
DELETE FROM audio_play_events;
DELETE FROM monthly_audio_analytics;
DELETE FROM monthly_top_audio;
DELETE FROM monthly_filetype_breakdown;
DELETE FROM monthly_namespace_breakdown;
DELETE FROM monthly_top_pages;
DELETE FROM monthly_top_users;
DELETE FROM persisted_monthly_analytics;

-- Seed raw audio events
INSERT INTO audio_play_events
(ape_user_id, ape_file_id, ape_timestamp, ape_seconds_listened, ape_completed)
VALUES
(1, 10, '2026-02-01 10:15:00', 120, 0),
(2, 10, '2026-02-02 11:20:00', 250, 1),
(3, 12, '2026-02-03 09:10:00', 180, 0),
(1, 12, '2026-02-04 14:30:00', 300, 1),
(4, 15, '2026-02-05 16:45:00', 90, 0);

-- Seed monthly snapshot
INSERT INTO monthly_audio_analytics
(maa_year, maa_month, total_listens, total_seconds, completion_rate)
VALUES
(2026, 1, 120, 54000, 0.62),
(2026, 2, 210, 98000, 0.71);

-- Seed ranking data
INSERT INTO monthly_top_audio
(mta_year, mta_month, file_id, listens, seconds_listened, rank)
VALUES
(2026, 2, 10, 80, 30000, 1),
(2026, 2, 12, 60, 22000, 2),
(2026, 2, 15, 40, 15000, 3);

-- Seed file types
INSERT INTO monthly_filetype_breakdown
(mfb_year, mfb_month, file_type, upload_count, total_bytes)
VALUES
(2026, 2, 'image', 45, 125000000),
(2026, 2, 'audio', 12, 48000000),
(2026, 2, 'video', 4, 210000000),
(2026, 2, 'document', 20, 35000000);

-- Seed Namespaces
INSERT INTO monthly_namespace_breakdown
(mnb_year, mnb_month, namespace_id, page_count, edit_count)
VALUES
(2026, 2, 0, 320, 1450),   -- main/article namespace
(2026, 2, 1, 120, 320),    -- talk
(2026, 2, 2, 45, 210),     -- user
(2026, 2, 6, 85, 95);      -- file namespace

-- Seed top pages
INSERT INTO monthly_top_pages
(mtp_year, mtp_month, page_id, page_title, edit_count, rank)
VALUES
(2026, 2, 101, 'Satellite Communications Overview', 42, 1),
(2026, 2, 115, 'Orbital Mechanics Basics', 35, 2),
(2026, 2, 123, 'Launch Vehicle Comparison', 28, 3),
(2026, 2, 140, 'Mission Planning Guide', 21, 4),
(2026, 2, 150, 'Space Domain Awareness', 18, 5);

-- Seed Top Users
INSERT INTO monthly_top_users
(mtu_year, mtu_month, user_id, user_name, edit_count, rank)
VALUES
(2026, 2, 3, 'JSmith', 120, 1),
(2026, 2, 7, 'AKim', 98, 2),
(2026, 2, 11, 'RBrown', 75, 3),
(2026, 2, 5, 'LChen', 63, 4),
(2026, 2, 14, 'MGarcia', 51, 5);

-- Seed Monthly Stats
INSERT INTO persisted_monthly_analytics
(
pma_year, pma_month, pma_timestamp,
page_count, article_count, edit_count, user_count, active_user_count,
file_count, category_count, template_count, page_views,
upload_bytes, content_bytes, word_count,
pages_created, edits_this_month, uploads_this_month, upload_bytes_this_month,
new_users, returning_users, active_editors
)
VALUES
(
2026, 2, '2026-02-28 23:59:59',
1200, 950, 18450, 210, 48,
320, 120, 85, 54000,
210000000, 98000000, 560000,
35, 1450, 18, 65000000,
12, 26, 44
);