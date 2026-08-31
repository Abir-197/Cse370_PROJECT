STUDYVERSE FINAL ADMIN MODULE
================================

1. Import/run admin_database_patch.sql in phpMyAdmin on the studyverse database.
2. Put all PHP/CSS files in the same folder as the admin pages.
3. If your MySQL username/password/database name differs, edit admin_db.php.
4. Open admin_login.php.
5. Default supplied admin-ready SQL credentials:
   AdminID: A1
   Password: admin123

Features included:
- Special Admin login
- AdminID starts with A, max 8 chars
- Dashboard with all 8 requested cards
- All Course: insert/update/delete
- All Repository: insert/update/delete
- View Admin/User/Faculty/Student profiles
- Check All Admin/Faculty/Student/User
- Insert Admin/User
- Same AdminID restriction for user update/delete
- Admin profile update/delete
- Admin-to-admin response system
- Work Due system
- Report system for USER/GROUP/COURSE/REPOSITORY
- Report resolve/delete target
- Website performance statistics
- Basic PHP + MySQLi only
- NO JavaScript

Important:
The original schema has no report/message/work_due tables, so admin_database_patch.sql creates the minimum tables needed for those requested features.
Existing project files are not modified by this package.
