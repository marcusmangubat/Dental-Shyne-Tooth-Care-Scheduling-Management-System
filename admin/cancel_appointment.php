<?php
//admin can cancel appointment of patient
session_start();
if (isset($_SESSION["user"])) {
    if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'a') {
        header("location ../login.php");
    }
} else {
    header("location: ../login.php");
}

if ($_GET) {
    include "../config.php";
    $id = $_GET["id"];
    $sql = $database->query("DELETE FROM appointment WHERE appoid = '$id'; ");
    header("location: appointment.php");
}
