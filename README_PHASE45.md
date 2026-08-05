# Phase 45 - Complete Weekly Test System

This phase focuses on Weekly Test as a real exam module.

## Added / Improved

- Admin Weekly Test Manager rebuilt for client-friendly workflow.
- Create/update Basic, Previous and Upcoming tests.
- Upcoming tests force student login.
- CSV/XLSX 30-question upload support.
- Manual question add/delete support.
- Student/guest submissions review panel.
- Admin marks and feedback save.
- Frontend real exam UI with timer.
- Auto-save answers every 20 seconds.
- Prevent duplicate submitted upcoming test per logged-in student.
- Student dashboard weekly result history.
- New result page: `weekly-result.php`.
- Sample test CSV: `sql/weekly_test_sample_30.csv`.

## Upload Flow

1. Replace files.
2. Open `admin/system-check.php`.
3. Open `admin/weekly-tests.php`.
4. Create/select a test.
5. Upload `sql/weekly_test_sample_30.csv` or your Excel/CSV.
6. Set upcoming test to Active.
7. Student logs in and opens `weekly-test.php`.

## CSV Columns

`question_text, expected_answer, question_type, topic_name, level, marks, option_a, option_b, option_c, option_d`

Supported question types:

- hindi_to_english
- english_to_hindi
- correction
- mcq
- short_answer
