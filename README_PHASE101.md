# Phase 101 - Weekly Test Final Security/UI + Reviews/Homepage Cleanup

Weekly Test:
1. Submit Test button is visible in right sidebar and wired to the custom modal.
2. Test can be submitted even if 0, 1, or many answers are filled.
3. Browser default confirm is not used; project modal is used.
4. Warning popup appears immediately when suspicious activity happens.
5. Warning count updates on screen and is saved for admin.
6. Basic Test: warnings only, no mark penalty.
7. Previous and Upcoming Tests: first warning is only a warning; from the second warning onward, marks are deducted from correct answer score.
8. Penalty is configurable from admin:
   - Penalty After Warnings
   - Penalty Per Warning
   - Warning Limit
9. Cancel Test option added with warning modal.
10. Fullscreen request removed to avoid browser close/X overlay.
11. Question font, answer box and spacing reduced.
12. Timer remains based on started_at + admin duration and cannot exceed duration.

Admin Weekly Test:
- Shuffle questions/options, warning limit and penalty controls are manageable.
- Review screen shows warning count, suspicious flag, penalty marks, token status, activity log and timing log.

Navigation:
- Learning Roadmap added back to mobile footer navigation.
- Reviews page/menu links removed from header/footer/mobile menus.
- reviews.php now redirects to homepage review section.

Reviews:
- Admin Review Manager enhanced with:
  - name
  - role/time
  - rating
  - source label
  - avatar initials
  - image upload
  - sort order
  - status
- Homepage review cards redesigned in Google-style moving card layout.

Universal frontend typography:
- Main frontend headings are capped at 2.25rem or smaller for premium spacing.
