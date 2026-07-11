<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Frontdesk</title>
    <link rel="icon" type="image/png" href="../photo/logo_cropped.png">
</head>

<body>
    <?php
    session_start();

    // Check if user is logged in and is admin
    if (!isset($_SESSION["user"]) || $_SESSION["user"] == "" || $_SESSION['usertype'] != 'a') {
        header("location: ../login.php");
        exit();
    }
    //connection
    include "../config.php";

    //ensure database connection
    if (!$database instanceof mysqli) {
        die("Database connection error");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name     = trim($_POST['name']);
        $email    = trim($_POST['email']);
        $tele     = trim($_POST['tele']);
        $password = $_POST['password'];
        $cpassword = $_POST['cpassword'];

        //password validate
        $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        if (!preg_match($passwordRegex, $password)) {
            $error = '3';
            // Check if passwords match
        } elseif ($password !== $cpassword) {
            $error = '2'; // Passwords do not match
        } else {
            // Check if email already exists
            $stmt = $database->prepare("SELECT email FROM webuser WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $error = '1'; // Email already exists
            } else {
                // Hash the password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                // Insert into frontdesk table
                $stmt1 = $database->prepare("INSERT INTO frontdesk (fdemail, fdname, fdpassword, fdtel) VALUES (?, ?, ?, ?)");
                $stmt1->bind_param("ssss", $email, $name, $hashed_password, $tele);
                $stmt1->execute();

                // Insert into webuser table
                $stmt2 = $database->prepare("INSERT INTO webuser (email, usertype) VALUES (?, 'f')");
                $stmt2->bind_param("s", $email);
                $stmt2->execute();

                $error = '4'; // Success
            }
            $stmt->close();
        }
    } else {
        $error = '0'; // No POST data
    }

    header("location: frontdesk.php?action=add&error=" . $error);
    exit();
    ?>

</body>

</html>