<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment</title>
    <link rel="stylesheet" href="../css/aindex.css">
    <link rel="stylesheet" href="../css/table.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="../css/tailwind.js"></script>
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
        if ($userrow->num_rows === 0) {
            header("Location: ../login.php");
            exit();
        }
        $userfetch = $userrow->fetch_assoc();
        $userid = $userfetch['docid'];
        $username = $userfetch['docname'];
        $stmt->close();
    } catch (Exception $e) {
        die("Database error: " . $e->getMessage());
    }
    ?>
    <div class="cotainer">
        <div class="sidebar">
            <div class="p-6 text-center border-b border-gray-200">
                <img src="../photo/form-logo.png" alt="Dental Shyne Logo" class="mx-auto mb-4">
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
                    <a href="appointment.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 active text-blue-600 font-semibold">
                        <i class="fas fa-clipboard-list"></i> Appointment
                    </a>
                </li>
                <li>
                    <a href="schedule.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200">
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
                <h1 class="text-2xl font-bold text-gray-900">Appointment</h1>
                <div class="user"></div>
                <div class="datetime" id="datetime"></div>
            </div>
            <?php
            date_default_timezone_set('Asia/Manila');
            $today = date('Y-m-d');
            $list110 = $database->query("SELECT *
                FROM schedule
                INNER JOIN appointment
                    ON schedule.scheduleid = appointment.scheduleid
                INNER JOIN patient
                    ON patient.pid = appointment.pid
                INNER JOIN doctor
                    ON schedule.docid = doctor.docid
                WHERE doctor.docid = '$userid'");
            ?>
            <div class="content-header">
                <div>
                    <p>My Appointment (<?php echo $list110->num_rows; ?>)</p>
                </div>
            </div>
            <form action="" method="post" class="flex items-center justify-center gap-4 p-4">
                <label for="date" class="text-center font-medium">Date:</label>
                <input type="date" name="scheduledate" id="date"
                    class="border border-gray-300 rounded-md p-2 w-1/3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="submit" name="filter" value="Filter"
                    class="bg-blue-500 text-white font-medium rounded-md p-2 w-1/6 hover:bg-blue-600 transition">
            </form>
            <?php
            $sqlmain = "SELECT appointment.appoid, schedule.scheduleid, schedule.title, doctor.docname, patient.pname, 
                        schedule.scheduledate, schedule.scheduletime, appointment.apponum, schedule.estimated_duration 
                        FROM schedule
                        INNER JOIN appointment
                            ON schedule.scheduleid = appointment.scheduleid
                        INNER JOIN patient
                            ON patient.pid = appointment.pid
                        INNER JOIN doctor
                            ON schedule.docid = doctor.docid
                        WHERE doctor.docid = '$userid'";
            if ($_POST && !empty($_POST["scheduledate"])) {
                $scheduledate = $_POST["scheduledate"];
                $sqlmain .= " AND schedule.scheduledate = '$scheduledate'";
            }
            $result = $database->query($sqlmain);
            ?>
            <div class="table-container overflow-x-auto fade-in-up-delay">
                <table id="doctors-table" class="min-w-[800px] w-full border-collapse fade-in-up-delay">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="text-center py-2 px-4">Patient Name</th>
                            <th class="text-center py-2 px-4">Appointment Number</th>
                            <th class="text-center py-2 px-4">Schedule Title</th>
                            <th class="text-center py-2 px-4">Schedule Date & Time</th>
                            <th class="text-center py-2 px-4">Appointment Time Range</th>
                            <th class="text-center py-2 px-4">Events</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result->num_rows == 0) {
                            echo '
                                <tr>
                                    <td colspan="6" class="py-2 px-4">
                                        <img src="../photo/nodata.png" alt="No data" style="width:25%; display:block; margin:0 auto;" class="nodata-img">
                                        <br>
                                        <p class="heading-main12 nodata-img text-center" style="margin-left: 45px; font-size: 20px; color: rgb(49, 49, 49); font-family: Arial, sans-serif; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; line-height: 1.5;">We couldn’t find anything related</p>
                                    </td>
                                </tr>';
                        } else {
                            for ($x = 0; $x < $result->num_rows; $x++) {
                                $row = $result->fetch_assoc();
                                $appoid = $row["appoid"];
                                $scheduleid = $row["scheduleid"];
                                $title = $row["title"];
                                $docname = $row["docname"];
                                $scheduledate = $row["scheduledate"];
                                $scheduletime = $row["scheduletime"];
                                $pname = $row["pname"];
                                $apponum = $row["apponum"];
                                $estimated_duration = $row["estimated_duration"];
                                // Calculate appointment time range
                                $startDateTime = new DateTime($scheduledate . ' ' . $scheduletime);
                                // Convert estimated_duration (HH:MM:SS) to seconds
                                list($h, $m, $s) = explode(':', $estimated_duration);
                                $durationSeconds = ($h * 3600) + ($m * 60) + $s;
                                // Handle zero or invalid duration
                                if ($durationSeconds <= 0) {
                                    $timeRange = date("h:i A", strtotime($scheduletime)) . ' - N/A';
                                } else {
                                    // Calculate start time: scheduletime + (estimated_duration * (apponum - 1))
                                    $slotOffset = $durationSeconds * ($apponum - 1);
                                    $appointmentStartTime = clone $startDateTime;
                                    $appointmentStartTime->modify("+$slotOffset seconds");
                                    // Calculate end time: start time + estimated_duration
                                    $appointmentEndTime = clone $appointmentStartTime;
                                    $appointmentEndTime->modify("+$durationSeconds seconds");
                                    // Format as "h:i A - h:i A"
                                    $timeRange = $appointmentStartTime->format("h:i A") . ' - ' . $appointmentEndTime->format("h:i A");
                                }
                                echo '
                                    <tr>
                                        <td class="py-2 px-4">' . htmlspecialchars($pname) . '</td>
                                        <td class="text-center py-2 px-4">' . htmlspecialchars($apponum) . '</td>
                                        <td class="py-2 px-4">' . htmlspecialchars($title) . '</td>
                                        <td class="py-2 px-4">' . date("D, M j, Y", strtotime($scheduledate)) . ' ' . date("h:i A", strtotime($scheduletime)) . '</td>
                                        <td class="py-2 px-4">' . htmlspecialchars($timeRange) . '</td>
                                        <td class="text-center py-2 px-4">
                                            <div class="action-buttons flex justify-center space-x-2">
                                                <a href="?action=drop&id=' . $appoid . '&name=' . urlencode($pname) . '&session=' . urlencode($title) . '&apponum=' . $apponum . '">
                                                    <button class="btn btn-delete flex items-center px-3 py-1 text-white rounded"><img src="../icons/trash.png" alt="Plus Icon"
                                                            style="width: 16px; height: 16px; margin-right: 5px;">Cancel</button>
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
            <?php
            if ($_GET) {
                $id = $_GET["id"];
                $action = $_GET["action"];
                if ($action == 'drop') {
                    $nameget = $_GET["name"];
                    $session = $_GET["session"];
                    $apponum = $_GET["apponum"];
                    echo '
                        <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
                            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                                <div class="flex justify-end">
                                    <a href="appointment.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                                </div>
                                <div class="mt-4 text-center">
                                    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Are you sure?</h2>
                                    <p class="text-gray-700 mb-6">You want to delete this appointment<br>Patient Name: (' . htmlspecialchars(substr($nameget, 0, 40)) . ')<br>Appointment Number: (' . htmlspecialchars(substr($apponum, 0, 40)) . ')</p>
                                    <div class="flex justify-center space-x-4">
                                        <a href="cancel_appointment.php?id=' . $id . '" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none">Yes</a>
                                        <a href="appointment.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none">No</a>
                                    </div>
                                </div>
                            </div>
                        </div>';
                }
            }
            ?>
        </div>
    </div>
    <script src="../js/active-link.js"></script>
    <script src="../js/date-time.js"></script>
</body>

</html>