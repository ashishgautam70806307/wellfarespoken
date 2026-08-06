# Phase 136 Audit Report

## Audit objective

Verify Phase 135 without adding design/features, identify real regressions, and repair only proven defects.

## Findings and actions

| Area | Finding | Action | Status |
|---|---|---|---|
| PHP runtime | `mbstring` was unavailable in the verification runtime and could cause fatal errors | Added compatible UTF-8 fallback functions | Fixed |
| Course detail | Older/incomplete records could emit undefined-key warnings | Added safe optional-field fallbacks | Fixed |
| Roadmap | Missing `level` key could emit a warning | Added safe Beginner fallback | Fixed |
| DB environment | Proxy host could incorrectly affect local/live selection | Proxy headers now require explicit trust | Fixed |
| Student auth | Account-only rate limiting did not fully cover distributed/account attacks | Added account plus IP limits | Fixed |
| Admission | Phone-only rate limit did not fully cover repeated requests | Added phone plus IP limits | Fixed |
| Weekly attempt creation | Parameterized `INTERVAL ? MINUTE` was unreliable on MySQL/MariaDB | Expiry is calculated in PHP and stored as a normal timestamp | Fixed |
| Weekly redirect | Selected test ID could be lost through login/redirect | Exact type/test ID/fragment preserved | Fixed |
| Weekly result | Started attempt could reach answer-review route | Started attempt now returns a controlled conflict response | Fixed |
| Weekly answer visibility | Expected-answer policy was not sufficiently tied to final attempt state | Requires submitted/checked state plus policy | Fixed |
| Exam room | Soft-deleted attempt was not explicitly rejected | Added soft-delete rejection | Fixed |
| Legacy exam attempt | Missing snapshot/current questions could produce an unusable room | Controlled safe failure added | Fixed |
| PWA | New regression build required cache invalidation | Cache version advanced to v136 | Fixed |

## Verified without a real MySQL server

- PHP page rendering and warning detection using a protected fixture PDO layer.
- Home responsive banners, heading visibility, slider next action, and reviews animation.
- Navigation desktop dropdown.
- Course card actions.
- Basic/Previous/Upcoming test selection and setup opening.
- Online Class admission batch/course preselection.
- Roadmap path/process rendering.
- Spoken Materials practice UI/API population with controlled fixture response.
- Gallery lightbox and next action.
- Mobile topbar, drawer, and horizontal-overflow checks.
- Local/live/proxy/CLI environment resolution.
- Static PHP/JS/CSS/schema/asset validation.

## Real database verification not possible in this build container

The PHP runtime reported no PDO drivers, and no MySQL/MariaDB server was installed. Consequently, the following remain conditional until the included checker is run on XAMPP/staging:

- Fresh SQL execution by MySQL/MariaDB itself.
- Student registration/login database writes.
- Admission insertion.
- Actual Weekly Test attempt creation, autosave, resume, submit, and result persistence.
- Roadmap database progress persistence.
- Spoken Materials progress persistence.
- Admin create/edit/publish/upload operations against real tables/files.

No claim of real-database PASS is made for these items.

## Overall decision

The code-level, template-level, configuration, browser-fixture, and static regression suites pass. Deployment should proceed first to localhost/staging, where `php tools\phase136-functional-check.php` must pass against the real database before production promotion.
