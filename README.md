# College Club Management Platform
1. Install JDK 17+ and Maven. Open XAMPP and start **MySQL**.
2. Windows: double-click `run.bat`  |  Mac/Linux: `sh run.sh`
   (or open the folder in IntelliJ/NetBeans as a Maven project and run `club.Main`).
3. Tables are created automatically in database `club_db` on first launch.
4. Log in: admin@college.edu / admin123. Others: click Register (new users are STUDENTs; admin promotes them in the Users tab).
DB settings (user/password/port) are at the top of `Db.java`. XAMPP default: root, no password.

Suggested demo flow: admin promotes a user to CLUB_HEAD and another to FACULTY -> head creates club -> faculty approves ->
student joins -> head approves member, creates event -> faculty approves -> student registers -> head marks present ->
student downloads certificate PDF.
