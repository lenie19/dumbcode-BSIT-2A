<?php
$pdo = new PDO('mysql:host=localhost', 'root', '');
$pdo->exec('USE crud_db;');
$tables = ['parents', 'login_attempts', 'users', 'migrations', 'person', 'profiling', 'student', 'tbl_logs'];
foreach ($tables as $table) {
    try {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "Dropped: $table\n";
    } catch (Exception $e) {
        echo "Could not drop $table\n";
    }
}
echo "All tables dropped successfully\n";
?>
