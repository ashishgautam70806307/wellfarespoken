# Phase 82 - Frontend Polish + Faculty Dynamic Section

Added:
1. Dynamic Faculty module
   - New DB table: faculty_members
   - New admin page: admin/faculty.php
   - New frontend profile page: faculty-profile.php?id=...
   - Homepage moving faculty cards with image, experience, designation and qualification.
   - Card click opens profile details page.

2. Homepage cleanup
   - Duplicate feature/batch/gallery cards are filtered by title/data.
   - Faculty section added before reviews.
   - Cards get premium border + hover style.
   - Section spacing reduced and optimized.

3. Typography update
   - Added Inter + Outfit font setup.
   - CSS root variables:
     --font-sans: "Inter", ui-sans-serif, system-ui, -apple-system, sans-serif;
     --font-serif: "Outfit", "Inter", ui-sans-serif, system-ui, sans-serif;
     --font-mono: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
   - Reduced over-bold styling. Headings and main labels remain bold.

4. Topbar update
   - topbar-inner stays inside container.
   - Added dynamic admission marquee text from site setting:
     admission_marquee_text
   - Added dynamic social links:
     facebook_url, instagram_url, youtube_url, twitter_url, linkedin_url

5. Admin updates
   - Settings page includes new social + topbar marquee settings.
   - Sidebar has Faculty menu link.
   - Faculty manager supports add/edit/delete.

Check:
- PHP lint passed for changed files.
- No external API used.
- Font loaded from Google Fonts as already used in project.
