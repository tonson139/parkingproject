<?php
    // ไฟล์สำหรับรับค่าที่ส่งมาจาก NodeMCU

    // เรียกชื่อไฟล์ DatabaseConfig.php
    include 'DatabaseConfig.php';

    // เชื่อมต่อกับ server
    $conn = mysqli_connect($Hostname, $HostUser, $HostPass, $DatabaseName);
    mysqli_set_charset($conn, "utf8");
        
    // Check การเชื่อมต่อกับ server
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error()); // แสดงข้อความหากเชื่อมต่อไม่สำเร็จ + error
    }

    // กำหนดตัวแปรสำหรับรับค่าจาก NodeMCU
    // หลัง GET เป็นชื่อตัวแปรที่ใส่ใน url ของ NodeMCU (ดูได้ใน code ของ NodeMCU)
    $d_status1 = (int)$_GET['d_status1']; // รับจาก Sensor ตัวที่ 1 (ช่องที่ 2)
    $d_status2 = (int)$_GET['d_status2']; // รับจาก Sensor ตัวที่ 2 (ช่องที่ 6)
    $d_status3 = (int)$_GET['d_status3']; // รับจาก Sensor ตัวที่ 3 (ช่องที่ 7)

    // คำสั่งส่งค่าที่รับได้ไปยังตารางเก็บข้อมูลใน database
    $sql = "INSERT INTO ParkingData (d_status1, d_status2, d_status3) VALUES ($d_status1, $d_status2, $d_status3)";

    // แสดงผลใน browser ว่าการส่งค่าไปยัง database สำเร็จหรือไม่
    if (mysqli_query($conn, $sql)) {
        echo "Insert new data successfully!"; // ส่งค่าที่รับไปเก็บในตารางสำเร็จ
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn); // ส่งค่าที่รับไปเก็บในตารางไม่ได้ + error
    }
        
    // ปิดการเชื่อมต่อกับ server
    mysqli_close($conn);
?>