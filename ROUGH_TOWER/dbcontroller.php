<?php
 class DBController {
private $serverName = "CPHYDERABAD1";
private $connectionInfo = array( "Database"=>"TWRMDB");
private $conn;

	function __construct() {
		$this->conn = $this->connectDB();
	}
		
	function connectDB() {
		$conn = sqlsrv_connect( $this->serverName, $this->connectionInfo );
		return $conn;
	}
	
	function runQuery($query) {
		$result = sqlsrv_query($this->conn,$query);
		while($row = sqlsrv_fetch_array( $result ,SQLSRV_FETCH_ASSOC)) {
			$resultset[] = $row;
		}		
		if(!empty($resultset))
			return $resultset;
	}
	
	function numRows($query) {
		$result  = sqlsrv_query($this->conn,$query);
		$rowcount = sqlsrv_num_rows($result);
		return $rowcount;	
	}
}

?>