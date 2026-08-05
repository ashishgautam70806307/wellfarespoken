-- Phase 126: responsive desktop/mobile hero banner management.
-- Run once before using the updated Hero Banner admin page.

ALTER TABLE hero_banners
  ADD COLUMN IF NOT EXISTS desktop_image_url VARCHAR(500) NULL AFTER image_url,
  ADD COLUMN IF NOT EXISTS mobile_image_url VARCHAR(500) NULL AFTER desktop_image_url,
  ADD COLUMN IF NOT EXISTS show_content ENUM('Yes','No') NOT NULL DEFAULT 'Yes' AFTER image_alt,
  ADD COLUMN IF NOT EXISTS content_position ENUM('left','center','right') NOT NULL DEFAULT 'left' AFTER show_content,
  ADD COLUMN IF NOT EXISTS overlay_strength TINYINT UNSIGNED NOT NULL DEFAULT 58 AFTER content_position;

UPDATE hero_banners
SET desktop_image_url = image_url
WHERE (desktop_image_url IS NULL OR desktop_image_url = '')
  AND image_url IS NOT NULL
  AND image_url <> '';
