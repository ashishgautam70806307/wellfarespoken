# Phase 43 - No-API Smart Spoken Practice Engine

This phase focuses on free/no-paid-API spoken English practice.

## Core idea
The system should not guess random translations. Admin uploads teacher-approved Hindi-English sentences. Students practice only verified content, so answers stay trustworthy without Google/OpenAI/DeepL APIs.

## Added / improved

- Admin content manager now explains the no-API practice engine.
- Translation pairs support extra fields:
  - sentence_type
  - difficulty_level
  - common_mistakes
  - teacher_hint
  - practice_priority
  - answer_match_mode
- Excel/CSV import supports better columns:
  - Hindi Sentence
  - English Sentence
  - Topic/Tense
  - Situation
  - Level
  - Accepted Answers
  - Explanation
  - Sentence Type
  - Common Mistakes
  - Teacher Hint
  - Match Mode
- Answer checker improved:
  - exact match
  - accepted answer match
  - smart similarity match
  - keyword match option
  - common mistake feedback
  - teacher-style correction
- Frontend practice room now shows topic + sentence type and teacher hint.
- Result card shows match type and score.
- Browser voice/mic remains free; no paid API required.

## After upload

1. Replace files.
2. Open `admin/system-check.php` once.
3. Open `admin/materials.php`.
4. Use `Load 1000 Use/Tense Sentences` or upload CSV/Excel.
5. Open `spoken-materials.php` and test practice.
