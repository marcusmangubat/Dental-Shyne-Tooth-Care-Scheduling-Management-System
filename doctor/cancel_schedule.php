<?php
session_start();

if (isset($_SESSION["user"])) {
    if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'd') {
        header("location: ../login.php");
    }
} else {
    header("location: ../login.php");
}

//doctor delete she/her schedule that admin set
if ($_GET) {
    include "../config.php";
    $id = $_GET["id"];
    $sql = $database->query("DELETE FROM schedule WHERE scheduleid = '$id';");
    header("location: schedule.php");
}
