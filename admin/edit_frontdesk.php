<?php
// Import database
include "../config.php";

// Ensure database connection uses MySQLi
if (!$database instanceof mysqli) {
    die("Database connection error");
}

if ($_POST) {
    $id = $_POST["id00"] ?? null;
    $name = trim($_POST['name']);
    $oldemail = trim($_POST['oldemail']);
    $email = trim($_POST['email']);
    $tele = trim($_POST['tele']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    // Password validation regex: min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char
    $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    if (!preg_match($passwordRegex, $password)) {
        $error = '3'; // Invalid password format
    } elseif ($password == $cpassword) {
        $error = '3'; // Default error, will be overwritten if successful

        // Check if email already exists for another frontdesk
        $stmt = $database->prepare("SELECT frontdesk.fdid FROM frontdesk INNER JOIN webuser ON frontdesk.fdemail=webuser.email WHERE webuser.email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $id2 = $result->fetch_assoc()["fdid"];
        } else {
            $id2 = $id;
        }
        $stmt->close();

        if ($id2 != $id) {
            $error = '1'; // Email already exists for another frontdesk
        } else {
            // Hash the password
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Update frontdesk table
            $stmt = $database->prepare("UPDATE frontdesk SET fdemail=?, fdname=?, fdpassword=?, fdtel=? WHERE fdid=?");
            $stmt->bind_param("ssssi", $email, $name, $hashedPassword, $tele, $id);
            $stmt->execute();
            $stmt->close();

            // Update webuser table
            $stmt = $database->prepare("UPDATE webuser SET email=? WHERE email=?");
            $stmt->bind_param("ss", $email, $oldemail);
            $stmt->execute();
            $stmt->close();

            $error = '4'; // Success
        }
    } else {
        $error = '2'; // Passwords don't match
    }
} else {
    $error = '3'; // Invalid request
}

// Close database connection if needed
$database->close();

header("location: frontdesk.php?action=edit&error=" . $error . "&id=" . $id);
