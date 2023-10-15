<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of EresseaDB
 *
 * @author stm
 */
class EresseaDB {
    const SCHEMA_VERSION = 0;
    /** @var PDO $pdo */
    private $pdo;
    private $stmtPw = NULL;

    private function update() {
        $stmt = $this->pdo->query("PRAGMA user_version");
        $column = $stmt->fetch(PDO::FETCH_NUM);
        $version = $column[0];
        if ($version >= self::SCHEMA_VERSION) {
            return;
        }
        echo "cannot update schema from version $version to " . self::SCHEMA_VERSION . PHP_EOL;
    }
    
    public function connect(string $dbsource) {
        $this->pdo = new PDO($dbsource);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->update();
    }
    
    public function getFactions() : PDOStatement {
        return $stmt = $this->pdo->query("SELECT `no`, `email` FROM `faction`");
        #return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function checkPasswd(string $id, string $pw) : bool {
        if (is_null($this->stmtPw)) {
            $this->stmtPw = $this->pdo->prepare('SELECT `password` FROM `faction` WHERE `no` = ?');
        }
        if ($this->stmtPw->execute([$id])) {
            $hash = $this->stmtPw->fetchColumn();
            return password_verify($pw, $hash);
        }

        return false;
    }

}
