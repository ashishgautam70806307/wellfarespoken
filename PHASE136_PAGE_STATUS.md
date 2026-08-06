# Phase 136 Page-wise Status

## Executed fixture/browser checks

| Page/module | Render | Responsive/interaction | Status |
|---|---:|---:|---|
| Home | PASS | Banners, heading visibility, next control, two review animations PASS | PASS |
| Courses | PASS | All fixture cards expose visible actions; no overflow | PASS |
| Course Detail | PASS | No PHP warning with legacy optional fields | PASS |
| Online Class | PASS | Link/form context renders | PASS |
| Admission with batch | PASS | Batch and related course preselected; no overflow | PASS |
| Spoken Materials | PASS | Practice controls and mocked API population PASS | PASS |
| Learning Roadmap | PASS | Stages, nodes, and process path render | PASS |
| Roadmap Lesson | PASS | Route renders without PHP errors | PASS |
| Weekly Test Basic | PASS | Card/type/setup selection PASS | PASS |
| Weekly Test Previous | PASS | Type route renders | PASS |
| Weekly Test Upcoming | PASS | Type route renders | PASS |
| Student Login | PASS | Route renders | PASS |
| Student Register | PASS | Route renders | PASS |
| About | PASS | Route renders | PASS |
| Contact | PASS | Route renders | PASS |
| Gallery | PASS | Lightbox opens and Next works | PASS |
| Reviews | PASS | Route renders | PASS |
| Faculty Profile | PASS | Route renders | PASS |
| PWA Check | PASS | Route renders | PASS |
| Institute Login | PASS | Route renders | PASS |
| AI Teacher | PASS | Hidden/redirect behavior PASS | PASS |
| Student Dashboard guest guard | PASS | Redirects to authentication | PASS |
| Student Revision guest guard | PASS | Redirects to authentication | PASS |
| Mobile topbar/drawer | PASS | Visible, balanced width, no overflow | PASS |

## Conditional until real MySQL execution

| Flow | Status | Reason |
|---|---|---|
| Fresh canonical SQL execution | PENDING REAL ENVIRONMENT | No MySQL/MariaDB server in build container |
| Student registration/login persistence | PENDING REAL ENVIRONMENT | No `pdo_mysql` driver |
| Admission insert | PENDING REAL ENVIRONMENT | No `pdo_mysql` driver |
| Weekly attempt/autosave/submit/result persistence | PENDING REAL ENVIRONMENT | No `pdo_mysql` driver |
| Roadmap progress persistence | PENDING REAL ENVIRONMENT | No `pdo_mysql` driver |
| Materials progress persistence | PENDING REAL ENVIRONMENT | No `pdo_mysql` driver |
| Admin upload/publish mapping against real data | PENDING REAL ENVIRONMENT | Requires writable server/database |

The conditional rows must pass `tools/phase136-functional-check.php` and the manual checklist on XAMPP/staging before production deployment.
