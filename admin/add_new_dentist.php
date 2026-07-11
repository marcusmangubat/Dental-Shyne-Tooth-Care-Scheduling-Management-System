<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor</title>
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

    // Database connection
    include "../config.php";

    // Ensure database connection
    if (!$database instanceof mysqli) {
        die("Database connection error");
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name     = trim($_POST['name']);
        $spec     = trim($_POST['spec']);
        $email    = trim($_POST['email']);
        $tele     = trim($_POST['tele']);
        $password = $_POST['password'];
        $cpassword = $_POST['cpassword'];

        // Password validation
        $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        if (!preg_match($passwordRegex, $password)) {
            $error = '3'; // Invalid password format
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
                // Handle file upload
                $photoPath = null;
                if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                    $file = $_FILES['photo'];
                    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
                    $maxFileSize = 2 * 1024 * 1024; // 2MB
                    $uploadDir = '../uploads/doctors/';
                    $fileName = uniqid() . '_' . basename($file['name']);
                    $filePath = $uploadDir . $fileName;

                    // Validate file type
                    if (!in_array($file['type'], $allowedTypes)) {
                        $error = '5'; // Invalid file type
                    } elseif ($file['size'] > $maxFileSize) {
                        $error = '6'; // File too large
                    } else {
                        // Validate image dimensions (2x2 aspect ratio, e.g., 128x128 pixels)
                        list($width, $height) = getimagesize($file['tmp_name']);
                        if ($width != $height || $width < 100 || $height < 100) {
                            $error = '7'; // Invalid image dimensions
                        } else {
                            // Move uploaded file
                            if (move_uploaded_file($file['tmp_name'], $filePath)) {
                                $photoPath = $filePath;
                            } else {
                                $error = '8'; // File upload failed
                            }
                        }
                    }
                } else {
                    $photoPath = '../uploads/doctors/default-doctor.png'; // Default image if no photo uploaded
                }

                // Proceed with database insertion if no file-related errors
                if (!isset($error) || $error == '0') {
                    // Hash the password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                    // Insert into doctor table
                    $stmt1 = $database->prepare("INSERT INTO doctor (docemail, docname, docpassword, doctel, specialties, docphoto) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt1->bind_param("ssssss", $email, $name, $hashed_password, $tele, $spec, $photoPath);
                    $stmt1->execute();

                    // Insert into webuser table
                    $stmt2 = $database->prepare("INSERT INTO webuser (email, usertype) VALUES (?, 'd')");
                    $stmt2->bind_param("s", $email);
                    $stmt2->execute();

                    $error = '4'; // Success
                    $stmt1->close();
                    $stmt2->close();
                }
            }
            $stmt->close();
        }
    } else {
        $error = '0'; // No POST data
    }

    header("location: doctors.php?action=add&error=" . $error);
    exit();
    ?>
</body>

</html>