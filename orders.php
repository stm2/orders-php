<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . '/OrderDB.php';

class orders {
    public static function generate(OrderDB $db) {
        $files = $db->getFiles();
        foreach ($files as $filename) {
            $content = file_get_contents($filename);
            if ($content !== FALSE) {
                echo $content;
                echo PHP_EOL;
            }
        }
    }
    
    public static function insert(OrderDB $db, DateTimeInterface $time, string $filename, string $email = NULL) {
        return $db->addFile($time, $filename, $email);
    }
}