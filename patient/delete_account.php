<?php
session_start();

if (!isset($_SESSION["user"]) || $_SESSION["user"] == "" || $_SESSION['usertype'] != 'p') {
    header("location: ../login.php");
    exit();
}

include "../config.php";

$useremail = $_SESSION["user"];

// get loggedin users info
$stmt = $database->prepare("SELECT pid, pname FROM patient WHERE pemail = ?");
$stmt->bind_param("s", $useremail);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header("location: ../login.php");
    exit();
}

$userid = $user["pid"];
$username = $user["pname"];

// delete only if the loggedin user matches the id
if (isset($_GET["id"])) {
    $id = intval($_GET["id"]);
    if ($id === $userid) { // Prevent deleting someone else's account
        $stmt = $database->prepare("DELETE FROM webuser WHERE email = ?");
        $stmt->bind_param("s", $useremail);
        $stmt->execute();

        $stmt = $database->prepare("DELETE FROM patient WHERE pemail = ?");
        $stmt->bind_param("s", $useremail);
        $stmt->execute();

        session_destroy();
        header("location: ../logout.php");
        exit();
    } else {
        // redirect or show error if someone tries to delete another account
        header("location: ../login.php");
        exit();
    }
}
