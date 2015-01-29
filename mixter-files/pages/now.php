<?

pd();
print "after putenv<br />\n";
$x = putenv("TZ=America/Los_Angeles");
pd();

function pd() {
$php_date = date('Y-m-d H:i:s');
print "php date(): " . $php_date . "<br \>\n";
$mysql_date = CCDatabase::QueryItem('SELECT NOW()');
print "mysql NOW(): " . $mysql_date . "<br \>\n";
}
?>
