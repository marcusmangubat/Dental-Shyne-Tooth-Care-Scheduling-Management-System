<?php
session_start();

// check if user is logged in and is admin
if (!isset($_SESSION["user"]) || $_SESSION["user"] == "" || $_SESSION['usertype'] != 'a') {
    header("location: ../login.php");
    exit();
}

if ($_GET) {
    include("../config.php");

    if (!$database instanceof mysqli) {
        die("Database connection error");
    }

    $id = $_GET["id"];

    // fetch the doctor's email and photo path
    $stmt = $database->prepare("SELECT docemail, docphoto FROM doctor WHERE docid = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $email = $row["docemail"];
        $photo = $row["docphoto"];

        // delete the photo file if it exists and is not the default image
        if ($photo && $photo !== '../uploads/doctors/default-doctor.png') {
            $filePath = realpath(__DIR__ . "/../uploads/doctors/" . basename($photo));
            if ($filePath && file_exists($filePath)) {
                unlink($filePath);
            }
        }

        // delete from webuser table
        $stmt = $database->prepare("DELETE FROM webuser WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->close();

        // delete from doctor table
        $stmt = $database->prepare("DELETE FROM doctor WHERE docid = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }

    $database->close();

    header("location: doctors.php");
    exit();
} else {
    header("location: doctors.php");
    exit();
}
