<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . '/orders.php';

class cli {
    public static function insert(OrderDB $db, string $filename, string $timestamp = 'now', string $email = NULL) {
        $time = new DateTime($timestamp);
        orders::insert($db, $time, $filename, $email);
    }
    
    public static function list(OrderDB $db) {
        orders::list($db);
    }
    
    public static function export(OrderDB $db) {
        orders::export($db);
    }
    
    public static function connect(string $dbsource) {
        $db = new OrderDB();
        $db->connect($dbsource);
        return $db;
    }
}

function usage($name) {
    return "Usage: $name [-d <database>] <command> [<args>] \n" . <<<USAGE
These commands are available:
            help    display help information
            list    show all files received
            insert  insert a new file
            export  export all files in order
USAGE;
}

// Script example.php
$dbname = 'orders.db';
$optind = null;
$opts = getopt('d::h', [], $optind);

if (isset($opts['h'])) {
    echo usage($argv[0]);
    exit(0);
}

$pos_args = array_slice($argv, $optind);

if (!isset($pos_args[0])) {
    echo usage($argv[0]);
    exit(1);
}

$command = $pos_args[0];

if ($command == 'help') {
    echo usage($argv[0]);
    exit(0);
}

if (isset($opts['d'])) {
    $dbname = $opts['d'];
}
$db = cli::connect('sqlite:' . $dbname);

if ('insert' == $command) {
    if (isset($pos_args[1])) {
        $filename = $pos_args[1];
    }
    else {
        echo usage($argv[0]);
        exit(1);
    }
    if (isset($pos_args[2])) {
        $time = $pos_args[2];
    }
    else {
        $time = 'now';
    }
    if (isset($pos_args[3])) {
        $email = $pos_args[3];
    }
    else {
        $email = null;
    }
    cli::insert($db, $filename, $time, $email);
}
elseif ('list' == $command) {
    cli::list($db);
}
elseif ('export' == $command) {
    cli::export($db);
}
