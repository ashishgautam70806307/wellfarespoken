-- Phase 174 security hardening (data-only; no schema change)
-- AI secrets/endpoints are server environment only from Phase 174 onward.
UPDATE practice_settings SET setting_value='' WHERE setting_key IN ('openai_api_key','openai_endpoint');
