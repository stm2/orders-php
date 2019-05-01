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
    
    public static function connect(string $dbsource) {
        $db = new OrderDB();
        $db->connect($dbsource);
        return $db;
    }
}

$db = cli::connect('sqlite:orders.db');
cli::insert($db, 'orders-enno.txt', '2005-08-15T15:52:02+00:00');
cli::insert($db, 'orders-wiki.txt', '2005-08-15T15:52:01+00:00');
var_dump($db->getFiles());
