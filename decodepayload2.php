<!DOCTYPE html>
<html>
    <head>
        <title>Decode</title>
    </head>
    <body> DECODE </body>
</html>
<?php
/*
//////////////////// Editor: Patanin phueanmuenwai ///////////////////////////////////////////////////
//////////////////// Date: 3 FEB 2020 ////////////////////////////////////////////////////////////////
    Payload Format = [id board 2 bytes][status 1 byte][battery 1 byte] = XX_X_X 
        value in hex , 1 byte = 2 hexs = 8 bits
    Field   p_id = id board 
            p_sta = stauts , 1 = lower lot is parked , 2 = upper lot id parked, 3 = both lot id parked 
            p_bat = Battery percentage range 0 to 10 
assume this code use to decode payload which recieve form HTTP only in body part no header and trails
payload recieved in type strings  
    using lookup table in DB: dp1 Table: table_convert 
    field: con_board_id,con_lot_id
/////////////////////////////////////////////////////////////////////////////////////////////////////
*/
//Dev Get input and test decode, you can delete this part 
        //MySQLi Object-Oriented https://www.w3schools.com/php/php_mysql_connect.asp
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "DemoParking";

        // Create connection
        $conn = new mysqli($servername, $username, $password,$dbname);

        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        echo "Connected successfully";


    function strToHex($string){ //Copy form somewhere cannot remeber LOL
        $hex='';
        for ($i=0; $i < strlen($string); $i++){
            $hex .= dechex(ord($string[$i]));
        }
        return $hex;
    }
//---------------------------------------------------------------------------------------------
    $payload = $_POST['payload'];   //get input from index.php using POST method
    $hex_payload = strToHex($payload); //convert strings to Hex 

//Convert hex to dec value 
    $p_id = hexdec($hex_payload[0].$hex_payload[1]);
    $p_sta = $hex_payload[2];
    $p_bat = hexdec($hex_payload[3]);

//Declare var to return result form decode in type array with 4 element [lot1][status1][lot2][status2]
    $get_result = array(0,0,0,0);
//Decode p_id  
    //query cmd
    $sql = "
        SELECT *
        FROM table_convert
        WHERE $p_id = con_board_id;
    ";
    //query statment  MySQLi Object-oriented https://www.w3schools.com/php/php_mysql_select.asp
    $qresult = $conn->query($sql);
    //save lot_id in get_result array 
    if ($qresult->num_rows > 0) {
        // output data of each row
        $i = 0;
        while($row = $qresult->fetch_assoc()) {
            $get_result[$i] = $row["con_lot_id"];
            $i = $i + 2;
        }
    } else {
        echo "0 results";
    }
    
///Decode p_sta ????????????????????? << งง 
    //lower lot id =  get_result[0] , upper lot id = get_result[2]
    //lower lot status =  get_result[1] , upper lot status = get_result[3]
    // 1 = lower lot is parked , 2 = upper lot id parked, 3 = both lot id parked 
    if($p_sta == 1){
        $get_result[1] = 1;}
    elseif($p_sta == 2){
        $get_result[3] = 1;}
    elseif($p_sta == 3){
        $get_result[1] = 1;
        $get_result[3] = 1;
    }

//Decode p_bat
    $bat = $p_bat*10;
    
//Insert borad_id and status to DB
    //query cmd
    $sql = "
    INSERT INTO table_bat (board_id, board_status, battery)
    VALUES ($p_id,$p_sta,$p_bat);
    ";
    //query statment  MySQLi Object-oriented https://www.w3schools.com/php/php_mysql_select.asp
    $qresult = $conn->query($sql);
    
$conn->close();

?>