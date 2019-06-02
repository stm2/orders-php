<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . '/OrderDB.php';

class orders {
    public static function export(OrderDB $db) {
        $files = $db->getFiles();
        foreach ($files as $filename) {
            $content = file_get_contents($filename);
            if ($content !== FALSE) {
                echo $content;
            }
        }
    }
    
    public static function list(OrderDB $db) {
        $stmt = $db->getRows();
        $email = NULL;
        $date = NULL;
        $filename = NULL;
        $status = -1;
        $stmt->bindColumn('email', $email, PDO::PARAM_STR);
        $stmt->bindColumn('time', $date, PDO::PARAM_STR);
        $stmt->bindColumn('filename', $filename, PDO::PARAM_STR);
        $stmt->bindColumn('status', $status, PDO::PARAM_INT);
        
        while ($stmt->fetch(PDO::FETCH_BOUND)) {
            echo "$status\t$date\t$filename\t$email\n";
        }
    }
    
    public static function insert(OrderDB $db, DateTimeInterface $time, string $filename, string $email = NULL) {
        return $db->addFile($time, $filename, $email);
    }
    
    public static function set_status(OrderDB $db, string $filename, int $status) {
        $db->setStatus($filename, $status);
    }

    public static function get_next(OrderDB $db) {
        $row = $db->getNext();
        return $row;
    }
}
