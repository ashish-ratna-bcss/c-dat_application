<?php
require_once __DIR__ . '/sqlsrv_compat.php';

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
        // Always an array. This used to fall off the end and return null when
        // the query matched nothing, so all 51 callers -- every one of which
        // does foreach($results as ...) to fill a dropdown -- printed
        // "foreach() argument must be of type array|object, null given"
        // the moment their table was empty. null is still distinguishable:
        // only a failed query returns it.
        return $resultset;
    }

    function numRows($query) {
        $result = sqlsrv_query($this->conn, $query);
        return $result === false ? 0 : sqlsrv_num_rows($result);
    }
}

?>
