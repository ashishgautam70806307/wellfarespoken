# Phase 158 - Browser / Database Checklist

## A. Basic Test result
- [ ] Login as a student (or use the existing supported Basic guest flow).
- [ ] Submit a Basic Test with at least one right and one wrong answer.
- [ ] Open final result.
- [ ] Confirm every question shows **Your answer**.
- [ ] Confirm every uploaded `expected_answer` variant is visible.
- [ ] Confirm `A || B` accepted answers render as separate accepted-answer lines.

## B. Previous Test result
- [ ] Submit a Previous Test.
- [ ] Confirm question + student answer + uploaded master answer appear after final submit.

## C. Upcoming Test answer safety
- [ ] Start an Upcoming Test that has a future `ends_at`.
- [ ] Submit it.
- [ ] Confirm student answer is visible but the master answer is locked.
- [ ] Confirm another student can still take the test without the first student's result exposing the key.
- [ ] After `ends_at` passes, reload the result and confirm master answers unlock; alternatively finish teacher grading and complete/archive the paper, then confirm unlock.

## D. Student Dashboard history
- [ ] Open Student Dashboard after at least two final attempts.
- [ ] Expand **Questions & Answers** on one history card.
- [ ] Confirm the review loads once, with no page refresh.
- [ ] Confirm questions, student answers and marks are correct.
- [ ] Confirm Upcoming master answer follows the same release rule.
- [ ] Confirm **Open complete result** opens the same attempt.

## E. Upcoming score + Top 3
- [ ] Create an Upcoming paper linked to a batch.
- [ ] Have at least three logged-in students submit.
- [ ] Confirm each attempt stores `auto_score` and teacher grading stores `admin_score`/`checked` status as expected.
- [ ] Try **Complete + Rank Top 3** while test is still open: it must refuse.
- [ ] Try while one attempt is `started`: it must refuse.
- [ ] Try while any attempt remains `submitted` and not teacher-checked: it must refuse.
- [ ] Check all copies, close/end the paper and run **Complete + Rank Top 3**.
- [ ] Confirm exactly ranks 1, 2 and 3 are stored for the highest final scores.
- [ ] Login as #1: dashboard achievement is Gold.
- [ ] Login as #2: dashboard achievement is Violet/Purple.
- [ ] Login as #3: dashboard achievement is Parrot Green.
- [ ] Confirm a non-Top-3 student's dashboard has no rank color.

## F. Offline student paper / PDF
- [ ] Admin -> Weekly Tests -> Upcoming -> **Offline PDF**.
- [ ] Confirm institute logo/name, paper title, batch, timing/days, duration, total marks and schedule.
- [ ] Confirm Name, Mobile/Roll, Date and Score fields.
- [ ] Confirm all active questions are present in correct order.
- [ ] Confirm MCQ options appear when uploaded.
- [ ] Confirm student paper does NOT expose expected/master answers.
- [ ] Confirm watermark appears on printed pages.
- [ ] Use Print -> Save as PDF.
- [ ] Inspect every page: no question is split/cut between pages.
- [ ] With 25 short translation questions, confirm the compact layout is practical on one page where content length permits.
- [ ] With longer questions/options, confirm the page grows to additional pages instead of shrinking to unreadable text.

## G. Admin answer key
- [ ] Open **Answer Key** from Admin.
- [ ] Confirm every accepted answer variant appears.
- [ ] Confirm only an Admin with `tests.manage` can access the route.

## H. Regression
- [ ] Basic/Previous/Upcoming online test start still works.
- [ ] Autosave, timer expiry, final submit and result ownership still work.
- [ ] Mobile final-submit CTA from Phase 154 remains visible.
- [ ] Student Dashboard remains responsive at 320, 360, 390, 412, 768 and desktop widths.
