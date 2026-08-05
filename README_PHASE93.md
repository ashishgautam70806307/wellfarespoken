# Phase 93 - Student Navigation + Footer Restore + Dashboard Counts

Changes:
1. Frontend top navigation:
   - Practice dropdown renamed to Student.
   - Student dropdown now contains Admission and conditional Student Login/Dashboard.
   - Right CTA button changed from Admission to Student Login.
   - If student is logged in, CTA opens student-dashboard.php and shows student name.
   - If not logged in, CTA opens student-auth.php and shows Student Login.
   - CTA hover effect matched with nav hover style.

2. Learn English menu:
   - Learning Roadmap added back.
   - Duplicate links avoided.

3. Footer:
   - Learning Roadmap added back in Learn column.
   - Footer Student column contains Admission + Student Login.
   - Dynamic footer extra links from admin nav menus are supported.
   - Duplicate/blocked links are automatically skipped.

4. Navigation Menus admin:
   - Frontend now reads published extra header/footer links from nav_menus.
   - Duplicate default links and blocked AI links are skipped.
   - This keeps hard-coded important dropdown structure safe while allowing extra dynamic links.

5. Dashboard counts:
   - Counts are now robust against published values Yes/Y/1.
   - Counts ignore status_deleted where available.
   - Roadmap Practice and Faculty Profiles count cards added.
   - AI/practice-lab dashboard card removed.
