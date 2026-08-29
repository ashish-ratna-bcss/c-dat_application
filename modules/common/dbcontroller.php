<?php

require_once __DIR__ . '/bootstrap.php';

class DBController {
    private $conn;

    function __construct() {
        $this->conn = get_cdat_pdo();
    }

    function runQuery($query) {
        $result = $this->conn->query($query);
        if ($result === false) {
            return null;
        }
        $resultset = [];
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $resultset[] = $row;
        }
        if (!empty($resultset)) {
            return $resultset;
        }
    }

    function numRows($query) {
        $result = $this->conn->query($query);
        return $result === false ? 0 : $result->rowCount();
    }
}

?>
