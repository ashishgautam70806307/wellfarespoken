# BFS Student Dashboard — Backend Implementation Map

## Design rule
This folder is a high-fidelity frontend design using the approved BFS Super Admin component language and BFS public website palette. It does not claim server-side functionality. Every action marked in the UI must be connected to Laravel policies, validation, workflows, audit and database transactions.

## Core student access formula
Authenticated user + active account + active student profile + active enrollment + branch membership + record ownership + workflow status = allowed action.

## Required domain connections

| Student page | Primary backend domains | Key records |
|---|---|---|
| Dashboard | Students, Enrollments, Attendance, Fees, Exams, Notifications | KPI queries and targeted alerts |
| Profile | Students, Guardians, Addresses, Change Requests | Profile snapshots and approval history |
| ID Card | Documents | Template version, student snapshot, verification token |
| Documents | Documents, Admissions | Private versions and verification history |
| Course | Academics, Enrollments, Teacher Assignments | Course version, branch offering, batch membership |
| Materials | Learning Resources | Versioned resources and audience targeting |
| Assignments | Assignments, Submissions, Evaluations | Submission versions and score revisions |
| Timetable | Timetable, Conducted Sessions | Published timetable version and substitutions |
| Attendance | Attendance, Leave, Exam Eligibility | Locked entries, corrections and eligibility snapshots |
| Leave | Student Leave | Policy, balance, request, affected sessions and approval |
| Exam Forms | Examination | Eligibility snapshot, fee status and application |
| Online Exams | Examination | Attempt, autosave, timer and idempotent submission |
| Admit Card | Documents, Examination | Approved exam form and centre allocation |
| Results | Results | Approved marks, calculation snapshot and publication |
| Certificates | Documents | Immutable issue, reissue, revision and revocation |
| Fees | Fees | Fee snapshot, installment, payment, receipt and ledger |
| Placement | Placement | Profile, resume version, application, interview, offer |
| News/Events | CMS, Notifications | Targeting by branch/course/batch/session/student status |
| Downloads | CMS/Documents | Private/public authorization and expiring links |
| Requests | Workflow/Approvals | Typed student-service requests; not a helpdesk module |
| Notifications | Notifications | Recipient targeting, per-channel delivery and read state |
| Preferences | User Preferences/Consent | User-scoped settings and consent history |
| Security | Identity | Password, 2FA, device sessions and security audit |

## Non-negotiable rules
- Student can access only their own records.
- Enrollment, course version, batch membership and branch assignment are date-aware.
- Financial, attendance, marks, results and documents are never silently overwritten.
- Protected files require authorization and temporary signed URLs.
- UI badges and counts must come from official backend formulas, not hardcoded values.
- No Helpdesk/Ticket/SLA module is part of the current student portal.
