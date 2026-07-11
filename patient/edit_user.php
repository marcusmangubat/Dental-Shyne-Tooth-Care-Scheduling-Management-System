<?php
session_start();

// Import database
include "../config.php";

// Ensure database connection uses MySQLi
if (!$database instanceof mysqli) {
    die("Database connection error");
}

if ($_POST) {
    $name = $_POST['name'];
    $oldemail = $_POST["oldemail"];
    $address = $_POST['address'];
    $email = $_POST['email'];
    $tele = $_POST['tele'];
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];
    $id = $_POST['id00'];

    // Password validation regex: min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char
    $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    // Validate inputs
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '3'; // Invalid email format
        header("location: settings.php?action=edit&error=$error&id=$id");
        exit();
    }
    if (!preg_match('/^\+?\d{10,15}$/', $tele)) {
        $error = '3'; // Invalid phone number format
        header("location: settings.php?action=edit&error=$error&id=$id");
        exit();
    }
    if (!preg_match($passwordRegex, $password)) {
        $error = '3'; // Invalid password format
        header("location: settings.php?action=edit&error=$error&id=$id");
        exit();
    }
    if ($password !== $cpassword) {
        $error = '2'; // Passwords don't match
        header("location: settings.php?action=edit&error=$error&id=$id");
        exit();
    }

    // Check if the new email already exists in the webuser table (excluding the current user's old email)
    $stmt = $database->prepare("SELECT email FROM webuser WHERE email = ? AND email != ?");
    $stmt->bind_param("ss", $email, $oldemail);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $error = '1'; // Email already exists in webuser
        $stmt->close();
        header("location: settings.php?action=edit&error=$error&id=$id");
        exit();
    }
    $stmt->close();

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Update patient table
    $stmt = $database->prepare("UPDATE patient SET pemail = ?, pname = ?, ppassword = ?, ptel = ?, paddress = ? WHERE pid = ?");
    $stmt->bind_param("sssssi", $email, $name, $hashedPassword, $tele, $address, $id);
    $result1 = $stmt->execute();
    $stmt->close();

    // Update webuser table
    $stmt = $database->prepare("UPDATE webuser SET email = ? WHERE email = ?");
    $stmt->bind_param("ss", $email, $oldemail);
    $result2 = $stmt->execute();
    $stmt->close();

    if ($result1 && $result2) {
        // Update session with new email
        $_SESSION["user"] = $email;
        $error = '4'; // Success
    } else {
        error_log("Database update failed: " . $database->error);
        $error = '3'; // Database update failed
    }
} else {
    $error = '3'; // Invalid request
}

// Close database connection
$database->close();
header("location: settings.php?action=edit&error=$error&id=$id");
exit();
