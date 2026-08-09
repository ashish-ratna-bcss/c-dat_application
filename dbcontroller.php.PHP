<?php
require_once __DIR__ . '/sqlsrv_compat.php';

class DBController {
    private $conn;

    function __construct() {
        $this->conn = $this->connectDB();
    }

    function connectDB() {
        return sqlsrv_connect('postgres', ['Database' => 'postgres']);
    }

    function runQuery($query) {
        $result = sqlsrv_query($this->conn, $query);
        if ($result === false) {
            return null;
        }
        $resultset = [];
        while ($row = sqlsrv_fetch_array($result, SQLSRV_FETCH_ASSOC)) {
            $resultset[] = $row;
        }
        if (!empty($resultset)) {
            return $resultset;
        }
    }

    function numRows($query) {
        $result = sqlsrv_query($this->conn, $query);
        if ($result === false) {
            return 0;
        }
        return sqlsrv_num_rows($result);
    }
}

?>
