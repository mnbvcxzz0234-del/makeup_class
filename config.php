<?php
if(session_status()===PHP_SESSION_NONE)session_start();
date_default_timezone_set('Asia/Bangkok');
function db(){static $p;if(!$p){$h=getenv('DB_HOST');$port=getenv('DB_PORT')?:'5432';$n=getenv('DB_NAME')?:'postgres';$u=getenv('DB_USER')?:'postgres';$pw=getenv('DB_PASS');
 if(!$h&&($url=getenv('DATABASE_URL'))){$o=parse_url($url);$h=$o['host'];$port=$o['port']??'5432';$n=ltrim($o['path'],'/');$u=$o['user'];$pw=$o['pass'];}
 $p=new PDO("pgsql:host=$h;port=$port;dbname=$n;sslmode=require",$u,$pw,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);}return $p;}
function h($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function csrf(){return $_SESSION['t']??($_SESSION['t']=bin2hex(random_bytes(16)));}
function csrf_ok(){return hash_equals($_SESSION['t']??'',$_POST['t']??'');}
// กฎของคอร์ส (ถ้าไม่มีกฎเฉพาะ ใช้กฎ '*')
function rule($course){$s=db()->prepare("SELECT * FROM makeup_rules WHERE course=? OR course='*' ORDER BY (course='*') LIMIT 1");$s->execute([$course]);return $s->fetch();}
// ตรวจสิทธิ์จองรอบชดเชย คืน [ok, ข้อความ]
function can_book($a,$d,$tm){
 $r=rule($a['course']);
 if(!$a['eligible'])return [false,'ไม่มีสิทธิ์ชดเชย: '.$a['note']];
 if($a['status']!=='open')return [false,'ใบลานี้ถูกใช้สิทธิ์แล้ว'];
 if(strtotime("$d $tm")<=time())return [false,'ต้องเลือกรอบในอนาคต'];
 if($d<$a['class_date']||$d>date('Y-m-d',strtotime($a['class_date'].' +'.$r['window_days'].' day')))return [false,'ต้องจองภายใน '.$r['window_days'].' วันหลังวันที่ลา'];
 $c=db()->prepare("SELECT COUNT(*) FROM makeup_bookings b JOIN absences x ON x.id=b.absence_id WHERE b.student_id=? AND x.course=? AND b.status IN('booked','attended') AND to_char(b.slot_date,'YYYY-MM')=?");
 $c->execute([$a['student_id'],$a['course'],substr($d,0,7)]);
 if($c->fetchColumn()>=$r['max_per_month'])return [false,'ครบสิทธิ์ชดเชย '.$r['max_per_month'].' ครั้ง/เดือนแล้ว'];
 $b=db()->prepare('SELECT balance FROM credits WHERE student_id=?');$b->execute([$a['student_id']]);
 if((float)$b->fetchColumn()<$r['cost_credits'])return [false,'เครดิตไม่พอ (ต้องใช้ '.$r['cost_credits'].')'];
 return [true,''];
}
