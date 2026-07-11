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
    $spec = trim($_POST['spec']);
    $email = trim($_POST['email']);
    $tele = trim($_POST['tele']);
    $password = $_POST['password'];
    $cpassword = $_POST['cpassword'];

    // Password validation regex: min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char
    $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

    if (!preg_match($passwordRegex, $password)) {
        $error = '3'; // Invalid password format
    } elseif ($password !== $cpassword) {
        $error = '2'; // Passwords don't match
    } else {
        // Check if email already exists for another doctor
        $stmt = $database->prepare("SELECT doctor.docid FROM doctor INNER JOIN webuser ON doctor.docemail=webuser.email WHERE webuser.email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $id2 = $result->fetch_assoc()["docid"];
        } else {
            $id2 = $id;
        }
        $stmt->close();

        if ($id2 != $id) {
            $error = '1'; // Email already exists for another doctor
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
                            // Optionally, delete the old photo if it exists and is not the default
                            $stmt = $database->prepare("SELECT docphoto FROM doctor WHERE docid=?");
                            $stmt->bind_param("i", $id);
                            $stmt->execute();
                            $result = $stmt->get_result();
                            $oldPhoto = $result->fetch_assoc()['docphoto'];
                            $stmt->close();
                            if ($oldPhoto && $oldPhoto !== '../uploads/doctors/default-doctor.png' && file_exists($oldPhoto)) {
                                unlink($oldPhoto);
                            }
                        } else {
                            $error = '8'; // File upload failed
                        }
                    }
                }
            }

            // Proceed with database update if no file-related errors
            if (!isset($error) || $error == '0') {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Update doctor table (include photo if uploaded, otherwise retain existing photo)
                if ($photoPath) {
                    $stmt = $database->prepare("UPDATE doctor SET docemail=?, docname=?, docpassword=?, doctel=?, specialties=?, docphoto=? WHERE docid=?");
                    $stmt->bind_param("ssssssi", $email, $name, $hashedPassword, $tele, $spec, $photoPath, $id);
                } else {
                    $stmt = $database->prepare("UPDATE doctor SET docemail=?, docname=?, docpassword=?, doctel=?, specialties=? WHERE docid=?");
                    $stmt->bind_param("sssssi", $email, $name, $hashedPassword, $tele, $spec, $id);
                }
                $stmt->execute();
                $stmt->close();

                // Update webuser table
                $stmt = $database->prepare("UPDATE webuser SET email=? WHERE email=?");
                $stmt->bind_param("ss", $email, $oldemail);
                $stmt->execute();
                $stmt->close();

                $error = '4'; // Success
            }
        }
    }
} else {
    $error = '3'; // Invalid request
}

// Close database connection
$database->close();

header("location: doctors.php?action=edit&error=" . $error . "&id=" . $id);
