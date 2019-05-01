<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of OrderDB
 *
 * @author Enno
 */
class OrderDB {
    /** @var PDO $pdo */
    private $pdo;
    private $stmtInsert;

    public function connect(string $dbsource) {
        $this->pdo = new PDO($dbsource);
        $this->stmtInsert = NULL;
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $schema = file_get_contents(__DIR__ . '/schema/sqlite.sql');
        $this->pdo->exec($schema);
    }
    
    public function getFiles() {
        $stmt = $this->pdo->query("SELECT `filename` FROM `submission` ORDER BY `time` ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getRows() : PDOStatement {
        return $this->pdo->query("SELECT `time`, `status`, `email`, `filename` FROM `submission` ORDER BY `time` ASC");
    }

    public function addFile(DateTimeInterface $time, string $filename, string $email = NULL): int {
        $datetime = date('Y-m-d H:i:s', $time->format('U'));
        if (is_null($this->stmtInsert)) {
            $this->stmtInsert = $this->pdo->prepare("INSERT INTO `submission` (`filename`, `time`, `email`) VALUES (?, ?, ?)");
        }
        if ($this->stmtInsert->execute([$filename, $datetime, $email]) !== TRUE) {
            return FALSE;
        }
        return $this->pdo->lastInsertId();
    }
}
