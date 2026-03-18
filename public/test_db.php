<?php
require_once __DIR__ . '/../config/database.php';

$db = getDB();
$count = $db->query("SELECT COUNT(*) FROM movies")->fetchColumn();

echo "Conectou! Total movies = " . $count;