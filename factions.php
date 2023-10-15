<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . '/EresseaDB.php';

class factions {
    public static function list(EresseaDB $db) {
        $stmt = $db->getFactions();
        $email = NULL;
        $fid = NULL;
        $stmt->bindColumn('no', $fid, PDO::PARAM_STR);
        $stmt->bindColumn('email', $email, PDO::PARAM_STR);
        while ($stmt->fetch(PDO::FETCH_BOUND)) {
            echo "$fid\t$$email\n";
        }
    }
    
    public static function insert(EresseaDB $db, string $fid, string $email, string $pw) {
        // TODO
        return FALSE;
    }
    
    public static function checkpw(EresseaDB $db, string $fid, string $pw) {
        return $db->checkPasswd($fid, $pw);
    }

}
