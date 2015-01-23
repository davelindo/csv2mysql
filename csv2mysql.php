<?php

$host = 'localhost';
$user = 'username';
$pass = 'password';
$database = 'database';

$db = mysql_connect($host, $user, $pass);
mysql_query("use $database", $db);

/********************************************************************************/
// Parameters: filename.csv table_name insert_count

if(isset($argv[1])) { $file = $argv[1]; }
else {
    error();
}
if(isset($argv[2])) { $table = $argv[2]; }
else {
    error();
}
if(isset($argv[3])) { $maxInsertCount = $argv[3]; }
else {
    $maxInsertCount = 100;
}


/********************************************************************************/
// Get the first row to create the column headings

$fp = fopen($file, 'r');
$frow = fgetcsv($fp);
$headers = array();
$columns = null;
foreach($frow as $column) {
    if($columns) $columns .= ', ';
    $columns .= "`$column` varchar(250)";
    array_push($headers, $column);
}
$create = "create table if not exists $table ($columns);";
mysql_query($create, $db);

/********************************************************************************/
// Import the data into the newly created table. Adhear to Maximum insert count.
$inserted = 0;
$insert = insertInto();
while (!feof($fp)) {
    if ($inserted >= $maxInsertCount){
        $insert = rtrim($insert, ',');
        mysql_query($insert, $db);
        $inserted = 0;
        $insert = insertInto();
    }
    $inLine = trim(fgets($fp));
    if (count(explode(',',$inLine)) == count($headers)){
        $insert .= '(\'';
        $line = array();
        $values = explode(',',$inLine);
        foreach ($values as $value){
            array_push($line, $value);
        }
        $insert .= implode('\',\'',$line);
        $insert .= '\'),';
        $inserted++;
    }
}
$insert = rtrim($insert, ',');
mysql_query($insert, $db);
function insertInto (){
    global $table, $headers;
    return 'insert into '.$table.' ('.implode(',',$headers).') VALUES '."\n";
}
function error(){
    print "Usage: ./".__FILE__." csv_file.csv table_name maximum_insert_count\n\tDefaults:\n\t\tmaximum_insert_count:\t100\n";
    exit;
}
?>
