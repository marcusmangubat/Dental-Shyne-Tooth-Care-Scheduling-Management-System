<?php
session_start();

if (!isset($_SESSION["user"]) || $_SESSION["user"] == "" || $_SESSION['usertype'] != 'a') {
    header("location: ../login.php");
    exit();
}

if (isset($_GET["id"])) {
    include "../config.php";
    $id = intval($_GET["id"]); // ensures it's a number

    // prepared statements to prevent SQL injection
    $stmt = $database->prepare("DELETE FROM schedule WHERE scheduleid = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("location: schedule.php");
    exit();
}
