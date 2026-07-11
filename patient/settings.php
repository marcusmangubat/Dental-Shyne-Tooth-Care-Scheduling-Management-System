<?php
session_start();

// Check if user is logged in and has the correct usertype
if (!isset($_SESSION["user"]) || empty($_SESSION["user"]) || $_SESSION['usertype'] != 'p') {
    header("location: ../login.php");
    exit();
}

$useremail = $_SESSION["user"];

// Include database connection file
include "../config.php";

try {
    // Use prepared statement to prevent SQL injection
    $stmt = $database->prepare("SELECT * FROM patient WHERE pemail = ?");
    $stmt->bind_param("s", $useremail);
    $stmt->execute();
    $userrow = $stmt->get_result();

    if ($userrow->num_rows > 0) {
        $userfetch = $userrow->fetch_assoc();
        $userid = $userfetch['pid'];
        $username = $userfetch['pname'];
    } else {
        // Handle case where user is not found
        session_destroy();
        header("location: ../login.php?error=user_not_found");
        $stmt->close();
        exit();
    }

    $stmt->close();
} catch (Exception $e) {
    // Log the error securely in production
    error_log("Database error: " . $e->getMessage());
    header("location: ../login.php?error=database_error");
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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="icon" type="image/png" href="../photo/logo_cropped.png">
    <style>
        .sidebar a {
            transition: all 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #00A7B5;
            color: white;
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0, 167, 181, 0.3);
        }

        @keyframes float {
            0% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }

            100% {
                transform: translateY(0);
            }
        }

        .nodata-img {
            animation: float 3s ease-in-out infinite;
        }

        .welcome-card {
            background: linear-gradient(135deg, #00A7B5 0%, #0891b2 100%);
            color: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 167, 181, 0.2);
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
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

        .sidebar {
            transition: transform 0.3s ease-in-out;
        }

        @media (max-width: 640px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }
        }
    </style>
</head>

<body class="bg-gray-100 font-sans">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="sidebar fixed top-0 left-0 w-64 bg-white shadow-lg h-full z-20 sm:translate-x-0">
            <div class="p-6 text-center border-b border-gray-200">
                <img src="../photo/form-logo.png" alt="Dental Shyne Logo" class="mx-auto mb-4 w-24">
                <h2 class="text-sm font-semibold text-gray-800"><?php echo htmlspecialchars(explode(" ", trim($username))[0]); ?></h2>
                <p class="text-xs text-gray-500"><?php echo htmlspecialchars(substr($useremail, 0, 30)); ?></p>
            </div>
            <ul class="space-y-2 p-4">
                <li><a href="index.php" class="flex items-center gap-2 p-3 rounded-lg hover:bg-gray-200"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="schedule.php" class="flex items-center gap-2 p-3 rounded-lg hover:bg-gray-200"><i class="fas fa-clock"></i> Calendar Schedule</a></li>
                <li><a href="appointment.php" class="flex items-center gap-2 p-3 rounded-lg hover:bg-gray-200"><i class="fas fa-clipboard-list"></i> My Appointment</a></li>
                <li><a href="settings.php" class="flex items-center gap-2 p-3 rounded-lg bg-gray-200 active"><i class="fas fa-cog"></i> Settings</a></li>
                <hr class="border-t border-gray-200 my-2">
                <li class="list-none">
                    <a href="../logout.php" id="dashboard-logout" class="flex items-center gap-2 px-4 py-2">
                        <i class="fas fa-sign-out-alt text-red-500 hover:text-white transition-colors"></i> Logout
                    </a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6 sm:ml-64">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center gap-4">
                        <button id="toggleSidebar" class="sm:hidden p-2 text-gray-600 hover:text-gray-800">
                            <i class="fas fa-bars text-2xl"></i>
                        </button>
                        <h1 class="text-3xl font-bold text-gray-900">Settings</h1>
                    </div>
                    <div class="text-gray-600" id="datetime"></div>
                </div>

                <!-- Settings Options -->
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

            <!-- Modals -->
            <?php
            if (isset($_GET["action"])) {
                $id = $_GET["id"];
                $action = $_GET["action"];
                if ($action == 'drop') {
                    $nameget = $_GET["name"];
                    echo '
                    <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
                        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                            <div class="flex justify-end">
                                <a href="settings.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                            </div>
                            <div class="mt-4 text-center">
                                <h2 class="text-xl font-semibold text-gray-800 mb-6">Are you sure?</h2>
                                <p class="text-gray-600 mb-6">You want to delete your account<br>(' . htmlspecialchars(substr($nameget, 0, 40)) . ')</p>
                                <div class="flex justify-center space-x-4">
                                    <a href="delete_account.php?id=' . htmlspecialchars($id) . '" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Yes</a>
                                    <a href="settings.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">No</a>
                                </div>
                            </div>
                        </div>
                    </div>';
                } elseif ($action == 'view') {
                    $stmt = $database->prepare("SELECT * FROM patient WHERE pid = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows === 0) {
                        header("location: settings.php?error=invalid_id");
                        exit();
                    }
                    $row = $result->fetch_assoc();
                    $stmt->close();

                    $name = $row["pname"];
                    $email = $row["pemail"];
                    $address = $row["paddress"];
                    $dob = $row["pdob"];
                    $tele = $row["ptel"];
                    echo '
                    <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
                        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                            <div class="flex justify-end">
                                <a href="settings.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                            </div>
                            <div class="mt-4">
                                <h2 class="text-2xl font-semibold text-gray-800 text-center mb-6">Your Personal Details</h2>
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
                                        <label class="block text-sm font-medium text-gray-700"><strong>Phone Number</strong></label>
                                        <p class="mt-1 text-gray-900">' . htmlspecialchars($tele) . '</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"><strong>Address</strong></label>
                                        <p class="mt-1 text-gray-900">' . htmlspecialchars($address) . '</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"><strong>Date of Birth</strong></label>
                                        <p class="mt-1 text-gray-900">' . htmlspecialchars(date("F d, Y", strtotime($dob))) . '</p>
                                    </div>
                                </div>
                                <div class="flex justify-center mt-6">
                                    <a href="settings.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Close</a>
                                </div>         
                            </div>
                        </div>
                    </div>';
                } elseif ($action == 'edit') {
                    $stmt = $database->prepare("SELECT * FROM patient WHERE pid = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    if ($result->num_rows === 0) {
                        header("location: settings.php?error=invalid_id");
                        exit();
                    }
                    $row = $result->fetch_assoc();
                    $stmt->close();

                    $name = $row["pname"];
                    $email = $row["pemail"];
                    $address = $row["paddress"];
                    $tele = $row["ptel"];
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
                        <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
                            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                                <div class="flex justify-end">
                                    <a href="settings.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                                </div>
                                <div class="mt-4">
                                    <h2 class="text-2xl font-semibold text-gray-800 text-center mb-6">Edit Your Account Details</h2>
                                    <div class="text-red-500 text-center mb-4">' . $errorlist[$error_1] . '</div>
                                    <form action="edit_user.php" method="POST" class="space-y-6" onsubmit="return validatePassword()">
                                        <div>
                                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                                            <input type="text" name="name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Your Name" value="' . htmlspecialchars($name) . '" required>
                                        </div>
                                        <div>
                                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                                            <input type="hidden" value="' . htmlspecialchars($id) . '" name="id00">
                                            <input type="hidden" name="oldemail" value="' . htmlspecialchars($email) . '">
                                            <input type="email" name="email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Email Address" value="' . htmlspecialchars($email) . '" required>
                                        </div>
                                        <div>
                                            <label for="tele" class="block text-sm font-medium text-gray-700">Phone Number</label>
                                            <input type="text" name="tele" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Phone Number" value="' . htmlspecialchars($tele) . '" required>
                                        </div>
                                        <div>
                                            <label for="address" class="block text-sm font-medium text-gray-700">Address</label>
                                            <input type="text" name="address" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Address" value="' . htmlspecialchars($address) . '" required>
                                        </div>
                                        <div>
                                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                            <div class="relative">
                                                <input type="password" id="password" name="password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Define a Password" required oninput="validatePasswordDynamic()">
                                                <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hidden" onclick="togglePasswordVisibility(\'password\', this)">
                                                    <i class="fa-solid fa-eye-slash"></i>
                                                </button>
                                            </div>
                                            <p id="passwordError" class="text-red-500 text-sm text-center mt-1"></p>
                                        </div>
                                        <div>
                                            <label for="cpassword" class="block text-sm font-medium text-gray-700">Confirm Password</label>
                                            <div class="relative">
                                                <input type="password" id="cpassword" name="cpassword" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Confirm Password" required disabled oninput="toggleConfirmPasswordIcon()">
                                                <button type="button" id="toggleCPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hidden" onclick="togglePasswordVisibility(\'cpassword\', this)">
                                                    <i class="fa-solid fa-eye-slash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="flex justify-center space-x-4">
                                            <input type="reset" value="Reset" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 cursor-pointer">
                                            <input type="submit" value="Save" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 cursor-pointer">
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
                        <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
                            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                                <div class="flex justify-end">
                                    <a href="settings.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                                </div>
                                <div class="mt-4 text-center">
                                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Edited Successfully!</h2>
                                    <div class="flex justify-center">
                                        <a href="settings.php" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">OK</a>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    }
                }
            }
            ?>
        </div>
    </div>

    <script>
        const toggleSidebar = document.getElementById('toggleSidebar');
        const sidebar = document.querySelector('.sidebar');
        toggleSidebar.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 640 && !sidebar.contains(e.target) && !toggleSidebar.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });
    </script>
    <script src="../js/active-link.js"></script>
    <script src="../js/date-time.js"></script>
</body>

</html>