<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$method = $_SERVER['REQUEST_METHOD'];
if ($method != 'POST') {
    header('HTTP/1.0 405 Method Not Allowed');
    exit();
}

require_once __DIR__ . '/orders.php';

// TODO: read these from a config file
$config = [
    'game' => 2,
    'lang' => 'de',
    'uploads' => '/home/eressea/www/eressea/files',
    'dbname' => 'orders.db',
];

$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL, FILTER_REQUIRE_SCALAR);
$game = filter_input(INPUT_POST, 'game', FILTER_VALIDATE_INT, ['options' => ['default' => $config['game'], 'min_range' => 1]]);
$lang = filter_input(INPUT_POST, 'lang', FILTER_SANITIZE_STRING, ['options' => ['default' => $config['lang'], 'flags' => FILTER_REQUIRE_SCALAR]]);
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR);

if ($password != 'eressea') {
    echo "Permission denied\n";
    header('HTTP/1.0 403 Permission denied');
    exit();
}

$upload_dir = $config['uploads'] . '/game-' . $game;
$dbfile = $upload_dir . '/' . $config['dbname'];
if (!file_exists($dbfile)) {
    echo "database not found: $dbfile\n";
    exit();
}
$dbsource = 'sqlite:' . $dbfile;
$time = new DateTime();
if (isset($_FILES['input']) && !empty($email)) {
    $tmp_name = $_FILES['input']['tmp_name'];
    $filename = tempnam($upload_dir . '/uploads', 'upload-');
    if ($filename) {
        $db = new OrderDB();
        $db->connect($dbsource);
        if (move_uploaded_file($tmp_name, $filename)) {
            orders::insert($db, $time, $filename, $lang, $email, 3);
	}
        unset($db);
    }
    else {
        header('HTTP/1.0 507 Insufficient Storage');
    }
}
else {
    header('HTTP/1.0 400 Bad Request');
    exit();
}
