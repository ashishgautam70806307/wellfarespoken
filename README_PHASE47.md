# Phase 47 - Student Dashboard Progress + Revision System

Stable base: Phase 46.

## Added

- Professional student dashboard redesign.
- Progress percentage by level, practice count and correct rate.
- Daily practice plan with progress bar.
- Day streak calculation from spoken practice attempts.
- Wrong answer revision list.
- New frontend page: `student-revision.php`.
- Revision page includes Listen & Repeat button using browser speech synthesis.
- Weekly test result history remains visible in student dashboard.
- Material practice attempts now save `student_id` when student is logged in.
- System Check validates `material_practice_attempts.student_id`.

## Student Flow

1. Student logs in.
2. Opens Student Dashboard.
3. Starts Today’s Practice.
4. Wrong answers are saved automatically.
5. Student opens Revision Room to repeat mistakes.
6. Weekly test results appear after admin marking.

## No API

All progress tracking, answer history and revision are database-driven. No paid API is required.
