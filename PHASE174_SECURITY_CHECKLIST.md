# Phase 174 Browser / Security Checklist

- [ ] Admin > System Check shows the new security rows green.
- [ ] Super Admin without MFA is redirected to Account Security when `ADMIN_REQUIRE_OWNER_MFA=true`.
- [ ] Enable MFA, logout, login and verify a TOTP code works.
- [ ] Database `admins.mfa_secret` starts with `enc:v1:` after enabling MFA.
- [ ] Practice Lab delete uses POST and never puts a CSRF token in the browser URL.
- [ ] A Materials-only admin cannot open Practice Lab Settings without `settings.manage`.
- [ ] An admin without `content.manage` cannot open `admin/online-classes.php`.
- [ ] `http://` meeting/social links are rejected or normalized to HTTPS as designed.
- [ ] `/tests/` and `/tools/` return 403 on the live domain.
- [ ] With AI disabled/unconfigured, local practice continues normally.
- [ ] With AI configured in `.env`, the allowlisted HTTPS endpoint works.
- [ ] Verify PHP warnings are not visible publicly on production.
- [ ] Rotate the live DB password because an older package contained it.
