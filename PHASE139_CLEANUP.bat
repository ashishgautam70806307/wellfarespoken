@echo off
setlocal
for %%F in (
  "assets\css\phase138-ui-system.css"
  "assets\css\phase138-ui-system.min.css"
  "assets\css\phase138-materials.css"
  "assets\css\phase138-materials.min.css"
  "assets\js\phase138-ui.js"
  "storage\phase136-page-smoke.log"
  "storage\phase139-page-smoke.log"
) do (
  if exist "%%~F" del /q "%%~F"
)
echo Phase 139 obsolete UI/materials assets removed.
echo Keep phase138-admin-login.css because Admin Login still uses it.
endlocal
