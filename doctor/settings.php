<?php
session_start();

// Check if user is logged in and has the correct usertype
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
        session_destroy();
        header("Location: ../login.php?error=user_not_found");
        $stmt->close();
        exit();
    }

    // Fetch user data
    $userfetch = $userrow->fetch_assoc();
    $userid = $userfetch['docid'];
    $username = $userfetch['docname'];

    $stmt->close();
} catch (Exception $e) {
    // Log the error securely in production
    error_log("Database error: " . $e->getMessage());
    header("Location: ../login.php?error=database_error");
    exit();
}

date_default_timezone_set('Asia/Manila');
$today = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="icon" type="image/png" href="../photo/logo_cropped.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../css/tailwind.js"></script>
    <link rel="stylesheet" href="../css/aindex.css">
    <link rel="stylesheet" href="../css/table.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .sidebar ul li a {
            color: black;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px;
            transition: all 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            border-radius: 4px;
        }

        .sidebar ul li a:hover,
        .sidebar ul li a.active {
            background-color: #00A7B5;
            color: white;
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0, 167, 181, 0.3);
            border-radius: 4px;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        .fade-in-up-delay {
            animation: fadeInUp 0.8s ease-out;
        }
    </style>
</head>

<body>
    <!-- Overlay for mobile -->
    <div id="overlay" class="overlay hidden"></div>
    <div class="containr">
        <div id="sidebar" class="sidebar">
            <div class="p-6 text-center border-b border-gray-200">
                <img src="../photo/form-logo.png" alt="Dental Shyne Logo" class="mx-auto mb-4">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Dr. <?php echo htmlspecialchars(explode(" ", trim($username))[0]); ?></h2>
                <p class="text-xs text-gray-500"><?php echo htmlspecialchars(substr($useremail, 0, 30)); ?></p>
            </div>
            <hr>
            <ul class="space-y-2">
                <li>
                    <a href="index.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 ">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="appointment.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200">
                        <i class="fas fa-clipboard-list"></i> Appointment
                    </a>
                </li>
                <li>
                    <a href="schedule.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200">
                        <i class="fas fa-clock"></i> Schedule
                    </a>
                </li>
                <li>
                    <a href="patient.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 ">
                        <i class="fas fa-users"></i> Patient
                    </a>
                </li>
                <li>
                    <a href="settings.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 active text-blue-600 font-semibold">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <div class="p- text-center border-b border-gray-200">
                    <hr class="border-t border-gray-200 my-2">
                </div>
                <li class="list-none">
                    <a href="../logout.php" id="dashboard-logout" class="flex items-center gap-2 px-4 py-2">
                        <i class="fas fa-sign-out-alt text-red-500 hover:text-white transition-colors"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
        <div class="main-content flex-1 p-6">
            <div class="header">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <a href="settings.php">
                        <button class="back-btn" style="display: flex; align-items: center; background: #fff; border: 1px solid #ccc; border-radius: 8px; padding: 1px 5px; cursor: pointer; gap: 2px;">
                            <img src="../icons/arrow-left.png" alt="Back Icon" style="width: 16px; height: 16px;">
                            <span>Back</span>
                        </button>
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900" style="margin: 0;">Settings</h1>
                </div>
                <div class="user"></div>
                <div class="datetime" id="datetime"></div>
            </div>
            <div class="flex items-center justify-center min-h-screen fade-in-up-delay">
                <div class="w-full max-w-4xl mx-auto self-start mt-20">
                    <div class="bg-white rounded-lg shadow-lg p-6 md:p-8 items-center">
                        <!-- Account Settings -->
                        <a href="?action=edit&id=<?php echo htmlspecialchars($userid); ?>&error=0" class="block mb-4 fade-in-up-delay">
                            <div class="flex items-center p-4 hover:bg-gray-50 rounded-lg transition-colors">
                                <i class="fa-solid fa-user-gear text-gray-600 text-2xl"></i>
                                <div class="w-12 h-12 bg-cover bg-center" style="background-image: url('../img/icons/doctors-hover.svg');"></div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-800">Manage Account</h3>
                                    <p class="text-sm text-gray-600">Manage Profile & Password</p>
                                </div>
                            </div>
                        </a>
                        <hr>
                        <!-- View Account Details -->
                        <a href="?action=view&id=<?php echo htmlspecialchars($userid); ?>" class="block mb-4 fade-in-up-delay">
                            <div class="flex items-center p-4 hover:bg-gray-50 rounded-lg transition-colors">
                                <i class="fa-solid fa-id-card text-blue-500 text-2xl"></i>
                                <div class="w-12 h-12 bg-cover bg-center" style="background-image: url('../img/icons/view-iceblue.svg');"></div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-gray-800">View Profile Details</h3>
                                    <p class="text-sm text-gray-600">View Personal Data</p>
                                </div>
                            </div>
                        </a>
                        <hr>
                        <!-- Delete Account -->
                        <a href="?action=drop&id=<?php echo htmlspecialchars($userid); ?>&name=<?php echo htmlspecialchars($username); ?>" class="block fade-in-up-delay">
                            <div class="flex items-center p-4 hover:bg-gray-50 rounded-lg transition-colors">
                                <i class="fa-solid fa-user-slash text-red-500 text-2xl"></i>
                                <div class="w-12 h-12 bg-cover bg-center" style="background-image: url('../img/icons/patients-hover.svg');"></div>
                                <div class="ml-4">
                                    <h3 class="text-lg font-semibold text-red-500">Deactivate Permanently</h3>
                                    <p class="text-sm text-gray-600">Will Permanently Delete Your Account</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    if ($_GET) {
        $id = $_GET["id"];
        $action = $_GET["action"];
        if ($action == 'drop') {
            $nameget = $_GET["name"];
            echo '
                <div id="popup1" class="fade-in-up fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                        <div class="flex justify-end">
                            <a href="settings.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                        </div>
                        <div class="mt-4 text-center">
                            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Are you sure?</h2>
                            <p class="text-gray-700 mb-6">You want to delete your account<br>(' . htmlspecialchars(substr($nameget, 0, 40)) . ')</p>
                            <div class="flex justify-center space-x-4">
                                <a href="delete_doctor.php?id=' . htmlspecialchars($id) . '" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none">Yes</a>
                                <a href="settings.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none">No</a>
                            </div>
                        </div>
                    </div>
                </div>';
        } else if ($action == 'view') {
            $stmt = $database->prepare("SELECT * FROM doctor WHERE docid = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                header("Location: settings.php?error=invalid_id");
                exit();
            }
            $row = $result->fetch_assoc();
            $stmt->close();

            $name = $row["docname"];
            $email = $row["docemail"];
            $spe = $row["specialties"];
            $stmt = $database->prepare("SELECT sname FROM specialties WHERE id = ?");
            $stmt->bind_param("i", $spe);
            $stmt->execute();
            $spcil_res = $stmt->get_result();
            $spcil_array = $spcil_res->fetch_assoc();
            $spcil_name = $spcil_array['sname'] ?? 'Unknown';
            $stmt->close();
            $tele = $row['doctel'];

            echo '
                <div id="popup1" class="fade-in-up fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                        <div class="flex justify-end">
                            <a href="settings.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                        </div>
                        <div class="mt-4">
                            <h2 class="text-2xl font-semibold text-gray-800 text-center mb-6">Doctor Details</h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><strong>Name</strong></label>
                                    <p class="mt-1 text-gray-900">' . htmlspecialchars($name) . '</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><strong>Email</strong></label>
                                    <p class="mt-1 text-gray-900">' . htmlspecialchars($email) . '</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><strong>Specialties</strong></label>
                                    <p class="mt-1 text-gray-900">' . htmlspecialchars($spcil_name) . '</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><strong>Phone Number</strong></label>
                                    <p class="mt-1 text-gray-900">' . htmlspecialchars($tele) . '</p>
                                </div>
                            </div>
                            <div class="flex justify-center mt-6">
                                <a href="settings.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">Close</a>
                            </div>         
                        </div>
                    </div>
                </div>';
        } elseif ($action == 'edit') {
            $stmt = $database->prepare("SELECT * FROM doctor WHERE docid = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows === 0) {
                header("Location: settings.php?error=invalid_id");
                exit();
            }
            $row = $result->fetch_assoc();
            $stmt->close();

            $name = $row["docname"];
            $email = $row["docemail"];
            $spe = $row["specialties"];
            $stmt = $database->prepare("SELECT sname FROM specialties WHERE id = ?");
            $stmt->bind_param("i", $spe);
            $stmt->execute();
            $spcil_res = $stmt->get_result();
            $spcil_array = $spcil_res->fetch_assoc();
            $spcil_name = $spcil_array['sname'] ?? 'Unknown';
            $stmt->close();
            $tele = $row['doctel'];

            $error_1 = $_GET["error"];
            $errorlist = array(
                '1' => '<p class="text-red-500 text-sm text-center">This email address is already in use.</p>',
                '2' => '<p class="text-red-500 text-sm text-center">Password Confirmation Error! Reconfirm Password</p>',
                '3' => '<p class="text-red-500 text-sm text-center">Invalid input or server error.</p>',
                '4' => "",
                '0' => '',
            );
            if ($error_1 != '4') {
                echo '
                    <div id="popup1" class="fade-in-up fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                            <div class="flex justify-end">
                                <a href="settings.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                            </div>
                            <div class="mt-4">
                                <h2 class="text-2xl font-semibold text-gray-800 text-center mb-6">Edit Dentist Details</h2>
                                <div class="text-red-500 text-center mb-4">' . $errorlist[$error_1] . '</div>
                                <form action="edit_doctor.php" method="POST" onsubmit="return validatePassword()" class="space-y-6">
                                    <div>
                                        <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                        <input type="text" name="name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Dentist Name" value="' . htmlspecialchars($name) . '" required>
                                    </div>
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                        <input type="hidden" value="' . htmlspecialchars($id) . '" name="id00">
                                        <input type="hidden" name="oldemail" value="' . htmlspecialchars($email) . '">
                                        <input type="email" name="email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Email Address" value="' . htmlspecialchars($email) . '" required>
                                    </div>
                                    <div>
                                        <label for="tele" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                        <input type="text" name="tele" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Phone Number" value="' . htmlspecialchars($tele) . '" required>
                                    </div>
                                    <div>
                                        <label for="spec" class="block text-sm font-medium text-gray-700">Specialty (Current: ' . htmlspecialchars($spcil_name) . ')</label>
                                        <select name="spec" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            ';
                $list11 = $database->query("SELECT * FROM specialties ORDER BY sname ASC");
                for ($y = 0; $y < $list11->num_rows; $y++) {
                    $row00 = $list11->fetch_assoc();
                    $sn = $row00["sname"];
                    $id00 = $row00["id"];
                    echo "<option value='$id00'" . ($id00 == $spe ? " selected" : "") . ">" . htmlspecialchars($sn) . "</option>";
                }
                echo '
                                        </select>
                                    </div>
                                    <div>
                                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                        <div class="relative">
                                            <input type="password" id="password" name="password" 
                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                                                focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Define a Password" required 
                                                oninput="validatePasswordDynamic()">
                                            <button type="button" id="togglePassword" 
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hidden"
                                                onclick="togglePasswordVisibility(\'password\', this)">
                                                <i class="fa-solid fa-eye-slash"></i>
                                            </button>
                                        </div>
                                        <p id="passwordError" class="text-red-500 text-sm text-center mt-1"></p>
                                    </div>
                                    <div>
                                        <label for="cpassword" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                        <div class="relative">
                                            <input type="password" id="cpassword" name="cpassword" 
                                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm 
                                                focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Confirm Password" required disabled 
                                                oninput="toggleConfirmPasswordIcon()">
                                            <button type="button" id="toggleCPassword" 
                                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hidden"
                                                onclick="togglePasswordVisibility(\'cpassword\', this)">
                                                <i class="fa-solid fa-eye-slash"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="flex justify-center space-x-4">
                                        <input type="reset" value="Reset" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 cursor-pointer">
                                        <input type="submit" value="Save" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                    </div>
                                </form>
                                <script>
                                    function validatePasswordDynamic() {
                                        var password = document.getElementById("password").value;
                                        var confirmPasswordInput = document.getElementById("cpassword");
                                        var passwordError = document.getElementById("passwordError");
                                        var togglePasswordBtn = document.getElementById("togglePassword");
                                        var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[@$!%*?&])[A-Za-z\\d@$!%*?&]{8,}$/;
                                        if (password.length > 0) {
                                            togglePasswordBtn.classList.remove("hidden");
                                            if (!passwordRegex.test(password)) {
                                                passwordError.textContent = "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.";
                                                confirmPasswordInput.disabled = true;
                                            } else {
                                                passwordError.textContent = "";
                                                confirmPasswordInput.disabled = false;
                                            }
                                        } else {
                                            togglePasswordBtn.classList.add("hidden");
                                            passwordError.textContent = "";
                                            confirmPasswordInput.disabled = true;
                                        }
                                    }
                                    function toggleConfirmPasswordIcon() {
                                        var confirmPassword = document.getElementById("cpassword").value;
                                        var toggleCPasswordBtn = document.getElementById("toggleCPassword");
                                        if (confirmPassword.length > 0) {
                                            toggleCPasswordBtn.classList.remove("hidden");
                                        } else {
                                            toggleCPasswordBtn.classList.add("hidden");
                                        }
                                    }
                                    function validatePassword() {
                                        var password = document.getElementById("password").value;
                                        var confirmPassword = document.getElementById("cpassword").value;
                                        var passwordError = document.getElementById("passwordError");
                                        var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\\d)(?=.*[@$!%*?&])[A-Za-z\\d@$!%*?&]{8,}$/;
                                        if (!passwordRegex.test(password)) {
                                            passwordError.textContent = "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.";
                                            return false;
                                        }
                                        if (password !== confirmPassword) {
                                            passwordError.textContent = "Passwords do not match!";
                                            return false;
                                        }
                                        return true;
                                    }
                                    function togglePasswordVisibility(inputId, btn) {
                                        var input = document.getElementById(inputId);
                                        var icon = btn.querySelector("i");
                                        if (input.type === "password") {
                                            input.type = "text";
                                            icon.classList.remove("fa-eye-slash");
                                            icon.classList.add("fa-eye");
                                        } else {
                                            input.type = "password";
                                            icon.classList.remove("fa-eye");
                                            icon.classList.add("fa-eye-slash");
                                        }
                                    }
                                </script>
                            </div>
                        </div>
                    </div>';
            } else {
                echo '
                    <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                            <div class="flex justify-end">
                                <a href="settings.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                            </div>
                            <div class="mt-4 text-center">
                                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Edited Successfully!</h2>
                                <div class="flex justify-center">
                                    <a href="settings.php" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">OK</a>
                                </div>
                            </div>
                        </div>
                    </div>';
            }
        }
    }
    ?>
    <script src="../js/active-link.js"></script>
    <script src="../js/date-time.js"></script>
</body>

</html>