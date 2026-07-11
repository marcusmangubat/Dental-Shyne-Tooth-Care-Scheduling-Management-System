<?php
session_start();
if (isset($_SESSION["user"])) {
    if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'a') {
        header("location: ../login.php");
    }
} else {
    header("location: ../login.php");
}
//database connection
include '../config.php';
$userrow = $database->query("SELECT aemail FROM admin");
$userfetch = $userrow->fetch_assoc();
$username = $userfetch['aemail'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctors</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="../css/tailwind.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../css/aindex.css">
    <link rel="stylesheet" href="../css/table.css">
    <link rel="icon" type="image/png" href="../photo/logo_cropped.png">
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

        .table-container {
            max-width: 100%;
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

        #doctors-table th,
        #doctors-table td {
            border: 1px solid #e5e7eb;
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

        .image-preview {
            width: 128px;
            height: 128px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }
    </style>
</head>

<body>

    <div class="cntainer">
        <div class="sidebar">
            <div class="p-6 text-center border-b border-gray-200">
                <img src="../photo/form-logo.png" alt="Dental Shyne Logo" class="mx-auto mb-4">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Administrator</h2>
                <p class="text-xs text-gray-500"><?php echo $username; ?></p>
            </div>

            <ul class="space-y-2">
                <li>
                    <a href="index.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 text-blue-600">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="schedule.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200">
                        <i class="fas fa-clock"></i> Schedule
                    </a>
                </li>
                <!-- Accounts Dropdown -->
                <li class="relative">
                    <button id="accounts-toggle" class="flex items-center gap-2 px-3 py-2 rounded-lg w-full text-blue-600 font-semibold hover:bg-gray-200">
                        <span class="flex items-center gap-2">
                            <i class="fas fa-user-circle"></i> Accounts
                        </span>
                        <i class="fas fa-chevron-down ml-auto"></i>
                    </button>
                    <ul id="accounts-dropdown" class="hidden mt-1 ml-6 space-y-1">
                        <li>
                            <a href="doctors.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 active text-blue-600 font-semibold">
                                <i class="fas fa-user-md text-sm"></i> Doctors
                            </a>
                        </li>
                        <li>
                            <a href="patients.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 text-sm">
                                <i class="fas fa-users text-sm"></i> Patients
                            </a>
                        </li>
                        <li>
                            <a href="frontdesk.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 text-sm">
                                <i class="fas fa-concierge-bell text-sm"></i> Frontdesk
                            </a>
                        </li>
                    </ul>
                </li>
                <li>
                    <a href="calendar.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200">
                        <i class="fas fa-calendar-alt"></i> Calendar
                    </a>
                </li>
                <li>
                    <a href="appointment.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200">
                        <i class="fas fa-clipboard-list"></i> Appointment
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

            <!-- Script for Dropdown -->
            <script>
                const toggleBtn = document.getElementById('accounts-toggle');
                const dropdown = document.getElementById('accounts-dropdown');

                toggleBtn.addEventListener('click', () => {
                    dropdown.classList.toggle('hidden');
                });
            </script>
        </div>

        <div class="main-content flex-1 p-6">
            <div class="header">
                <h1 class="text-2xl font-bold text-gray-900">Dentist</h1>
                <form action="" method="POST">
                    <div class="search-bar"
                        style="display: flex; align-items: center; background: #fff; border: 1px solid #ccc; border-radius: 8px; padding: 5px; width: 300px; margin-right: 20px;">
                        <input type="search" name="search" list="doctors" placeholder="Search dentists..."
                            class="search-input" style="border: none; outline: none; padding: 5px; flex: 1;">
                        <?php
                        echo '<datalist id="doctors">';
                        $list11 = $database->query("SELECT docname, docemail FROM doctor; ");

                        for ($y = 0; $y < $list11->num_rows; $y++) {
                            $row00 = $list11->fetch_assoc();
                            $d = $row00['docname'];
                            $c = $row00['docemail'];
                            echo "<option value= '$d'>";
                            echo "<option value= '$c'>";
                        };
                        echo '</datalist>';
                        ?>
                        <button type="submit" value="search" class="search-btn"
                            style="background: none; border: none; cursor: pointer;">
                            <img src="../icons/search.png" alt="Search Icon" style="width: 16px; height: 16px;">
                        </button>
                    </div>
                </form>
                <div class="user"></div>
                <div class="datetime" id="datetime"></div>
            </div>

            <div class="content-header">
                <div>
                    <h1>Add New Dentist</h1>
                    <p>All Doctors
                        <?php echo $list11->num_rows; ?>
                    </p>
                </div>
                <a href="?action=add&id=none&error=0" class="add-btn"> <img src="../icons/plus.png" alt="Plus Icon"
                        style="width: 16px; height: 16px; margin-right: 5px;">
                    Add New Dentist
                </a>
            </div>
            <?php
            if ($_POST) {
                $keyword = $_POST['search'];
                $sqlmain = "SELECT * FROM doctor WHERE 
                docemail= '$keyword' OR 
                docname = '$keyword' OR 
                docname LIKE '$keyword%' OR 
                docname LIKE '%$keyword' OR 
                docname LIKE '%$keyword%'";
            } else {
                $sqlmain = "SELECT * FROM doctor ORDER BY  docid DESC";
            }
            ?>


            <div class="table-container overflow-x-auto fade-in-up-delay">
                <table id="doctors-table" class="min-w-[800px] w-full border-collapse fade-in-up-">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="text-center py-2 px-4">Doctor Name</th>
                            <th class="text-center py-2 px-4">Email</th>
                            <th class="text-center py-2 px-4">Specialties</th>
                            <th class="text-center py-2 px-4">Events</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $database->query($sqlmain);
                        if ($result->num_rows == 0) {
                            echo '
                                <tr>
                                    <td colspan="4" style="text-align:center;" class="text-center py-4">
                                        <img src="../photo/nodata.png" alt="No data" style="width:25%; display:block; margin:0 auto;" class="nodata-img">
                                        <br>
                                        <p class="heading-main12 text-xl text-gray-700 font-bold uppercase tracking-wide mt-2 nodata-img" style="margin-left: 45px; font-size: 20px; color: rgb(49, 49, 49); font-family: Arial, sans-serif; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; line-height: 1.5;">We couldnt find anything related </p>
                                    </td>
                                </tr>';
                        } else {
                            for ($x = 0; $x < $result->num_rows; $x++) {
                                $row = $result->fetch_assoc();
                                $docid = $row['docid'];
                                $name = $row['docname'];
                                $email = $row['docemail'];
                                $spe = $row['specialties'];
                                $spcil_res = $database->query("SELECT sname FROM specialties WHERE id= '$spe'");
                                $spcil_array = $spcil_res->fetch_assoc();
                                $spcil_name = $spcil_array['sname'];
                                echo
                                '<tr class="hover:bg-gray-50">
                                        <td class=" py-2 px-4">' . substr($name, 0, 50) . '</td>
                                        <td class=" py-2 px-4">' . substr($email, 0, 50) . '</td>
                                        <td class=" py-2 px-4">' . substr($spcil_name, 0, 50) . '</td>
                                        <td class="text-center py-2 px-4">
                                            <div class="action-buttons flex justify-center gap-2">
                                                                                        <a href="?action=edit&id=' . $docid . '&error=0">
                                                <button class="btn btn-edit flex items-center px-3 py-1 rounded bg-yellow-50 hover:bg-yellow-100 transition-colors" title="Edit">
                                                    <i class="fa-solid fa-pen-to-square" style="font-size: 16px; color: #f59e0b;"></i>
                                                </button>
                                            </a>
                                            <a href="?action=view&id=' . $docid . '">
                                                <button class="btn btn-view flex items-center px-3 py-1 rounded bg-blue-50 hover:bg-blue-100 transition-colors" title="View">
                                                    <i class="fa-solid fa-eye" style="font-size: 16px; color: #3b82f6;"></i>
                                                </button>
                                            </a>
                                            <a href="?action=drop&id=' . $docid . '&name=' . $name . '">
                                                <button class="btn btn-delete flex items-center px-3 py-1 rounded bg-red-50 hover:bg-red-100 transition-colors" title="Delete">
                                                    <i class="fa-solid fa-trash-can" style="font-size: 16px; color: #ef4444;"></i>
                                                </button>
                                            </a>
                                            </div>
                                        </td>
                                </tr> ';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
    if ($_GET) {
        $id = isset($_GET['id']) ? $_GET['id'] : null;
        $action = $_GET['action'];
        if ($action == 'drop') {
            $nameget = $_GET['name'];
            echo '
           <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
                <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                    <div class="flex justify-end">
                        <a href="doctors.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                    </div>
                    <div class="mt-4 text-center">
                        <h2 class="text-2xl font-semibold text-gray-800 mb-6">Are you sure?</h2>
                        <p class="text-gray-700 mb-6">You want to delete this record<br>(' . substr($nameget, 0, 40) . ')</p>
                        <div class="flex justify-center space-x-4">
                            <a href="delete_doctor.php?id=' . $id . '" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none">Yes</a>
                            <a href="doctors.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none">No</a>
                        </div>
                    </div>
                </div>
            </div>
            ';
        } else if ($action == 'view') {
            $sqlmain = "SELECT * FROM doctor WHERE docid= '$id'";
            $result = $database->query($sqlmain);
            $row = $result->fetch_assoc();
            $name = $row["docname"];
            $email = $row["docemail"];
            $spe = $row["specialties"];
            $spcil_res = $database->query("SELECT sname FROM specialties WHERE id= '$spe'");
            $spcil_array = $spcil_res->fetch_assoc();
            $spcil_name = $spcil_array['sname'];
            $tele = $row['doctel'];
            $photo = isset($row['docphoto']) ? $row['docphoto'] : '../uploads/doctors/default-doctor.png';
            echo '
                <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
                    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                        <div class="flex justify-end">
                            <a href="doctors.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                        </div>
                        <div class="mt-4">
                            <h2 class="text-2xl font-semibold text-gray-800 text-center mb-6">Doctor Details</h2>
                            <div class="space-y-4">
                                <div class="text-center">
                                    <img src="' . $photo . '" alt="Doctor Photo" class="image-preview mx-auto mb-4">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><strong>Name</strong></label>
                                    <p class="mt-1 text-gray-900">' . $name . '</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><strong>Email</strong></label>
                                    <p class="mt-1 text-gray-900">' . $email . '</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><strong>Specialties</strong></label>
                                    <p class="mt-1 text-gray-900">' . $spcil_name . '</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><strong>Phone Number</strong></label>
                                    <p class="mt-1 text-gray-900">' . $tele . '</p>
                                </div>
                            </div>
                            <div class="flex justify-center mt-6">
                                <a href="doctors.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">Close</a>
                            </div>         
                        </div>
                    </div>
                </div>';
        } elseif ($action == 'add') {
            $error_1 = $_GET["error"];
            $errorlist = array(
                '1' => '<p class="text-red-500 text-sm text-center">Already have an account for this Email address.</p>',
                '2' => '<p class="text-red-500 text-sm text-center">Password Confirmation Error! Reconfirm Password</p>',
                '3' => '<p class="text-red-500 text-sm text-center">Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.</p>',
                '4' => "",
                '5' => '<p class="text-red-500 text-sm text-center">Invalid file type. Please upload a JPEG, PNG, or GIF image.</p>',
                '6' => '<p class="text-red-500 text-sm text-center">File size exceeds 2MB limit.</p>',
                '7' => '<p class="text-red-500 text-sm text-center">Image must be square (2x2 aspect ratio) and at least 100x100 pixels.</p>',
                '8' => '<p class="text-red-500 text-sm text-center">Failed to upload image. Please try again.</p>',
                '0' => '',
            );
            if ($error_1 != '4') {
                echo '
        <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-end">
                    <a href="doctors.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                </div>
                <div class="mt-4">
                    <h2 class="text-2xl font-semibold text-gray-800 text-center mb-6">Add New Dentist</h2>
                    <div class="text-red-500 text-center mb-4">' . $errorlist[$error_1] . '</div>
                    <form action="add_new_dentist.php" method="POST" enctype="multipart/form-data" onsubmit="return validatePassword()" class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Dentist Name" required>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" name="email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Email Address" required>
                        </div>
                        <div>
                            <label for="tele" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="text" name="tele" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Phone Number" required>
                        </div>
                        <div>
                            <label for="spec" class="block text-sm font-medium text-gray-700">Specialty</label>
                            <select name="spec" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                ';
                $list11 = $database->query("SELECT * FROM specialties ORDER BY sname ASC;");
                for ($y = 0; $y < $list11->num_rows; $y++) {
                    $row00 = $list11->fetch_assoc();
                    $sn = $row00["sname"];
                    $id00 = $row00["id"];
                    echo "<option value='$id00'>$sn</option>";
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
                         <div>
                            <label for="photo" class="block text-sm font-medium text-gray-700">Doctor Photo (2x2)</label>
                            <input type="file" id="photo" name="photo" accept="image/*" class="mt-1 cursor-pointer block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="previewImage(this, \'photo-preview\')">
                            <img id="photo-preview" src="../uploads/doctors/default-doctor.png" alt="Photo Preview" class="image-preview mt-2">
                        </div>
                        <div class="flex justify-center space-x-4">
                            <input type="reset" value="Reset" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 cursor-pointer">
                            <input type="submit" value="Add" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        </div>
                    </form>
                    <script>
                        function previewImage(input, previewId) {
                            const preview = document.getElementById(previewId);
                            const file = input.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    preview.src = e.target.result;
                                };
                                reader.readAsDataURL(file);
                            } else {
                                preview.src = "../uploads/doctors/default-doctor.png";
                            }
                        }
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
        </div>
        ';
            } else {
                echo '
        <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                <div class="flex justify-end">
                    <a href="doctors.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                </div>
                <div class="mt-4 text-center">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">New Record Added Successfully!</h2>
                    <div class="flex justify-center">
                        <a href="doctors.php" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">OK</a>
                    </div>
                </div>
            </div>
        </div>
        ';
            }
        } else if ($action == 'edit') {
            $sqlmain = "SELECT * FROM doctor WHERE docid= '$id'";
            $result = $database->query($sqlmain);
            $row = $result->fetch_assoc();
            $name = $row["docname"];
            $email = $row["docemail"];
            $spe = $row["specialties"];
            $spcil_res = $database->query("SELECT sname FROM specialties WHERE id= '$spe'");
            $spcil_array = $spcil_res->fetch_assoc();
            $spcil_name = $spcil_array['sname'];
            $tele = $row['doctel'];
            $photo = isset($row['docphoto']) ? $row['docphoto'] : '../uploads/doctors/default-doctor.png';
            $error_1 = $_GET["error"];
            $errorlist = array(
                '1' => '<p class="text-red-500 text-sm text-center">Already have an account for this Email address.</p>',
                '2' => '<p class="text-red-500 text-sm text-center">Password Confirmation Error! Reconfirm Password</p>',
                '3' => '<p class="text-red-500 text-sm text-center">Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.</p>',
                '4' => "",
                '5' => '<p class="text-red-500 text-sm text-center">Invalid file type. Please upload a JPEG, PNG, or GIF image.</p>',
                '6' => '<p class="text-red-500 text-sm text-center">File size exceeds 2MB limit.</p>',
                '7' => '<p class="text-red-500 text-sm text-center">Image must be square (2x2 aspect ratio) and at least 100x100 pixels.</p>',
                '8' => '<p class="text-red-500 text-sm text-center">Failed to upload image. Please try again.</p>',
                '0' => '',
            );
            if ($error_1 != '4') {
                echo '
                 <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                <div class="flex justify-end">
                    <a href="doctors.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                </div>
                <div class="mt-4">
                    <h2 class="text-2xl font-semibold text-gray-800 text-center mb-6">Edit Dentist Details</h2>
                    <div class="text-red-500 text-center mb-4">' . $errorlist[$error_1] . '</div>
                    <form action="edit_dentist.php" method="POST" enctype="multipart/form-data" onsubmit="return validatePassword()" class="space-y-6">
                       
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                            <input type="text" name="name" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Dentist Name" value="' . $name . '" required>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="hidden" value="' . $id . '" name="id00">
                            <input type="hidden" name="oldemail" value="' . $email . '">
                            <input type="email" name="email" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Email Address" value="' . $email . '" required>
                        </div>
                        <div>
                            <label for="tele" class="block text-sm font-medium text-gray-700">Phone Number</label>
                            <input type="text" name="tele" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Phone Number" value="' . $tele . '" required>
                        </div>
                        <div>
                            <label for="spec" class="block text-sm font-medium text-gray-700">Specialty (Current: ' . $spcil_name . ')</label>
                            <select name="spec" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                ';
                $list11 = $database->query("SELECT * FROM specialties ORDER BY sname ASC;");
                for ($y = 0; $y < $list11->num_rows; $y++) {
                    $row00 = $list11->fetch_assoc();
                    $sn = $row00["sname"];
                    $id00 = $row00["id"];
                    echo "<option value='$id00'" . ($id00 == $spe ? " selected" : "") . ">$sn</option>";
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
                         <div>
                            <label for="photo" class="block text-sm font-medium text-gray-700">Doctor Photo (2x2)</label>
                            <input type="file" id="photo" name="photo" accept="image/*" class="mt-1 cursor-pointer block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="previewImage(this, \'photo-preview\')">
                            <img id="photo-preview" src="' . $photo . '" alt="Photo Preview" class="image-preview mt-2">
                        </div>
                        <div class="flex justify-center space-x-4">
                            <input type="reset" value="Reset" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 cursor-pointer">
                            <input type="submit" value="Save" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                        </div>
                    </form>
                    <script>
                        function previewImage(input, previewId) {
                            const preview = document.getElementById(previewId);
                            const file = input.files[0];
                            if (file) {
                                const reader = new FileReader();
                                reader.onload = function(e) {
                                    preview.src = e.target.result;
                                };
                                reader.readAsDataURL(file);
                            } else {
                                preview.src = "' . $photo . '";
                            }
                        }
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
                    <a href="doctors.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                </div>
                <div class="mt-4 text-center">
                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Edited Successfully!</h2>
                    <div class="flex justify-center">
                        <a href="doctors.php" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">OK</a>
                    </div>
                </div>
            </div>
        </div>
                ';
            }
        }
    }
    ?>

    <script src="../js/date-time.js"></script>
    <script src="../js/active-link.js"></script>
</body>

</html>