# Phase 80 - Praise Feedback + 50-50 Practice CSV Data

Changes:
- Correct answer feedback now randomly shows:
  Good!, Amazing!, Excellent!, Very good!, Great job!
- Added 4 import-ready CSV files in /sql:
  1. verb_practice_50.csv
  2. has_have_uses_50.csv
  3. was_were_uses_50.csv
  4. can_could_uses_50.csv

How to import:
1. Open admin/roadmap.php.
2. For verbs, go to Word Meaning and create topic: Verb Forms.
3. For has/have, was/were, can/could, go to Uses / Modal Pattern and create these topics:
   - Use of Has / Have
   - Use of Was / Were
   - Use of Can / Could
4. In Import Excel CSV section, select the matching topic.
5. Upload the matching CSV file from /sql folder.
6. Open learning-roadmap.php or roadmap-lesson.php and test the lesson practice.

CSV format:
key, col1_correct_answer, col2_question_or_hindi, col3_example, col4_hindi_example, col5_variation, type_tag, notes, sort_order
