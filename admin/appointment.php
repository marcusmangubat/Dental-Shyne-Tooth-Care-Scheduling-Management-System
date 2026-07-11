<?php
session_start();
if (!isset($_SESSION["user"]) || empty($_SESSION["user"]) || $_SESSION['usertype'] != 'a') {
    header("location: ../login.php");
    exit();
}
include "../config.php";
try {
    $stmt = $database->prepare("SELECT aemail FROM admin WHERE aemail = ?");
    $stmt->bind_param("s", $_SESSION["user"]);
    $stmt->execute();
    $userrow = $stmt->get_result();
    if ($userrow->num_rows === 0) {
        header("location: ../login.php");
        exit();
    }
    $userfetch = $userrow->fetch_assoc();
    $username = $userfetch['aemail'];
    $stmt->close();
} catch (Exception $e) {
    die("Database error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment</title>
    <script src="../css/tailwind.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
    <div class="continer">
        <div class="sidebar">
            <div class="p-6 text-center border-b border-gray-200">
                <img src="../photo/form-logo.png" alt="Dental Shyne Logo" class="mx-auto mb-4">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">Administrator</h2>
                <p class="text-xs text-gray-500"><?php echo htmlspecialchars($username); ?></p>
            </div>
            <ul class="space-y-2">
                <li>
                    <a href="index.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="schedule.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200">
                        <i class="fas fa-clock"></i> Schedule
                    </a>
                </li>
                <li class="relative">
                    <button id="accounts-toggle" class="flex items-center gap-2 px-3 py-2 rounded-lg w-full text-blue-600 font-semibold hover:bg-gray-200">
                        <span class="flex items-center gap-2">
                            <i class="fas fa-user-circle"></i> Accounts
                        </span>
                        <i class="fas fa-chevron-down ml-auto"></i>
                    </button>
                    <ul id="accounts-dropdown" class="hidden mt-1 ml-6 space-y-1">
                        <li>
                            <a href="doctors.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 text-sm">
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
                    <a href="appointment.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 active text-blue-600 font-semibold">
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
                <h1 class="text-2xl font-bold text-gray-900">Appointment Manager</h1>
                <div class="user"></div>
                <div class="datetime" id="datetime"></div>
            </div>
            <?php
            date_default_timezone_set('Asia/Manila');
            $today = date('Y-m-d');
            $list10 = $database->query("SELECT COUNT(*) as total FROM appointment");
            $totalAppointments = $list10->fetch_assoc()['total'];
            ?>
            <div class="content-header">
                <div>
                    <p>All Appointment (<?php echo $totalAppointments; ?>)</p>
                </div>
            </div>
            <br>
            <form action="" method="post" class="flex items-center justify-center gap-4 p-4">
                <label for="scheduledate" class="text-center font-medium">Date:</label>
                <input type="date" name="scheduledate" id="scheduledate"
                    class="border border-gray-300 rounded-md p-2 w-1/3 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <label for="docid" class="text-center font-medium">Doctor:</label>
                <select name="docid" id="docid"
                    class="border border-gray-300 rounded-md p-2 w-1/3 h-10 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="" disabled selected hidden>Choose Doctor Name from the list</option>
                    <?php
                    $list11 = $database->query("SELECT docid, docname FROM doctor ORDER BY docname ASC");
                    while ($row00 = $list11->fetch_assoc()) {
                        $sn = htmlspecialchars($row00["docname"]);
                        $id00 = $row00["docid"];
                        echo "<option value=\"$id00\">$sn</option>";
                    }
                    ?>
                </select>
                <input type="submit" name="filter" value="Filter"
                    class="bg-blue-500 text-white font-medium rounded-md p-2 w-1/6 hover:bg-blue-600 transition">
            </form>
            <div class="table-container" style="background-color: white;">
                <?php
                $sqlmain = "SELECT appointment.appoid, schedule.scheduleid, schedule.title, doctor.docname, patient.pname, 
                            schedule.scheduledate, schedule.scheduletime, appointment.apponum, appointment.appodate, 
                            schedule.estimated_duration
                            FROM schedule
                            INNER JOIN appointment ON schedule.scheduleid = appointment.scheduleid
                            INNER JOIN patient ON patient.pid = appointment.pid
                            INNER JOIN doctor ON schedule.docid = doctor.docid";
                $params = [];
                $types = "";
                if ($_POST) {
                    $conditions = [];
                    if (!empty($_POST["scheduledate"])) {
                        $conditions[] = "schedule.scheduledate = ?";
                        $params[] = $_POST["scheduledate"];
                        $types .= "s";
                    }
                    if (!empty($_POST["docid"])) {
                        $conditions[] = "doctor.docid = ?";
                        $params[] = $_POST["docid"];
                        $types .= "i";
                    }
                    if (!empty($conditions)) {
                        $sqlmain .= " WHERE " . implode(" AND ", $conditions);
                    }
                }
                $sqlmain .= " ORDER BY schedule.scheduledate ASC, appointment.apponum ASC";
                try {
                    $stmt = $database->prepare($sqlmain);
                    if (!empty($params)) {
                        $stmt->bind_param($types, ...$params);
                    }
                    $stmt->execute();
                    $result = $stmt->get_result();
                } catch (Exception $e) {
                    die("Database error: " . $e->getMessage());
                }
                $appointments = [];
                while ($row = $result->fetch_assoc()) {
                    // Calculate time range
                    $startDateTime = new DateTime($row['scheduledate'] . ' ' . $row['scheduletime']);
                    list($h, $m, $s) = explode(':', $row['estimated_duration']);
                    $durationSeconds = ($h * 3600) + ($m * 60) + $s;
                    if ($durationSeconds <= 0) {
                        $timeRange = date("M d, Y h:i A", strtotime($row['scheduledate'] . ' ' . $row['scheduletime'])) . ' - N/A';
                    } else {
                        $slotOffset = $durationSeconds * ($row['apponum'] - 1);
                        $appointmentStartTime = clone $startDateTime;
                        $appointmentStartTime->modify("+$slotOffset seconds");
                        $appointmentEndTime = clone $appointmentStartTime;
                        $appointmentEndTime->modify("+$durationSeconds seconds");
                        $timeRange = $appointmentStartTime->format("M d, Y h:i A") . ' - ' . $appointmentEndTime->format("h:i A");
                    }
                    $row['timeRange'] = $timeRange;
                    $appointments[] = $row;
                }
                ?>
                <div class="table-container overflow-x-auto fade-in-up-delay">
                    <table id="doctors-table" class="min-w-[800px] w-full border-collapse fade-in-up-delay">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="text-center py-2 px-4">Patient Name</th>
                                <th class="text-center py-2 px-4">Appointment ID</th>
                                <th class="text-center py-2 px-4">Doctor</th>
                                <th class="text-center py-2 px-4">Schedule Title</th>
                                <th class="text-center py-2 px-4">Schedule Date & Time Range</th>
                                <th class="text-center py-2 px-4">Events</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (empty($appointments)) {
                                echo '
                                <tr>
                                    <td colspan="6" style="text-align:center;">
                                        <img src="../photo/nodata.png" alt="No data" style="width:25%; display:block; margin:0 auto;" class="nodata-img">
                                        <br>
                                        <p class="heading-main12 nodata-img"
                                            style="font-size: 20px; color: rgb(49, 49, 49); font-family: Arial, sans-serif; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; line-height: 1.5; text-align:center;">
                                            We couldn\'t find anything related
                                        </p>
                                    </td>
                                </tr>';
                            } else {
                                foreach ($appointments as $row) {
                                    $appoid = $row["appoid"];
                                    $title = $row["title"];
                                    $docname = $row["docname"];
                                    $pname = $row["pname"];
                                    $apponum = $row["apponum"];
                                    $timeRange = $row["timeRange"];
                                    echo '
                                        <tr>
                                            <td class="py-2 px-4">' . htmlspecialchars($pname) . '</td>
                                            <td class="text-center py-2 px-4">' . htmlspecialchars(sprintf("%02d", $apponum)) . '</td>
                                            <td class="py-2 px-4">' . htmlspecialchars($docname) . '</td>
                                            <td class="py-2 px-4">' . htmlspecialchars($title) . '</td>
                                            <td class="py-2 px-4">' . htmlspecialchars($timeRange) . '</td>
                                            <td class="py-2 px-4">
                                                <div class="action-buttons flex justify-center space-x-2">
                                                    <a href="?action=drop&id=' . htmlspecialchars($appoid) . '&name=' . urlencode($pname) . '&session=' . urlencode($title) . '&apponum=' . urlencode($apponum) . '">
                                                        <button class="btn btn-delete flex items-center px-3 py-1 rounded bg-red-50 hover:bg-red-100 transition-colors" title="Delete">
                                                            <i class="fa-solid fa-trash-can" style="font-size: 16px; color: #ef4444;"></i>
                                                        </button>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php
        if (isset($_GET["id"]) && isset($_GET["action"])) {
            $id = $_GET["id"];
            $action = $_GET["action"];
            if ($action == 'drop') {
                $nameget = $_GET["name"] ?? '';
                $session = $_GET["session"] ?? '';
                $apponum = $_GET["apponum"] ?? '';
                echo '
                <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up">
                    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                        <div class="flex justify-end">
                            <a href="appointment.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                        </div>
                        <div class="mt-4 text-center">
                            <h2 class="text-2xl font-semibold text-gray-800 mb-6">Are you sure?</h2>
                            <p class="text-gray-700 mb-4 text-lg">
                                You want to delete this appointment
                            </p>
                            <p class="text-gray-600 mb-2">Patient Name: ' . htmlspecialchars(substr($nameget, 0, 40)) . '</p>
                            <p class="text-gray-600 mb-6">Appointment Number: ' . htmlspecialchars(substr($apponum, 0, 40)) . '</p>
                            <div class="flex justify-center space-x-4">
                                <a href="cancel_appointment.php?id=' . htmlspecialchars($id) . '" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none">
                                    Yes
                                </a>
                                <a href="appointment.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none">
                                    No
                                </a>
                            </div>
                        </div>
                    </div>
                </div>';
            }
        }
        ?>
    </div>
    <script src="../js/date-time.js"></script>
    <script src="../js/active-link.js"></script>
</body>

</html>