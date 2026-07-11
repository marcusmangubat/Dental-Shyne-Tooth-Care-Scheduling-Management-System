<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule</title>
    <link rel="icon" type="image/png" href="../photo/logo_cropped.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../css/tailwind.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="../css/aindex.css">
    <link rel="stylesheet" href="../css/table.css">
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

        @keyframes float {
            0% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
                /* Move up by 10 pixels */
            }

            100% {
                transform: translateY(0);
                /* Return to original position */
            }
        }

        .nodata-img {
            animation: float 3s ease-in-out infinite;
            /* 3-second duration, smooth easing, repeats indefinitely */
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
    </style>
</head>

<body>
    <?php
    session_start();

    // Check if user is logged in and has the correct user type
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
            // Handle case where no user is found (e.g., log out or redirect)
            header("Location: ../login.php");
            exit();
        }

        // Fetch user data
        $userfetch = $userrow->fetch_assoc();
        $userid = $userfetch['docid'];
        $username = $userfetch['docname'];

        $stmt->close();
    } catch (Exception $e) {
        // Handle database errors (log the error securely in production)
        die("Database error: " . $e->getMessage());
    }
    ?>
    <div class="comtainer">
        <div class="sidebar">
            <div class="p-6 text-center border-b border-gray-200">
                <img src="../photo/form-logo.png" alt="Dental Shyne Logo" class=" mx-auto mb-4">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Dr. <?php echo explode(" ", trim($username))[0]; ?></h2>
                <p class="text-xs text-gray-500"><?php echo substr($useremail, 0, 30) ?></p>
            </div>
            <hr>
            <ul class="space-y-2">
                <li>
                    <a href="index.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="appointment.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 ">
                        <i class="fas fa-clipboard-list"></i> Appointment
                    </a>
                </li>
                <li>
                    <a href="schedule.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 active text-blue-600 font-semibold">
                        <i class="fas fa-clock"></i> Schedule
                    </a>
                </li>
                <li>
                    <a href="patient.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200">
                        <i class="fas fa-users"></i> Patient
                    </a>
                </li>
                <li>
                    <a href="settings.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200">
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
                <h1 class="text-2xl font-bold text-gray-900">My Schedule </h1>
                <div class="user"></div>
                <div class="datetime" id="datetime"></div>
            </div>
            <?php
            date_default_timezone_set('Asia/Manila');
            $today = date('Y-m-d');

            $list110 = $database->query("SELECT * FROM schedule WHERE docid = '$userid'");
            ?>
            <div class="content-header">
                <div>
                    <p>My Schedule (<?php echo $list110->num_rows;  ?>)
                    </p>
                </div>
            </div>
            <form action="" method="post" class="flex items-center justify-center gap-4 p-4">
                <label for="date" class="text-center font-medium">Date:</label>
                <input type="date" name="sheduledate" id="date"
                    class="border border-gray-300 rounded-md p-2 w-1/3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="submit" name="filter" value="Filter"
                    class="bg-blue-500 text-white font-medium rounded-md p-2 w-1/6 hover:bg-blue-600 transition">
            </form>
            <?php
            // Updated SQL query to include estimated_duration
            $sqlmain = "SELECT schedule.scheduleid, schedule.title, doctor.docname, schedule.scheduledate, schedule.scheduletime, schedule.nop, schedule.estimated_duration FROM schedule
            INNER JOIN doctor ON schedule.docid = doctor.docid WHERE doctor.docid = $userid";

            if ($_POST) {
                if (!empty($_POST["scheduledate"])) {
                    $scheduledate = $_POST["scheduledate"];
                    $sqlmain .= " AND schedule.scheduledate = '$scheduledate'";
                }
            }
            ?>
            <div class="table-container overflow-x-auto fade-in-up-delay">
                <table id="doctors-table" class="min-w-[800px] w-full border-collapse fade-in-up-delay">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="text-center py-2 px-4">Schedule Title</th>
                            <th class="text-center py-2 px-4">Schedule Date & Time</th>
                            <th class="text-center py-2 px-4">Estimated Duration Per Patient</th> <!-- New Header -->
                            <th class="text-center py-2 px-4">Number of Appointment</th>
                            <th class="text-center py-2 px-4">Events</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $database->query($sqlmain);
                        if ($result->num_rows == 0) {
                            echo '
                                <tr>
                                    <td colspan="5" style="text-align:center;"> <!-- Corrected colspan to 5 -->
                                        <img src="../photo/nodata.png" alt="No data" style="width:25%; display:block; margin:0 auto;" class="nodata-img">
                                        <br>
                                        <p class="heading-main12 nodata-img" style="margin-left: 45px; font-size: 20px; color: rgb(49, 49, 49); font-family: Arial, sans-serif; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; line-height: 1.5;">We couldnt find anything related </p>
                                    </td>
                                </tr>';
                        } else {
                            for ($x = 0; $x < $result->num_rows; $x++) {
                                $row = $result->fetch_assoc();
                                $scheduleid = $row["scheduleid"];
                                $title = $row["title"];
                                $docname = $row["docname"];
                                $scheduledate = $row["scheduledate"];
                                $scheduletime = $row["scheduletime"];
                                $nop = $row["nop"];
                                $estimated_duration = $row["estimated_duration"];

                                echo
                                '<tr class="hover:bg-gray-50">
                                <td  class=" py-2 px-4">' . substr($title, 0, 30) . '</td>
                           <td class=" py-2 px-4">' . date("D, M j, Y", strtotime($scheduledate)) . ' ' . date("h:i A", strtotime($scheduletime)) . '</td>
                                <td class="text-center py-2 px-4">' . date("H \h\o\u\\r\s : i \m\i\n\s", strtotime($estimated_duration)) . '</td> <!-- Display Estimated Duration -->
                                <td class="text-center py-2 px-4">' . $nop .  '</td>
                                <td class="py-2 px-4">
                                 <div class="action-buttons flex justify-center gap-2">
                                    <a href="?action=view&id=' . $scheduleid . '">
                                        <button class="btn btn-view flex items-center px-3 py-1 text-white rounded "><img src="../icons/view.png" alt="Plus Icon"
                                                style="width: 16px; height: 16px; margin-right: 5px;">View</button>
                                    </a>
                                    <a href="?action=drop&id=' . $scheduleid . '&name=' . $title . '">
                                        <button class="btn btn-delete flex items-center px-3 py-1 text-white rounded "><img src="../icons/trash.png" alt="Plus Icon"
                                                style="width: 16px; height: 16px; margin-right: 5px;">Delete</button>
                                    </a>
                                </div>
                                </td>
                                </tr>
                                ';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
        if ($_GET) {
            $id = $_GET["id"];
            $action = $_GET["action"];
            if ($action == 'drop') {
                $nameget = $_GET["name"];
                echo '
                    <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50        fade-in-up-delay">
                        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                            <div class="flex justify-end">
                                <a href="schedule.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                            </div>
                            <div class="mt-4 text-center">
                                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Are you sure?</h2>
                                <p class="text-gray-700 mb-6">You want to delete this appointment<br>(' . substr($nameget, 0, 40) . ')</p>
                                <div class="flex justify-center space-x-4">
                                    <a href="cancel_schedule.php?id=' . $id . '" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none">Yes</a>
                                    <a href="schedule.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none">No</a>
                                </div>
                            </div>
                        </div>
                    </div>';
            } else if ($action == 'view') {
                $sqlmain = "SELECT schedule.scheduleid, schedule.title, doctor.docname, schedule.scheduledate, schedule.scheduletime, schedule.nop, schedule.estimated_duration FROM schedule INNER JOIN doctor ON schedule.docid=doctor.docid WHERE schedule.scheduleid=$id";
                $result = $database->query($sqlmain);
                $row = $result->fetch_assoc();
                $scheduleid = $row["scheduleid"];
                $title = $row["title"];
                $docname = $row["docname"];
                $scheduledate = $row["scheduledate"];
                $scheduletime = $row["scheduletime"];
                $nop = $row["nop"];
                $estimated_duration = $row["estimated_duration"];

                $sqlmain12 = "SELECT * FROM appointment INNER JOIN patient ON patient.pid=appointment.pid INNER JOIN schedule ON schedule.scheduleid=appointment.scheduleid WHERE schedule.scheduleid=$id; ";
                $result12 = $database->query($sqlmain12);
                echo '
                     <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-[900px] max-h-[90vh] overflow-y-auto">
                            <div class="flex justify-end">
                                <a href="schedule.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                            </div>
                            <div class="mt-4">
                                <h2 class="text-2xl font-semibold text-gray-800 text-center mb-6">Schedule Details</h2>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"><strong>Schedule Title</strong></label>
                                        <p class="mt-1 text-gray-900">' . $title . '</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"><strong>Appointment Date</strong></label>
                                        <p class="mt-1 text-gray-900">' . date("D, M j, Y", strtotime($scheduledate)) . '</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"><strong>Appointment Time</strong></label>
                                        <p class="mt-1 text-gray-900">' . date("h:i A", strtotime($scheduletime)) . '</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"><strong>Estimated Duration</strong></label>
                                        <p class="mt-1 text-gray-900">' . date("H \h\o\u\\r\s i \m\i\n\s", strtotime($estimated_duration)) . '</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"><strong>Patient Registered for this Appointment (' . $result12->num_rows . "/" . $nop . ')</strong></label>
                                    </div>   
                                </div>
                                <br>
                                <div class="table-container overflow-x-auto">
                                    <table id="doctors-table" class="min-w-[800px] w-full border-collapse">
                                        <thead>
                                            <tr class="bg-gray-200">
                                               <!-- <th class="text-center py-2 px-4">Patient ID</th>-->
                                                <th class="text-center py-2 px-4">Patient Name</th>
                                                <th class="text-center py-2 px-4">Appointment Number</th>
                                                <th class="text-center py-2 px-4">Phone Number</th>
                                            </tr>
                                        </thead>
                                        <tbody>';
                if ($result12->num_rows == 0) {
                    echo '
                                        <tr>
                                            <td colspan="3" style="text-align:center;">
                                                <img src="../photo/nodata.png" alt="No data" style="width:25%; display:block; margin:0 auto;" class="nodata-img">
                                                <br>
                                                <p class="heading-main12 nodata-img" style="margin-left: 45px; font-size: 20px; color: rgb(49, 49, 49); font-family: Arial, sans-serif; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; line-height: 1.5;">We don\'t have any Upcoming Appointment !</p>
                                            </td>
                                        </tr>';
                } else {
                    for ($x = 0; $x < $result12->num_rows; $x++) {
                        $row = $result12->fetch_assoc();
                        $apponum = $row["apponum"];
                        $pid = $row["pid"];
                        $pname = $row["pname"];
                        $ptel = $row["ptel"];
                        echo '
                                        <tr>
                                          <!--  <td class="text-center py-2 px-4">' .  substr($pid, 0, 15) . '</td> -->
                                            <td class=" py-2 px-4">' . substr($pname, 0, 25) . '</td>
                                            <td class="text-center py-2 px-4">' . $apponum . '</td>
                                            <td class=" py-2 px-4">' . substr($ptel, 0, 25) . '</td>
                                        </tr>';
                    }
                }
                echo ' </tbody>
                                    </table>
                                </div>
                                <div class="flex justify-center mt-6">
                                    <a href="schedule.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">Close</a>
                                </div>         
                            </div>
                        </div>
                    </div>
                ';
            }
        }
        ?>
    </div>
    <script src="../js/active-link.js"></script>
    <script src="../js/date-time.js"></script>

</body>

</html>