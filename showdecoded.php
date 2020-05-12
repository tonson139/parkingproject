<?php require 'decodepayload2.php' ?> 
    Show Decoded payload <br>
    Payload: <?php echo $payload; ?> <br>
    HEX: <?php echo $hex_payload[0].$hex_payload[1]." | ".$hex_payload[2]." | ".$hex_payload[3]; ?> <br>
    DEC: <?php echo $p_id." | ".$p_sta."(hex) | ".$p_bat ?> <br>
    Decoded----------------------------<br>
    p_id: <?php echo '$get_result[0] = '.$get_result[0].' | $get_result[2] = '.$get_result[2]; ?> <br>
    p_sta: <?php echo '$get_result[1] = '.$get_result[1].' | $get_result[3] = '.$get_result[3]; ?> <br>
    p_bat: <?php echo $bat."%"; ?> <br>