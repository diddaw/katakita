<?php
require_once __DIR__ . '/../includes/bootstrap.php';
$auth = new Auth($databaseConnection);
$auth->logout();
header("Location: ../login.php");
exit;