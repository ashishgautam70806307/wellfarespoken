# Phase 92 - Navigation Cleanup

Changes:
1. Removed AI Teacher from main frontend navigation.
2. Removed AI Teacher links from footer and mobile navigation.
3. Removed AI Practice/AI Teacher link from admin sidebar when present.
4. Removed these links from Practice dropdown:
   - Spoken Practice
   - Learning Roadmap
   - Revision Room
5. Student dashboard AI Teacher checklist link changed to Courses.
6. No working feature files were deleted, to avoid breaking dependencies or old direct URLs.

Note:
- Files such as ai-teacher.php remain in the project for safety, but they are no longer linked from visible navigation.

Extra cleanup:
- Removed AI Teacher from mobile bottom navigation.
- Removed visible student dashboard revision links/buttons while keeping backend records intact.
