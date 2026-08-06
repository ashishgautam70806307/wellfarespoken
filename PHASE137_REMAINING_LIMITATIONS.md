# Phase 137 — Remaining Limitations

The requested frontend and fixture-backed interaction repairs passed. The following items cannot be honestly marked as real-environment PASS in this build container because MySQL/MariaDB and `pdo_mysql` are unavailable:

- Real student registration/login persistence.
- Real admission insertion and duplicate handling.
- Real Basic/Previous/Upcoming attempt rows, autosave rows, submission, and result persistence.
- Real Roadmap progress persistence.
- Real Materials progress persistence.
- Admin upload/publish transactions against the user's database.

Use `tools/phase137-functional-check.php` on XAMPP/staging. The checker validates schema, mappings, published records, social settings, prepared insert statements, storage, and optional rollback-safe writes.
