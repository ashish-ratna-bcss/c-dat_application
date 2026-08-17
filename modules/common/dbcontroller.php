<?php
require_once __DIR__ . '/bootstrap.php';
require_once CDAT_COMMON . '/sqlsrv_compat.php';

class DBController {
    private $conn;

    function __construct() {
        $this->conn = sqlsrv_connect('postgres', ['Database' => 'CDATDUPL']);
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
        return $result === false ? 0 : sqlsrv_num_rows($result);
    }
}

?>
