# Phase 42 - Roadmap, Translator, Weekly Test Upgrade

## Added
- Clear learning roadmap page
- Quick translator / corrector improvements with online Google translation endpoint when server internet/cURL is available
- Real weekly test system with three tabs:
  - Basic Test: public without login
  - Previous Weekly Test: public missed-test practice
  - Upcoming Weekly Test: active + login required + timed test
- Weekly Test Admin Manager:
  - create tests
  - upload CSV/XLSX questions
  - review submitted attempts
  - add marks and notes
- Student admin improvements:
  - active/inactive
  - soft delete
  - update password
  - notes/level update

## After upload
1. Open admin/system-check.php once.
2. Open Admin > Weekly Tests.
3. Create or upload questions.
4. Set Upcoming Weekly Test status to Active when students should attend.

## CSV/XLSX Columns
question_text, expected_answer, question_type, topic_name, level, marks, option_a, option_b, option_c, option_d

Question types: hindi_to_english, english_to_hindi, mcq, correction, short_answer.
