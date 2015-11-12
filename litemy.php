<?php
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}
// ------------------------------
$time_start = microtime_float();
// Set the SQL script file that contains ONLY database schema
$backup_schema = isset($_GET['schema']) ? $_GET['schema'] : '0-backup_schema.sqlite.sql';
// Set the output SQL script file name
$import_schema = '1-import_schema.mysql.sql';
$line = null;
$result = null;
if(!isset($backup_schema)) {
  echo "Please provide a SQLite schema file with .sql extension";
  exit;
}
$file = fopen($backup_schema, 'r');
while ($line = fgets($file)) {
  if(strpos($line, '--') !== false || strpos($line, 'CREATE INDEX') !== false || strpos($line, 'sqlite_') !== false) { continue; }
  $line = str_replace('"', '`', $line);
  $line = str_replace('text', 'TEXT', $line);
  if(strpos($line, 'integer') !== false || strpos($line, 'INTEGER') !== false) {
    $line = str_replace('integer', 'INT(11)', $line);
    $line = str_replace('INTEGER', 'INT(11)', $line);
  }
  if(strpos($line, 'AUTOINCREMENT') !== false) {
    $line = str_replace('NULL', 'NOT NULL', $line);
    $line = str_replace('null', 'NOT NULL', $line);
    $line = str_replace('AUTOINCREMENT', 'AUTO_INCREMENT', $line);
  }
  $result .= $line;
}
$time_end = microtime_float();
$time = $time_end - $time_start;
$time = number_format($time, 5);
$result .= PHP_EOL.'-- Convertion time: ' . $time . ' seconds';
$wfile = fopen($import_schema, 'w');
$wrote = fwrite($wfile, $result);
fclose($wfile);
echo 'SQLite database schema is converted to MySQL and saved as SQL script file in '.$time.' seconds<br>';
// ------------------------------
$time_start = microtime_float();
// Set the SQL script file that contains ONLY data
$backup_data = isset($_GET['data']) ? $_GET['data'] : '0-backup_data.sqlite.sql';
// Set the output SQL script file name
$import_data = '2-import_data.mysql.sql';
$line = null;
$result = null;
if(!isset($backup_data)) {
    echo "Please provide a SQLite data file with .sql extension"; exit;
}
$file = fopen($backup_data, 'r');
while ($line = fgets($file)) {
  if(strpos($line, '--') !== false || strpos($line, 'sqlite_') !== false) { continue; }
  $line = str_replace('"', '`', $line);
  $line = str_replace("''", 'NULL', $line);
  $line = str_replace(';INSERT', ';'.PHP_EOL.'INSERT', $line);
  $result .= $line;
}
$time_end = microtime_float();
$time = $time_end - $time_start;
$time = number_format($time, 5);
$result .= PHP_EOL.'-- Convertion time: ' . $time . ' seconds';
$wfile = fopen($import_data, 'w');
$wrote = fwrite($wfile, $result);
fclose($wfile);
echo 'SQLite data is converted to MySQL and saved as SQL script file in '.$time.' seconds<br>';
?>
