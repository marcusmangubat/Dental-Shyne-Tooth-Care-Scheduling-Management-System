<?php
session_start();

// Check if user is logged in and has the correct user type
if (!isset($_SESSION['user']) || empty($_SESSION['user']) || !isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'd') {
    header("Location: ../login.php");
    exit();
}

$useremail = $_SESSION['user'];

include "../config.php";

try {
    // Prepare and execute the database query
    $stmt = $database->prepare("SELECT * FROM doctor WHERE docemail = ?");
    $stmt->bind_param("s", $useremail);
    $stmt->execute();
    $userrow = $stmt->get_result();

    // Check if a record was found
    if ($userrow->num_rows === 0) {
        // Handle case where no user is found (e.g., log out or redirect)
        header("Location: ../login.php");
        exit();
    }

    // Fetch user data
    $userfetch = $userrow->fetch_assoc();
    $userid = $userfetch['docid'];
    $username = $userfetch['docname'];

    $stmt->close();
} catch (Exception $e) {
    // Handle database errors (log the error securely in production)
    die("Database error: " . $e->getMessage());
}

if ($_GET) {
    include "../config.php";
    $id = $_GET["id"];
    $result001 = $database->query("SELECT * FROM doctor WHERE docid = $id;");
    $email = ($result001->fetch_assoc())["docemail"];
    $sql = $database->query("DELETE FROM webuser WHERE email = '$email'; ");
    $sql = $database->query("DELETE FROM doctor WHERE docemail = '$email'; ");

    header("location: ../logout.php");
}
