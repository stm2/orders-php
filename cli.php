<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . '/orders.php';

class cli {
    public static function insert(OrderDB $db, string $filename, DateTimeInterface $time, string $lang, string $email = NULL) {
        orders::insert($db, $time, $filename, $lang, $email);
    }
    
    public static function update(OrderDB $db, string $filename, int $status) {
        orders::set_status($db, $filename, $status);
    }
    
    public static function lock(OrderDB $db) {
        $row = orders::get_next($db);
        $filename = $row['filename'];
        $email = $row['email'];
        echo $email . "\t" . $filename . PHP_EOL;
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

function usage($name, $command = NULL) {
    return "Usage: $name [-d <database>] <command> [<args>] \n" . <<<USAGE
These commands are available:
            help    display help information
            list    show all files received
            lock    fetch a filename for processing
            update  set file status
            insert  insert a new file
            export  export all files in order
USAGE;
}

$dbname = 'orders.db';

$optind = 1;
while (isset($argv[$optind])) {
    $arg = $argv[$optind];
    if (in_array($arg, ['-h', '--help'])) {
        echo usage($argv[0]);
        exit(0);
    }
    elseif ('-d' === $arg) {
        $dbname = $argv[++$optind];
    }
    elseif ('--dbname=' === substr($arg, 0, 9)) {
        $dbname = substr($arg, 9);
    }
    else {
        break;
    }
    ++$optind;
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
    if (isset($pos_args[2])) {
        $filename = $pos_args[1];
        $lang = $pos_args[2];
    }
    else {
        echo usage($argv[0], $command);
        exit(1);
    }
    if (isset($pos_args[3])) {
        $email = $pos_args[3];
    }
    else {
        $email = null;
    }
    if (isset($pos_args[4])) {
        $time = new DateTime($pos_args[4]);
    }
    else {
        $mtime = filemtime($filename);
        if (FALSE === $mtime) {
            $time = new DateTime('now');
        }
        else {
            $time = DateTime::createFromFormat('U', $mtime);
        }
    }
    cli::insert($db, $filename, $time, $lang, $email);
}
elseif ('lock' == $command) {
    cli::lock($db);
}
elseif ('list' == $command) {
    cli::list($db);
}
elseif('update' == $command) {
    if (isset($pos_args[1])) {
        $filename = $pos_args[1];
    }
    if (isset($pos_args[2])) {
        $status = intval($pos_args[2]);
    }
    if (isset($status) and isset($filename)) {
        cli::update($db, $filename, $status);
    }
    else {
        echo usage($argv[0], $command);
        exit(1);
    }
}
elseif ('export' == $command) {
    cli::export($db);
}
