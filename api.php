<?php
session_start();
$C = ['localhost', 'root', ''];            // host, user, password (XAMPP default)
$p0 = new PDO("mysql:host=$C[0]", $C[1], $C[2]);
$p0->exec("CREATE DATABASE IF NOT EXISTS club_db");
$db = new PDO("mysql:host=$C[0];dbname=club_db;charset=utf8mb4", $C[1], $C[2],
  [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
function q($s, $p = []) { global $db; $st = $db->prepare($s); $st->execute($p); return $st; }
foreach ([
 "CREATE TABLE IF NOT EXISTS users(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL,email VARCHAR(100) UNIQUE NOT NULL,password_hash VARCHAR(255) NOT NULL,role ENUM('STUDENT','CLUB_HEAD','FACULTY','ADMIN') NOT NULL DEFAULT 'STUDENT',department VARCHAR(100))",
 "CREATE TABLE IF NOT EXISTS clubs(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL,description TEXT,head_id INT NULL,status ENUM('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',FOREIGN KEY(head_id) REFERENCES users(id) ON DELETE SET NULL)",
 "CREATE TABLE IF NOT EXISTS memberships(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT,club_id INT,status ENUM('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',UNIQUE(user_id,club_id),FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,FOREIGN KEY(club_id) REFERENCES clubs(id) ON DELETE CASCADE)",
 "CREATE TABLE IF NOT EXISTS events(id INT AUTO_INCREMENT PRIMARY KEY,club_id INT,title VARCHAR(150),description TEXT,event_date DATETIME,reg_deadline DATETIME,capacity INT,status ENUM('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',FOREIGN KEY(club_id) REFERENCES clubs(id) ON DELETE CASCADE)",
 "CREATE TABLE IF NOT EXISTS registrations(id INT AUTO_INCREMENT PRIMARY KEY,event_id INT,user_id INT,attended BOOLEAN DEFAULT FALSE,UNIQUE(event_id,user_id),FOREIGN KEY(event_id) REFERENCES events(id) ON DELETE CASCADE,FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE)",
 "CREATE TABLE IF NOT EXISTS budget(id INT AUTO_INCREMENT PRIMARY KEY,club_id INT,type ENUM('FUNDING','SPONSORSHIP','EXPENSE'),amount DECIMAL(10,2),note VARCHAR(255),created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(club_id) REFERENCES clubs(id) ON DELETE CASCADE)",
 "CREATE TABLE IF NOT EXISTS announcements(id INT AUTO_INCREMENT PRIMARY KEY,club_id INT,title VARCHAR(150),body TEXT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(club_id) REFERENCES clubs(id) ON DELETE CASCADE)"
] as $s) $db->exec($s);
if (!q("SELECT 1 FROM users WHERE role='ADMIN' LIMIT 1")->fetch())
  q("INSERT INTO users(name,email,password_hash,role) VALUES('Admin','admin@college.edu',?,'ADMIN')", [hash('sha256', 'admin123')]);
q("UPDATE users SET password_hash=? WHERE email='admin@college.edu' AND LENGTH(password_hash)<>64", [hash('sha256', 'admin123')]);
// ---- sample data (inserted once; all sample users have password: pass123)
if (!q("SELECT 1 FROM users WHERE email='faculty@college.edu'")->fetch()) {
  $H = hash('sha256', 'pass123');
  foreach ([
  "INSERT IGNORE INTO users(name,email,password_hash,role,department) VALUES ('Dr. Meena Iyer','faculty@college.edu','$H','FACULTY','Computer Science'),('Arjun Kumar','arjun@college.edu','$H','CLUB_HEAD','Computer Science'),('Priya Sharma','priya@college.edu','$H','CLUB_HEAD','Electronics'),('Rahul Verma','rahul@college.edu','$H','STUDENT','Mechanical'),('Sneha Nair','sneha@college.edu','$H','STUDENT','Computer Science'),('Karthik R','karthik@college.edu','$H','STUDENT','IT'),('Divya S','divya@college.edu','$H','STUDENT','Electronics'),('Mohammed Ali','ali@college.edu','$H','STUDENT','Civil')",
  "INSERT INTO clubs(name,description,head_id,status) SELECT 'Coding Club','Competitive programming, hackathons and open-source projects',id,'APPROVED' FROM users WHERE email='arjun@college.edu'",
  "INSERT INTO clubs(name,description,head_id,status) SELECT 'Robotics Club','Build robots, drones and IoT gadgets',id,'APPROVED' FROM users WHERE email='priya@college.edu'",
  "INSERT INTO clubs(name,description,head_id,status) SELECT 'Photography Club','Photo walks, editing workshops and exhibitions',id,'PENDING' FROM users WHERE email='arjun@college.edu'",
  "INSERT IGNORE INTO memberships(user_id,club_id,status) SELECT u.id,c.id,'APPROVED' FROM users u,clubs c WHERE u.email IN ('rahul@college.edu','sneha@college.edu','karthik@college.edu') AND c.name='Coding Club'",
  "INSERT IGNORE INTO memberships(user_id,club_id,status) SELECT u.id,c.id,'APPROVED' FROM users u,clubs c WHERE u.email IN ('divya@college.edu','ali@college.edu','sneha@college.edu') AND c.name='Robotics Club'",
  "INSERT IGNORE INTO memberships(user_id,club_id,status) SELECT u.id,c.id,'PENDING' FROM users u,clubs c WHERE u.email IN ('rahul@college.edu','karthik@college.edu') AND c.name='Robotics Club'",
  "INSERT INTO events(club_id,title,description,event_date,reg_deadline,capacity,status) SELECT id,'Hackathon 2026','24-hour coding challenge',DATE_ADD(NOW(),INTERVAL 14 DAY),DATE_ADD(NOW(),INTERVAL 10 DAY),50,'APPROVED' FROM clubs WHERE name='Coding Club'",
  "INSERT INTO events(club_id,title,description,event_date,reg_deadline,capacity,status) SELECT id,'Python Workshop','Beginner friendly Python session',DATE_SUB(NOW(),INTERVAL 10 DAY),DATE_SUB(NOW(),INTERVAL 12 DAY),40,'APPROVED' FROM clubs WHERE name='Coding Club'",
  "INSERT INTO events(club_id,title,description,event_date,reg_deadline,capacity,status) SELECT id,'AI Talk','Guest lecture on applied AI',DATE_ADD(NOW(),INTERVAL 30 DAY),DATE_ADD(NOW(),INTERVAL 25 DAY),100,'PENDING' FROM clubs WHERE name='Coding Club'",
  "INSERT INTO events(club_id,title,description,event_date,reg_deadline,capacity,status) SELECT id,'Robo Race','Line-follower robot competition',DATE_ADD(NOW(),INTERVAL 21 DAY),DATE_ADD(NOW(),INTERVAL 18 DAY),30,'APPROVED' FROM clubs WHERE name='Robotics Club'",
  "INSERT INTO events(club_id,title,description,event_date,reg_deadline,capacity,status) SELECT id,'Arduino Basics','Hands-on Arduino workshop',DATE_SUB(NOW(),INTERVAL 5 DAY),DATE_SUB(NOW(),INTERVAL 7 DAY),25,'APPROVED' FROM clubs WHERE name='Robotics Club'",
  "INSERT IGNORE INTO registrations(event_id,user_id,attended) SELECT e.id,u.id,1 FROM events e,users u WHERE e.title='Python Workshop' AND u.email IN ('rahul@college.edu','sneha@college.edu')",
  "INSERT IGNORE INTO registrations(event_id,user_id,attended) SELECT e.id,u.id,0 FROM events e,users u WHERE e.title='Python Workshop' AND u.email='karthik@college.edu'",
  "INSERT IGNORE INTO registrations(event_id,user_id,attended) SELECT e.id,u.id,1 FROM events e,users u WHERE e.title='Arduino Basics' AND u.email IN ('divya@college.edu','ali@college.edu','sneha@college.edu')",
  "INSERT IGNORE INTO registrations(event_id,user_id,attended) SELECT e.id,u.id,0 FROM events e,users u WHERE e.title='Hackathon 2026' AND u.email IN ('sneha@college.edu','karthik@college.edu')",
  "INSERT INTO budget(club_id,type,amount,note) SELECT id,'FUNDING',20000,'College annual grant' FROM clubs WHERE name='Coding Club'",
  "INSERT INTO budget(club_id,type,amount,note) SELECT id,'SPONSORSHIP',5000,'Local tech company sponsor' FROM clubs WHERE name='Coding Club'",
  "INSERT INTO budget(club_id,type,amount,note) SELECT id,'EXPENSE',3500,'Hackathon prizes' FROM clubs WHERE name='Coding Club'",
  "INSERT INTO budget(club_id,type,amount,note) SELECT id,'FUNDING',30000,'College annual grant' FROM clubs WHERE name='Robotics Club'",
  "INSERT INTO budget(club_id,type,amount,note) SELECT id,'EXPENSE',12000,'Sensors and motors' FROM clubs WHERE name='Robotics Club'",
  "INSERT INTO announcements(club_id,title,body) SELECT id,'Hackathon registrations open','Register now, limited to 50 seats!' FROM clubs WHERE name='Coding Club'",
  "INSERT INTO announcements(club_id,title,body) SELECT id,'Robo Race rules published','Check the rules before building your bot.' FROM clubs WHERE name='Robotics Club'"
  ] as $s) $db->exec(str_replace('{H}', $H, $s));
}

$u = isset($_SESSION['uid']) ? (q("SELECT id,name,role FROM users WHERE id=?", [$_SESSION['uid']])->fetch() ?: null) : null;

if (isset($_GET['cert'])) {   // printable certificate (use Print -> Save as PDF)
  $r = $u ? q("SELECT us.name s,e.title,e.event_date d,c.name club FROM registrations r JOIN events e ON e.id=r.event_id JOIN clubs c ON c.id=e.club_id JOIN users us ON us.id=r.user_id WHERE r.id=? AND r.attended=1 AND r.user_id=?", [$_GET['cert'], $u['id']])->fetch() : null;
  if (!$r) { http_response_code(403); exit('Certificate not available'); }
  $h = fn($x) => htmlspecialchars($x);
  echo "<body style='font-family:Georgia,serif;text-align:center;border:8px double #234;margin:30px;padding:60px'><h1 style='letter-spacing:4px'>CERTIFICATE OF PARTICIPATION</h1><p>This is to certify that</p><h2 style='font-size:38px'>{$h($r['s'])}</h2><p>has successfully participated in</p><h2>{$h($r['title'])}</h2><p>organized by {$h($r['club'])} on {$h($r['d'])}</p><script>print()</script></body>";
  exit;
}
header('Content-Type: application/json');
$in = json_decode(file_get_contents('php://input'), true) ?: [];
$a = $in['a'] ?? '';
$g = function ($k) use ($in) { return trim((string)($in[$k] ?? '')); };
function out($d) { echo json_encode($d); exit; }
function fail($m) { http_response_code(400); out(['error' => $m]); }
function need($roles) { global $u; if (!$u || !in_array($u['role'], explode(',', $roles))) fail('Not allowed'); }
function myClub() { global $u; $r = q("SELECT id FROM clubs WHERE head_id=? AND status='APPROVED' LIMIT 1", [$u['id']])->fetch(); if (!$r) fail('You need an approved club first'); return $r['id']; }
$TB = ['users', 'clubs', 'memberships', 'events', 'registrations', 'budget', 'announcements'];
$dt = fn($s) => str_replace('T', ' ', $s) . ':00';

try {
 switch ($a) {
 case 'register':
  if (!$g('name') || !$g('email') || !$g('password')) fail('Fill all fields');
  q("INSERT INTO users(name,email,password_hash,department) VALUES(?,?,?,?)", [$g('name'), $g('email'), hash('sha256', $in['password']), $g('department')]);
  out(['ok' => 1]);
 case 'login':
  $r = q("SELECT * FROM users WHERE email=?", [$g('email')])->fetch();
  if (!$r || !(hash_equals($r['password_hash'], hash('sha256', $in['password'] ?? '')) || password_verify($in['password'] ?? '', $r['password_hash']))) fail('Invalid email or password');
  $_SESSION['uid'] = $r['id']; out(['u' => ['id' => $r['id'], 'name' => $r['name'], 'role' => $r['role']]]);
 case 'logout': session_destroy(); out(['ok' => 1]);
 case 'me': out(['u' => $u]);
 }
 if (!$u) fail('Please log in');
 $id = (int)$u['id']; $S = $u['role'] == 'STUDENT'; $H = $u['role'] == 'CLUB_HEAD';
 switch ($a) {
 case 'list':
  $t = $in['t'] ?? '';
  if ($t == 'reports') { need('CLUB_HEAD,FACULTY,ADMIN'); out(['multi' => [
   'Student Leaderboard' => q("SELECT u.name student,COUNT(*) events_attended FROM registrations r JOIN users u ON u.id=r.user_id WHERE r.attended GROUP BY u.id ORDER BY 2 DESC LIMIT 10")->fetchAll(),
   'Club Activity' => q("SELECT c.name club,(SELECT COUNT(*) FROM memberships m WHERE m.club_id=c.id AND m.status='APPROVED') members,(SELECT COUNT(*) FROM events e WHERE e.club_id=c.id) events,(SELECT COUNT(*) FROM registrations r JOIN events e ON e.id=r.event_id WHERE e.club_id=c.id AND r.attended) attendance FROM clubs c")->fetchAll(),
   'Budget Balance' => q("SELECT c.name club,SUM(CASE WHEN b.type='EXPENSE' THEN -b.amount ELSE b.amount END) balance FROM budget b JOIN clubs c ON c.id=b.club_id GROUP BY c.id")->fetchAll()]]); }
  if ($t == 'db') { need('ADMIN'); $tb = $in['table'] ?? 'users'; if (!in_array($tb, $TB)) fail('Bad table');
   $rows = q("SELECT * FROM `$tb` ORDER BY id DESC LIMIT 200")->fetchAll();
   foreach ($rows as &$r) unset($r['password_hash']);
   out(['rows' => $rows]); }
  $own = $H ? "c.head_id=$id" : "1=1";
  $Q = [
   'clubs' => "SELECT c.id,c.name,c.description,IFNULL(u.name,'-') head,c.status FROM clubs c LEFT JOIN users u ON u.id=c.head_id" . ($S ? " WHERE c.status='APPROVED'" : ""),
   'members' => "SELECT m.id,u.name student,c.name club,m.status FROM memberships m JOIN users u ON u.id=m.user_id JOIN clubs c ON c.id=m.club_id WHERE " . ($S ? "m.user_id=$id" : $own),
   'events' => "SELECT e.id,c.name club,e.title,e.event_date,e.reg_deadline,e.capacity,(SELECT COUNT(*) FROM registrations r WHERE r.event_id=e.id) registered,e.status FROM events e JOIN clubs c ON c.id=e.club_id WHERE " . ($S ? "e.status='APPROVED'" : $own),
   'attend' => "SELECT r.id,e.title event,u.name student,IF(r.attended,'PRESENT','ABSENT') status FROM registrations r JOIN events e ON e.id=r.event_id JOIN clubs c ON c.id=e.club_id JOIN users u ON u.id=r.user_id WHERE $own",
   'certs' => "SELECT r.id,e.title event,c.name club,e.event_date FROM registrations r JOIN events e ON e.id=r.event_id JOIN clubs c ON c.id=e.club_id WHERE r.attended=1 AND r.user_id=$id",
   'budget' => "SELECT b.id,c.name club,b.type,b.amount,b.note,b.created_at FROM budget b JOIN clubs c ON c.id=b.club_id WHERE $own ORDER BY b.id DESC",
   'ann' => "SELECT a.id,c.name club,a.title,a.body,a.created_at FROM announcements a JOIN clubs c ON c.id=a.club_id ORDER BY a.id DESC",
   'users' => "SELECT id,name,email,role,department FROM users"];
  if (!isset($Q[$t])) fail('Bad tab');
  out(['rows' => q($Q[$t])->fetchAll()]);
 case 'join': need('STUDENT'); q("INSERT IGNORE INTO memberships(user_id,club_id) VALUES(?,?)", [$id, $in['id']]); out(['ok' => 1]);
 case 'createClub': need('CLUB_HEAD'); q("INSERT INTO clubs(name,description,head_id) VALUES(?,?,?)", [$g('name'), $g('description'), $id]); out(['ok' => 1]);
 case 'status':
  $s = $in['s'] ?? ''; if (!in_array($s, ['APPROVED', 'REJECTED'])) fail('Bad status');
  if (in_array($in['t'], ['clubs', 'events'])) { need('FACULTY,ADMIN'); q("UPDATE {$in['t']} SET status=? WHERE id=?", [$s, $in['id']]); }
  elseif ($in['t'] == 'memberships') { need('CLUB_HEAD,ADMIN'); q("UPDATE memberships m JOIN clubs c ON c.id=m.club_id SET m.status=? WHERE m.id=? AND (c.head_id=? OR ?)", [$s, $in['id'], $id, $u['role'] == 'ADMIN' ? 1 : 0]); }
  else fail('Bad table');
  out(['ok' => 1]);
 case 'createEvent': need('CLUB_HEAD');
  q("INSERT INTO events(club_id,title,description,event_date,reg_deadline,capacity) VALUES(?,?,?,?,?,?)", [myClub(), $g('title'), $g('description'), $dt($g('date')), $dt($g('deadline')), (int)$g('capacity')]); out(['ok' => 1]);
 case 'regEvent': need('STUDENT');
  $e = q("SELECT status,reg_deadline>=NOW() ok,capacity-(SELECT COUNT(*) FROM registrations WHERE event_id=events.id) free FROM events WHERE id=?", [$in['id']])->fetch();
  if (!$e || $e['status'] != 'APPROVED') fail('Event is not approved');
  if (!$e['ok']) fail('Registration deadline has passed');
  if ($e['free'] <= 0) fail('Event is full');
  q("INSERT IGNORE INTO registrations(event_id,user_id) VALUES(?,?)", [$in['id'], $id]); out(['ok' => 1]);
 case 'attend': need('CLUB_HEAD,ADMIN');
  q("UPDATE registrations r JOIN events e ON e.id=r.event_id JOIN clubs c ON c.id=e.club_id SET r.attended=? WHERE r.id=? AND (c.head_id=? OR ?)", [$in['v'] ? 1 : 0, $in['id'], $id, $u['role'] == 'ADMIN' ? 1 : 0]); out(['ok' => 1]);
 case 'addBudget': need('CLUB_HEAD');
  if (!in_array($g('type'), ['FUNDING', 'SPONSORSHIP', 'EXPENSE']) || !is_numeric($g('amount'))) fail('Invalid type or amount');
  q("INSERT INTO budget(club_id,type,amount,note) VALUES(?,?,?,?)", [myClub(), $g('type'), $g('amount'), $g('note')]); out(['ok' => 1]);
 case 'postAnn': need('CLUB_HEAD'); q("INSERT INTO announcements(club_id,title,body) VALUES(?,?,?)", [myClub(), $g('title'), $g('body')]); out(['ok' => 1]);
 case 'setRole': need('ADMIN');
  if (!in_array($g('role'), ['STUDENT', 'CLUB_HEAD', 'FACULTY', 'ADMIN'])) fail('Bad role');
  q("UPDATE users SET role=? WHERE id=?", [$g('role'), $in['id']]); out(['ok' => 1]);
 case 'del': need('ADMIN');
  if (!in_array($in['table'], $TB)) fail('Bad table');
  q("DELETE FROM `{$in['table']}` WHERE id=?", [$in['id']]); out(['ok' => 1]);
 }
 fail('Unknown action');
} catch (PDOException $e) { fail($e->getCode() == '23000' ? 'Duplicate entry, or related data blocks this change' : $e->getMessage()); }
