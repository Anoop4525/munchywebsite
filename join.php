<?php
declare(strict_types=1);
ini_set('display_errors','0');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
function reply(int $code,string $message): never {
 http_response_code($code);
 if (str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['message'=>$message], JSON_UNESCAPED_UNICODE);
 } else {
  header('Content-Type: text/html; charset=utf-8');
  echo '<!doctype html><html lang="en"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Munchy Waitlist</title><link rel="stylesheet" href="/style.css"><main class="wrap" style="padding:4rem 1rem"><h1>Munchy.</h1><p>'.htmlspecialchars($message,ENT_QUOTES,'UTF-8').'</p><a class="btn" href="/waitlist.html">Back to waitlist</a></main></html>';
 }
 exit;
}
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { header('Allow: POST'); reply(405,'Method not allowed.'); }
if ((int)($_SERVER['CONTENT_LENGTH'] ?? 0)>8192) reply(413,'Request too large.');
if (!str_starts_with(strtolower($_SERVER['CONTENT_TYPE'] ?? ''),'application/x-www-form-urlencoded')) reply(415,'Unsupported request.');
if (($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '') === 'cross-site') reply(403,'Request not allowed.');
if (!empty($_SERVER['HTTP_ORIGIN'])) {
 $origin=parse_url($_SERVER['HTTP_ORIGIN'],PHP_URL_HOST);
 $host=parse_url('http://'.($_SERVER['HTTP_HOST'] ?? ''),PHP_URL_HOST);
 if (!$origin || strcasecmp($origin,(string)$host)!==0) reply(403,'Request not allowed.');
}
if (!empty($_POST['website'])) reply(400,'Request not allowed.');
$email=is_string($_POST['email'] ?? null)?strtolower(trim($_POST['email'])):'';
if (strlen($email)>254 || !filter_var($email,FILTER_VALIDATE_EMAIL)) reply(400,'Enter a valid email address.');
$utm=[];
foreach (['utm_source','utm_medium','utm_campaign'] as $key) {
 $value=$_POST[$key] ?? '';
 $utm[]=is_string($value)&&preg_match('/^.{0,100}/us',$value,$m)?$m[0]:'';
}
try {
 $c=require __DIR__.'/db-config.php';
 if ($c['database']==='YOUR_DATABASE_NAME'||$c['username']==='YOUR_DATABASE_USERNAME'||$c['password']==='YOUR_DATABASE_PASSWORD') throw new RuntimeException('Configuration incomplete');
 $pdo=new PDO('mysql:host='.$c['host'].';port='.$c['port'].';dbname='.$c['database'].';charset=utf8mb4',$c['username'],$c['password'],[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false,PDO::ATTR_TIMEOUT=>5]);
 try {$pdo->query('SELECT email FROM waitlist LIMIT 0');} catch(PDOException $e) {
  if (($e->errorInfo[1]??0)!==1146) throw $e;
  $pdo->exec(file_get_contents(__DIR__.'/schema.sql'));
 }
 $stmt=$pdo->prepare('INSERT INTO waitlist (email,utm_source,utm_medium,utm_campaign) VALUES (?,?,?,?)');
 try {$stmt->execute([$email,...$utm]);} catch(PDOException $e) {if (($e->errorInfo[1]??0)!==1062) throw $e;}
 reply(200,'You’re on the list! We’ll be in touch when Munchy launches.');
} catch(Throwable $e) {
 error_log('Munchy waitlist failure: '.get_class($e).' code '.$e->getCode());
 reply(503,'Signup is temporarily unavailable. Please try again later.');
}
