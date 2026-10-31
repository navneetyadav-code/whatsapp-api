-- Migration 016: Add Customizable Model per AI Provider Key
-- Allows users to customize the exact AI model per key instead of a fixed global provider model.

ALTER TABLE `ai_provider_keys`
  ADD COLUMN `model` varchar(120) DEFAULT NULL AFTER `api_key`;

UPDATE `ai_provider_keys` k
JOIN `ai_agent_settings` s ON k.user_id = s.user_id
SET k.model = CASE 
  WHEN k.provider = 'gemini' THEN s.gemini_model
  WHEN k.provider = 'groq' THEN s.groq_model
  WHEN k.provider = 'openrouter' THEN s.openrouter_model
  ELSE NULL
END
WHERE k.model IS NULL OR k.model = '';
