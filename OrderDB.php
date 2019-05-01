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
    
    public function connect(string $dbsource) {
        $this->pdo = new PDO($dbsource);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $schema = file_get_contents('schema/sqlite.sql');
        $this->pdo->exec($schema);
    }
    
    public function getFiles() {
        $stmt = $this->pdo->query("SELECT `filename` FROM `submission` ORDER BY `time` ASC");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function addFile(DateTimeInterface $time, string $filename, string $email = NULL): int {
        $datetime = date_format($time, DateTimeInterface::ATOM);
        $stmt = $this->pdo->prepare("INSERT INTO `submission` (`filename`, `time`, `email`) VALUES (?, ?, ?)");
        var_dump($stmt);
        if ($stmt->execute([$filename, $datetime, $email]) !== TRUE) {
            return FALSE;
        }
        return $this->pdo->lastInsertId();
    }
}
