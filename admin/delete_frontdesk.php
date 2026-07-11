<?php
session_start();

if (!isset($_SESSION["user"]) || $_SESSION["user"] == "" || $_SESSION['usertype'] != 'a') {
    header("location: ../login.php");
    exit();
}

if (isset($_GET["id"])) {
    include("../config.php");

    $id = intval($_GET["id"]); // Validate numeric input

    // get email safely
    $stmt = $database->prepare("SELECT fdemail FROM frontdesk WHERE fdid = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $email = $row["fdemail"];

        // delete from both tables 
        $stmt1 = $database->prepare("DELETE FROM webuser WHERE email = ?");
        $stmt1->bind_param("s", $email);
        $stmt1->execute();

        $stmt2 = $database->prepare("DELETE FROM frontdesk WHERE fdemail = ?");
        $stmt2->bind_param("s", $email);
        $stmt2->execute();
    }

    header("location: frontdesk.php");
    exit();
}
