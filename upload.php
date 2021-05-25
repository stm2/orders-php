<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
$method = getenv('REQUEST_METHOD');
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
    'password' => NULL,
//    'password' => password_hash('eressea'),
];
header('Content-Type: text/plain; charset=utf-8');
$email = NULL;
$game = filter_input(INPUT_POST, 'game', FILTER_VALIDATE_INT, ['options' => ['default' => $config['game'], 'min_range' => 1]]);
$lang = filter_input(INPUT_POST, 'lang', FILTER_SANITIZE_STRING, ['options' => ['default' => $config['lang'], 'flags' => FILTER_REQUIRE_SCALAR]]);
$password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING, FILTER_REQUIRE_SCALAR);
$pwhash = $config['password'];

if (!is_null($pwhash) && !password_verify($password, $pwhash)) {
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
if (isset($_FILES['input'])) {
    $tmp_name = $_FILES['input']['tmp_name'];
    $input = file_get_contents($tmp_name);
    $encoding = mb_detect_encoding($input, ['ASCII', 'UTF-8']);
    if (FALSE === $encoding) {
        echo "Please convert your file to UTF-8\n";
        header('HTTP/1.0 406 Not Acceptable');
        exit();
    }
    $filename = tempnam($upload_dir . '/uploads', 'upload-');
    if ($filename) {
        $db = new OrderDB();
        $db->connect($dbsource);
        if (move_uploaded_file($tmp_name, $filename)) {
            orders::insert($db, $time, $filename, $lang, $email, 3);
            echo "orders were received as $filename\n";
            header('HTTP/1.0 201 Created');
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
