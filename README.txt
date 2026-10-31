HOW TO RUN (Windows XAMPP)
1. Open XAMPP Control Panel -> Start Apache and MySQL.
2. Copy this whole "club" folder into  C:\xampp\htdocs\
3. Open Chrome: http://localhost/club/
4. Login: admin@college.edu / admin123   (tables are created automatically in database club_db)
Watch the raw data: login as admin -> "Data Viewer" tab (live), or http://localhost/phpmyadmin -> club_db.
DB user/password: top of api.php (XAMPP default root / empty).
Flow: Register users -> admin sets roles (Users tab) -> head creates club -> faculty approves -> student joins -> head approves ->
head creates event -> faculty approves -> student registers -> head marks present -> student opens certificate (Print -> Save as PDF).

SAMPLE DATA (added automatically on first run)
Admin:    admin@college.edu    / admin123
Faculty:  faculty@college.edu  / pass123
Heads:    arjun@college.edu (Coding Club), priya@college.edu (Robotics Club) / pass123
Students: rahul@, sneha@, karthik@, divya@, ali@ college.edu / pass123
Includes clubs, pending requests, past + upcoming events, attendance (sneha has 2 certificates), budgets and announcements.
Both the Java app and the web app use the same database and the same logins.
