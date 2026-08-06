# Phase 139 Browser Test Checklist

Use the real localhost/staging database. Test after clearing the old service worker and performing Ctrl + F5.

## Viewports

- 360 x 800
- 390 x 844
- 412 x 915
- 430 x 932
- Tablet 768 x 1024
- Desktop regression 1366 x 768

## Roadmap Lesson

- Open `roadmap-lesson.php?id=3`.
- Confirm lesson title, progress, and three tabs fit without horizontal overflow.
- Open Practice and confirm four answer options appear as a two-by-two grid.
- Confirm question, options, and primary action are visible in one normal phone frame where text length permits.
- Confirm long lesson content scrolls inside the active panel.
- Confirm Learn -> Practice -> Finish and progress save still work.

## Spoken Practice

- Open `spoken-materials.php`.
- Confirm all four practice modes are visible together without horizontal mode scrolling.
- Confirm Lesson Group, Topic, Search, and Start controls are compact and readable.
- Start practice and confirm the page switches to the focused session frame.
- Confirm Practice Active / Change works.
- Test Read Question, Speak Now, Stop Auto, typing, Manual Check, Finish & Check, and Next.
- Confirm the typed answer is not covered by a leading icon.

## Weekly Test Center

- Open `weekly-test.php` as guest and as student.
- Confirm Basic, Previous, and Upcoming cards are visible together.
- Confirm compact action labels do not crop.
- Select each type and confirm setup opens immediately below.
- Verify guest name/mobile fields, student verified state, paper select, and Start Test.

## Weekly Exam Room

- Start a test through the real flow.
- Confirm topbar, timer, navigator, question, answer area, and four actions fit the phone workspace.
- Verify MCQ and text-answer questions.
- Verify Previous, Next, Report Issue, Submit, autosave, timer expiry, resume, and result.
- Confirm strict-mode safeguards remain active.

## Learning Roadmap

- Confirm process steps and four statistics fit in compact grids.
- Confirm stages and level cards are readable.
- Confirm lock/unlock/current/completed states remain distinguishable.
- Confirm Open Level and Complete Previous Level text is not clipped.

## Courses and universal buttons

- Open Courses and check View Details and Join Course on 360px width.
- Review hero, admission, contact, dashboard, result, revision, and footer buttons for cropped labels.

## Forms

- Test name, phone, email, password, search, select, date, time, and textarea controls.
- Confirm leading icon is small before typing and fades on focus/value.
- Confirm no text/icon overlap.
- Confirm iPhone Safari does not zoom when focusing normal fields.

## Header and drawer

- Confirm hamburger has no background or border.
- Confirm drawer from Phase 138 remains correct and all options/actions are visible.

## Footer social links

- Go to Admin -> Site Settings -> Social Media Links.
- Save one real URL at a time.
- Confirm Visible/Hidden preview updates after save.
- Confirm only configured icons appear in the public footer.
- Confirm each icon opens the correct profile in a new tab.
- Confirm blank URL hides the icon.

## Desktop regression

- Confirm Phase 139 does not alter desktop roadmap, test, practice, forms, menu, courses, or footer layout.
