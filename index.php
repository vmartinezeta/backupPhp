<?php

require_once "DatabaseBackup.php";
require_once "Message.php";
require_once "DatabaseOption.php";

$backup = new DatabaseBackup("localhost", "root", "");
$message = $backup->generate("demo", "backup.sql", DatabaseOption::$TODO);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="main.css">
</head>
<body>
    <div class="top">
        <h1 class="mainTitle">Backup</h1>
    </div>
    <?php 
        $message->output();
    ?>
</body>
</html>