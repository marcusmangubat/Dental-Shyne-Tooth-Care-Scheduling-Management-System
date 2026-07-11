<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctors</title>
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

<body class="bg-gray-100 font-sans">
    <?php
    session_start();
    if (isset($_SESSION["user"])) {
        if (($_SESSION["user"]) == "" || $_SESSION['usertype'] != 'p') {
            header("location: ../login.php");
        } else {
            $useremail = $_SESSION["user"];
        }
    } else {
        header("location: ../login.php");
    }

    include "../config.php";
    $userrow = $database->query("SELECT * FROM patient WHERE pemail = '$useremail'");
    $userfetch = $userrow->fetch_assoc();
    $userid = $userfetch['pid'];
    $username = $userfetch['pname'];
    ?>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="sidebar w-64 bg-white shadow-lg flex-shrink-0">
            <div class="p-6 text-center border-b border-gray-200">
                <img src="../photo/form-logo.png" alt="Dental Shyne Logo" class="mx-auto mb-4 w-24">
                <h2 class="text-sm font-semibold text-gray-800"><?php echo explode(" ", trim($username))[0]; ?></h2>
                <p class="text-xs text-gray-500"><?php echo substr($useremail, 0, 30); ?></p>
            </div>
            <ul class="space-y-2 p-4">
                <li><a href="index.php" class="flex items-center gap-2 p-3 rounded-lg hover:bg-gray-200"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="doctors.php" class="flex items-center gap-2 p-3 rounded-lg bg-gray-200 active"><i class="fas fa-user-md"></i> Doctors</a></li>
                <li><a href="schedule.php" class="flex items-center gap-2 p-3 rounded-lg hover:bg-gray-200"><i class="fas fa-clock"></i> Doctor Schedule</a></li>
                <li><a href="appointment.php" class="flex items-center gap-2 p-3 rounded-lg hover:bg-gray-200"><i class="fas fa-clipboard-list"></i> My Appointment</a></li>
                <li><a href="settings.php" class="flex items-center gap-2 p-3 rounded-lg hover:bg-gray-200"><i class="fas fa-cog"></i> Settings</a></li>
                <li><a href="../logout.php" class="flex items-center gap-2 p-3 text-white bg-red-600 rounded-lg hover:bg-red-700"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6">
            <div class="max-w-7xl mx-auto">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-3xl font-bold text-gray-900">Dentists</h1>
                    <form action="" method="POST" class="flex items-center bg-white border border-gray-300 rounded-lg p-2 w-full max-w-md">
                        <input type="search" name="search" list="doctors" placeholder="Search dentists..." class="flex-1 border-none outline-none p-2">
                        <?php
                        echo '<datalist id="doctors">';
                        $list11 = $database->query("SELECT docname, docemail FROM doctor;");
                        for ($y = 0; $y < $list11->num_rows; $y++) {
                            $row00 = $list11->fetch_assoc();
                            $d = $row00['docname'];
                            $c = $row00['docemail'];
                            echo "<option value='$d'>";
                            echo "<option value='$c'>";
                        }
                        echo '</datalist>';
                        ?>
                        <button type="submit" class="bg-transparent border-none p-2"><i class="fas fa-search text-gray-500"></i></button>
                    </form>
                </div>

                <!-- Doctor Grid -->
                <div class="content-header mb-4">
                    <p class="text-gray-600">All Doctors (<?php echo $list11->num_rows; ?>)</p>
                </div>
                <?php
                if ($_POST) {
                    $keyword = $_POST["search"];
                    $sqlmain = "SELECT * FROM doctor WHERE docemail = '$keyword' OR docname = '$keyword' OR docname LIKE '$keyword%' OR docname LIKE '%$keyword' OR docname LIKE '%$keyword%'";
                } else {
                    $sqlmain = "SELECT * FROM doctor ORDER BY docid DESC";
                }
                $result = $database->query($sqlmain);
                ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 fade-in-up-delay">
                    <?php
                    if ($result->num_rows == 0) {
                        echo '<div class="col-span-full text-center py-8">
                            <img src="../photo/nodata.png" alt="No data" class="w-1/4 mx-auto">
                            <p class="text-xl text-gray-700 font-bold uppercase mt-4">We couldn’t find anything related</p>
                        </div>';
                    } else {
                        while ($row = $result->fetch_assoc()) {
                            $docid = $row["docid"];
                            $docname = $row["docname"];
                            $docemail = $row["docemail"];
                            $spe = $row["specialties"];
                            $docphoto = $row["docphoto"];
                            $spcil_res = $database->query("SELECT sname FROM specialties WHERE id = '$spe'");
                            $spcil_name = $spcil_res->fetch_assoc()["sname"];
                            echo '
                            <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow">
                                <div class="flex items-center gap-4">
                                    <img src="' . ($docphoto ? $docphoto : '../photo/default-doctor.png') . '" alt="Doctor Photo" class="w-16 h-16 rounded-full object-cover">
                                    <div>
                                        <h3 class="text-lg font-semibold text-gray-800">' . htmlspecialchars(substr($docname, 0, 30)) . '</h3>
                                        <p class="text-sm text-gray-500">' . htmlspecialchars(substr($docemail, 0, 20)) . '</p>
                                        <p class="text-sm text-gray-600">' . htmlspecialchars(substr($spcil_name, 0, 20)) . '</p>
                                    </div>
                                </div>
                                <div class="mt-4 flex gap-2">
                                    <a href="?action=view&id=' . $docid . '" class="flex items-center px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                                        <i class="fas fa-eye mr-2"></i> View
                                    </a>
                                    <a href="?action=session&id=' . $docid . '&docname=' . urlencode($docname) . '" class="flex items-center px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                                        <i class="fas fa-calendar-check mr-2"></i> Schedules
                                    </a>
                                </div>
                            </div>';
                        }
                    }
                    ?>
                </div>
            </div>

            <!-- Popup for View Action -->
            <?php
            if (isset($_GET["action"]) && $_GET["action"] == 'view') {
                $id = $_GET["id"];
                $sqlmain = "SELECT * FROM doctor WHERE docid = '$id'";
                $result = $database->query($sqlmain);
                $row = $result->fetch_assoc();
                $docname = $row["docname"];
                $docemail = $row["docemail"];
                $spe = $row["specialties"];
                $docphoto = $row["docphoto"];
                $spcil_res = $database->query("SELECT sname FROM specialties WHERE id = '$spe'");
                $spcil_name = $spcil_res->fetch_assoc()["sname"];
                $tele = $row["doctel"];
                echo '
                <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
                    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg max-h-[90vh] overflow-y-auto">
                        <div class="flex justify-end">
                            <a href="doctors.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                        </div>
                        <div class="mt-4 text-center">
                            <img src="' . ($docphoto ? $docphoto : '../photo/default-doctor.png') . '" alt="Doctor Photo" class="w-24 h-24 rounded-full mx-auto mb-4 object-cover">
                            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Doctor Details</h2>
                            <div class="space-y-4 text-left">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><strong>Name</strong></label>
                                    <p class="mt-1 text-gray-900">' . htmlspecialchars($docname) . '</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><strong>Email</strong></label>
                                    <p class="mt-1 text-gray-900">' . htmlspecialchars($docemail) . '</p>
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
                                <a href="doctors.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Close</a>
                            </div>
                        </div>
                    </div>
                </div>';
            } elseif (isset($_GET["action"]) && $_GET["action"] == 'session') {
                $docname = $_GET["docname"];
                echo '
                <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
                    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                        <div class="flex justify-end">
                            <a href="doctors.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                        </div>
                        <div class="mt-4 text-center">
                            <h2 class="text-xl font-semibold text-gray-800 mb-4">Redirect to Doctor Schedules?</h2>
                            <p class="text-gray-600 mb-6">View all schedules by Dr. ' . htmlspecialchars(substr($docname, 0, 40)) . '?</p>
                            <form action="schedule.php" method="post" class="flex justify-center gap-4">
                                <input type="hidden" name="search" value="' . htmlspecialchars($docname) . '">
                                <input type="submit" value="Yes" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 cursor-pointer">
                                <a href="doctors.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">No</a>
                            </form>
                        </div>
                    </div>
                </div>';
            }
            ?>
        </div>
    </div>
    <script src="../js/active-link.js"></script>
    <script src="../js/date-time.js"></script>
</body>

</html>