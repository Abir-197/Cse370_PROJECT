STUDYVERSE FINAL ADMIN + GROUP MODULE

1. Import the original StudyVerse database SQL first.
2. Open phpMyAdmin -> studyverse -> Import/SQL and run admin_database_patch.sql.
3. Put all PHP/CSS files in the same folder as your existing PHP project pages.
4. Admin login: admin_login.php. AdminID must start with A and be <= 8 chars.
5. Dashboard: admin_dashboard.php
6. Course: allcourse.php -> insert_course.php / edit_course.php / delete_course.php -> course_delete_votes.php
7. Repository: allrepository.php -> insert_repository.php / edit_repository.php
8. Analytics: website_performance.php
9. Groups: mygroup.php -> create_group.php -> group_view.php. Add the My Group card/link to the existing student dashboard.
10. Admin group monitoring: group_admin.php
11. Reports: report_submit.php -> manage_reports.php

TECHNICAL LIMITS:
- Basic PHP + mysqli + HTML/CSS only. No JavaScript.
- The existing database has no historical semester grade table or faculty verification flag. The patch adds grade and enrollment_semester to course_student_took, and the performance page explicitly avoids inventing missing verification data.
- Course deletion uses multi-admin consensus: at least 2 admins, course owner must vote YES, and YES must be a majority.
- Course prerequisites are checked before insert; only existing course codes may be selected as prerequisites.
- Repository ID is unique and course code must already exist.
