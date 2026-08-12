# Phase 162 Browser + Database Checklist

1. Open Admin Dashboard and confirm **Batch-wise Question Papers & Answer Keys** is immediately below the Dashboard header.
2. Confirm the `grid-4 admin-dashboard-links` quick-link grid is immediately below the paper section and before Security/Student Account/Director sections.
3. Confirm all Dashboard buttons fit their cards on desktop, tablet, 390px and 320px widths.
4. Confirm Upcoming Test Performance and **1st – 3rd Winners** are separate cards.
5. Create/prepare Batch A and Batch B and assign one Upcoming Test to each.
6. Set both tests Active. Confirm activating Batch B does not deactivate Batch A; only another Upcoming Test for the same batch should replace its active paper.
7. Open Upcoming Test Performance. Choose Batch A, then its test. Confirm Top 10 and winner cards show Batch A label.
8. Switch to Batch B. Confirm the test selector and Top 10 switch to Batch B.
9. With checked copies, confirm Top 10 ordering is marks DESC then earlier submission; Top 3 remains separately displayed.
10. Log in as a Batch A student. Confirm Batch A and Common/All-Batches Upcoming Tests can be shown/started, but Batch B’s paper cannot be started even by a forged POST.
11. Log in as a student with no batch relation. Confirm a batch-specific paper is denied with a clear message, while a Common/All-Batches paper can still be used.
12. Verify existing 12-hour (or configured) cross-paper anti-repeat gap still works after the batch check.
13. Verify Basic and Previous Test activation/start behavior is unchanged.
14. Clear Service Worker/site cache once after deploying Phase 162 and confirm cache name `v162`.
