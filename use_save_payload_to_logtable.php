<?php
include 'Config.php';
$conn = mysqli_connect($Hostname, $HostUser, $HostPass, $DatabaseName);
mysqli_set_charset($conn, "utf8");
// Checking connection with server
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////  
$jsdata = json_decode(file_get_contents('php://input'), true);
//extract array $jsdata from CAT portal 
$DevEUI = $jsdata["DevEUI_uplink"]["DevEUI"];
$DevAddr = $jsdata["DevEUI_uplink"]["DevAddr"];
$FCntUp = $jsdata["DevEUI_uplink"]["FCntUp"];
$payload_hex = $jsdata["DevEUI_uplink"]["payload_hex"];
$LrrRSSI = $jsdata["DevEUI_uplink"]["LrrRSSI"];
$LrrSNR = $jsdata["DevEUI_uplink"]["LrrSNR"];
$SpFact = $jsdata["DevEUI_uplink"]["SpFact"];
//insert to table log  
if (isset($jsdata["DevEUI_uplink"])) {
    $sql = "INSERT INTO log (DevEUI , DevAddr, FCntUp, payload_hex, LrrRSSI, LrrSNR, SpFact)
            VALUES ('$DevEUI', '$DevAddr', '$FCntUp', '$payload_hex', '$LrrRSSI', '$LrrSNR', '$SpFact')";
    if (mysqli_query($conn, $sql)) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
} else echo "NULL value detected <b>pls push this link in routing profile<b>";
mysqli_close($conn);
?>

<?php
include 'Config.php';
$conn = mysqli_connect($Hostname, $HostUser, $HostPass, $DatabaseName);
mysqli_set_charset($conn, "utf8");

// Checking connection with server
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////  
$jsdata = json_decode(file_get_contents('php://input'), true);

//extract array $jsdata from CAT portal 
$Time = $jsdata["DevEUI_uplink"]["Time"];
$DevEUI = $jsdata["DevEUI_uplink"]["DevEUI"];
$DevAddr = $jsdata["DevEUI_uplink"]["DevAddr"];
$FPort = $jsdata["DevEUI_uplink"]["FPort"];
$payload_hex = $jsdata["DevEUI_uplink"]["payload_hex"];

/*DECODING PART
   PAYLOAD FORMAT XY (2 STRING)
   FIELD Status X ($payload_hex[0]) define Lot_id_A and Lot_id_B STATUS 
      AVALIABLE = RETURN TRUE
      PARKED = RETURN FALSE 
      0 = BOTH PARKED
      1 = A PARKED/B AVALIABLE 
      2 = A AVALIABLE/B PARKED
      3 = BOTH AVALIABLE
      otherwise = return error_lotidField 
   FIELD Battery Y ($payload_hex[1]) IS battery 
      range between 0 - 10 integer 
      %battery = Y*10 
      Ex Y = 5 -> %battery = 5*10 = 50%; 
   */

//Query Lot_id from DevEUI, Result has either one or two row. 
//Order result by ascending value of Lot_id, It's Lot_id_A and Lot_id_B respectively In other words Lot_id_A < Lot_id_B
$Lot_id = array(FALSE, FALSE);
if ($stmt = mysqli_prepare($conn, "SELECT `Lot_id` FROM INFORMATION_TABLE WHERE `DevEUI` = ? ORDER BY `Lot_id`;")) {
    mysqli_stmt_bind_param($stmt, "s", $DevEUI);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $Lotid);

    $i = 0;
    while (mysqli_stmt_fetch($stmt)) {
        $Lot_id[$i] = $Lotid;
        ++$i;
    }

    if ($Lot_id[0] == FALSE && $Lot_id[1] == FALSE)
        echo "Error :: Not found DevEUI = $DevEUI";

    mysqli_stmt_close($stmt);
}

//convert field status to status 
//0 is AVALIABLE , 1 IS PARKED 
$fieldcheck = TRUE;
if ($payload_hex[0] == "0") {
    $Lot_Status[0] = 0;
    $Lot_Status[1] = 0;
} else if ($payload_hex[0] == "1") {
    $Lot_Status[0] = 0;
    $Lot_Status[1] = 1;
} else if ($payload_hex[0] == "2") {
    $Lot_Status[0] = 1;
    $Lot_Status[1] = 0;
} else if ($payload_hex[0] == "3") {
    $Lot_Status[0] = 1;
    $Lot_Status[1] = 1;
} else {
    echo "Error :: Field_status = " . $payload_hex[0];
    $fieldcheck = FALSE;
}

//convert BAT to %
/*    note : ord("0") = 48, ord("9") = 57, ord("A") = 65, ord("a") = 97 */
$bat = ord($payload_hex[1]);
if ((($bat >= 48) && ($bat <= 57)) || ($bat == 65) || ($bat == 97)) {
    if ($bat == 65 || ($bat == 97)) $battery = 100;
    else $battery = ($bat - 48) * 10;
} else {
    echo "Error :: Field_battery = " . $payload_hex[1];
    $fieldcheck = FALSE;
}

//insert status and BAT to Database
for ($i = 0; $i < 2; $i++) {
    if ($Lot_id[$i] && $fieldcheck) {
        $sql = "INSERT INTO `STATE_TABLE`(`Lot_Id`, `Lot_Status`, `Board_Status`, `Board_Battery`) VALUES ( $Lot_id[$i],$Lot_Status[$i],'',$battery)";
        if (mysqli_query($conn, $sql)) {
            //For Debug
            $everythingcomplete = TRUE;
        } else {
            echo "Error ::  insert status and BAT to Database" . $sql  . "<br>" . mysqli_error($conn);
        }
    }
}

//For Debug 
if ($everythingcomplete) {
    echo "Received (Time : $Time, DevEUI : $DevEUI, DevAddr : $DevAddr, FPort : $FPort, payload_hex = $payload_hex)<br>";
    echo "Decode (Lot_Id_A : $Lot_id[0], Lot_Status_A : $Lot_Status[0], Lot_Id_B : $Lot_id[1], Lot_Status_B : $Lot_Status[1], Board_Status = Null, Board_Battery = $battery)<br>";
}

mysqli_close($conn);
?>

