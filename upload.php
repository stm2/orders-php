<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (filter_input(INPUT_SERVER, 'REQUEST_METHOD', FILTER_REQUIRE_SCALAR) != 'POST') {
    header('HTTP/1.0 405 Method Not Allowed');
    exit();
}

require_once __DIR__ . '/orders.php';

// TODO: read these from a config file
$config = [
    'game' => 2,
    'lang' => 'de',
    'uploads' => __DIR__ . '/files',
    'dbsource' => 'sqlite:orders.db',
];

$email = filter_input(INPUT_POST, 'mail', FILTER_VALIDATE_EMAIL);
$game = filter_input(INPUT_POST, 'game', FILTER_VALIDATE_INT, ['options' => ['default' => $config['game'], 'min_range' => 1]]);
$lang = filter_input(INPUT_POST, 'lang', FILTER_REQUIRE_SCALAR, ['options' => ['default' => $config['lang']]]);

$dbsource = $config['dbsource'];
$upload_dir = $uploads . '/game-' . $game;
$time = new DateTime();

if (isset($_FILES['input'])) {
    $tmp_name = $_FILES['input']['tmp_name'];
    $filename = tempnam($upload_dir, 'upload-');
    if ($filename) {
        $db = new OrderDB();
        $db->connect($dbsource);
        move_uploaded_file($tmp_name, $filename);
        orders::insert($db, $time, $filename, $lang, $email);
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
