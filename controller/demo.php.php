<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8"/>
    <title>Table Dropdown Filter</title>
    <link href="../w3.css" rel="stylesheet"/>
    <style>
        .container{width:960px;margin:30px auto;}
        thead select{border: 1px solid #ffffff;width:100%;}
    </style>
</head>
<body>

    <div class="container">
        <table id="mytable" class="w3-table-all">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Gender</th>
                    <th>Address</th>
                </tr>
            </thead>
            <tbody>
               <?php
$serverName = "CPHYDERABAD1\DAU_HYD_2023";
$connectionInfo = array( "Database"=>"TRAINING_DB");
$conn = sqlsrv_connect( $serverName, $connectionInfo );
if( $conn === false ) {
    die( print_r( sqlsrv_errors(), true));
}


$sql8="SELECT DISTINCT * FROM ADDMORE";
$st8 = sqlsrv_query( $conn, $sql8 );

while( $row = sqlsrv_fetch_array( $st8, SQLSRV_FETCH_ASSOC) ) {
echo "<tr>";
echo "<td width=50px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['user_id'] ."<center></font></td>";
echo "<td width=150px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['user_name'] ."<center></font></td>";
echo "<td width=150px bgcolor=#AED1F1><font size=1 face=verdana><center>". $row['user_gender'] ."<center></font></td>";
echo "<td width=125px bgcolor=#C2E0FB><font size=1 face=verdana><center>". $row['user_address'] ."<center></font></td>";
echo "</tr>";
}
          
                              ?>
            </tbody>
        </table>
    </div>

    <script src="jquery.min.js"></script>
    <script src="ddtf.js"></script>
    <script>
        $('#mytable').ddTableFilter();
    </script>
</body>
</html>