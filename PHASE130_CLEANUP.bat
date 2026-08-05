@echo off
setlocal
cd /d "%~dp0"
del /q "assets\css\phase129-admission.css" 2>nul
del /q "assets\css\phase129-admission.min.css" 2>nul
del /q "assets\css\phase129-design-system.css" 2>nul
del /q "assets\css\phase129-design-system.min.css" 2>nul
del /q "assets\css\phase129-materials.css" 2>nul
del /q "assets\css\phase129-materials.min.css" 2>nul
del /q "assets\css\phase129-online-class.css" 2>nul
del /q "assets\css\phase129-online-class.min.css" 2>nul
del /q "assets\css\phase129-roadmap-lesson.css" 2>nul
del /q "assets\css\phase129-roadmap-lesson.min.css" 2>nul
del /q "assets\css\phase129-weekly-test.css" 2>nul
del /q "assets\css\phase129-weekly-test.min.css" 2>nul
del /q "assets\js\phase129-ui.js" 2>nul
del /q "assets\js\phase129-weekly-test.js" 2>nul
echo Phase 130 obsolete frontend files cleaned.
endlocal
