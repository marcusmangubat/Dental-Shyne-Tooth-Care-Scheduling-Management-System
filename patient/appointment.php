<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointment</title>
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
    <?php
    session_start();
    if (!isset($_SESSION["user"]) || empty($_SESSION["user"]) || $_SESSION['usertype'] != 'p') {
        header("location: ../login.php");
        exit();
    }
    $useremail = $_SESSION["user"];
    include "../config.php";
    try {
        // Fetch patient data with prepared statement
        $stmt = $database->prepare("SELECT * FROM patient WHERE pemail = ?");
        $stmt->bind_param("s", $useremail);
        $stmt->execute();
        $userrow = $stmt->get_result();
        if ($userrow->num_rows === 0) {
            header("location: ../login.php");
            exit();
        }
        $userfetch = $userrow->fetch_assoc();
        $userid = $userfetch['pid'];
        $username = $userfetch['pname'];
        $stmt->close();
    } catch (Exception $e) {
        die("Database error: " . $e->getMessage());
    }
    // Query appointments with estimated_duration
    $sqlmain = "SELECT appointment.appoid, schedule.scheduleid, schedule.title, doctor.docname, doctor.docid, 
                schedule.scheduledate, schedule.scheduletime, appointment.apponum, appointment.appodate, 
                schedule.estimated_duration, schedule.nop
                FROM schedule
                INNER JOIN appointment ON schedule.scheduleid = appointment.scheduleid
                INNER JOIN patient ON patient.pid = appointment.pid
                INNER JOIN doctor ON schedule.docid = doctor.docid
                WHERE patient.pid = ?";
    if ($_POST && !empty($_POST['sheduledate'])) {
        $scheduledate = $_POST['sheduledate'];
        $sqlmain .= " AND schedule.scheduledate = ?";
    }
    $sqlmain .= " ORDER BY schedule.scheduledate ASC, appointment.apponum ASC";
    try {
        $stmt = $database->prepare($sqlmain);
        if ($_POST && !empty($_POST['sheduledate'])) {
            $stmt->bind_param("is", $userid, $scheduledate);
        } else {
            $stmt->bind_param("i", $userid);
        }
        $stmt->execute();
        $result = $stmt->get_result();
    } catch (Exception $e) {
        die("Database error: " . $e->getMessage());
    }
    date_default_timezone_set('Asia/Manila');
    $today = date('Y-m-d');
    ?>
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
                <li><a href="appointment.php" class="flex items-center gap-2 p-3 rounded-lg bg-gray-200 active"><i class="fas fa-clipboard-list"></i> My Appointment</a></li>
                <li><a href="settings.php" class="flex items-center gap-2 p-3 rounded-lg hover:bg-gray-200"><i class="fas fa-cog"></i> Settings</a></li>
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
                        <h1 class="text-3xl font-bold text-gray-900">My Appointment History</h1>
                    </div>
                    <div class="text-gray-600" id="datetime"></div>
                </div>
                <!-- Filter Form -->
                <div class="welcome-card p-6 mb-8 fade-in-up-delay">
                    <h2 class="text-2xl font-bold mb-4">My Bookings (<?php echo $result->num_rows; ?>)</h2>
                    <!-- <form action="" method="post" class="flex items-center justify-center gap-4">
                        <label for="date" class="text-center font-medium">Filter by Date:</label>
                        <input type="date" style="color: black;" name="sheduledate" id="date" class="border border-gray-300 rounded-md p-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <input type="submit" name="filter" value="Filter" class="bg-blue-500 text-white font-medium rounded-md p-2 hover:bg-blue-600 transition">
                    </form> -->
                </div>
                <!-- Alerts -->
                <?php
                if (isset($_GET["action"])) {
                    if ($_GET["action"] == "session-full") {
                        echo '
                        <div id="alert" class="fade-in-up-delay bg-red-100 border-l-4 border-red-600 text-red-700 p-4 max-w-xl mx-auto rounded shadow flex justify-between items-center">
                            <p class="font-medium">Sorry, this session is fully booked. No slots available.</p>
                            <a href="schedule.php" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Try Another</a>
                        </div>';
                    } elseif ($_GET["action"] == "already-booked") {
                        echo '
                        <div id="alert" class="fade-in-up-delay bg-yellow-100 border-l-4 border-yellow-600 text-yellow-700 p-4 max-w-xl mx-auto rounded shadow flex justify-between items-center">
                            <p class="font-medium">You have already booked an appointment for this date.</p>
                            <a href="appointment.php" class="bg-yellow-600 text-white px-3 py-1 rounded hover:bg-yellow-700">View My Appointments</a>
                        </div>';
                    } elseif ($_GET["action"] == "booking-added") {
                        echo '
                        <div id="alert" class="fade-in-up-delay bg-green-100 border-l-4 border-green-600 text-green-700 p-4 max-w-xl mx-auto rounded shadow flex justify-between items-center">
                            <p class="font-medium">🎉 Your appointment has been booked successfully!</p>
                            <a href="appointment.php" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">View Appointment</a>
                        </div>';
                    } elseif ($_GET["action"] == "reschedule-success") {
                        echo '
                        <div id="alert" class="fade-in-up-delay bg-green-100 border-l-4 border-green-600 text-green-700 p-4 max-w-xl mx-auto rounded shadow flex justify-between items-center">
                            <p class="font-medium">🎉 Your appointment has been rescheduled successfully!</p>
                            <a href="appointment.php" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">View Appointments</a>
                        </div>';
                    } elseif ($_GET["action"] == "error") {
                        echo '
                        <div id="alert" class="fade-in-up-delay bg-red-100 border-l-4 border-red-600 text-red-700 p-4 max-w-xl mx-auto rounded shadow flex justify-between items-center">
                            <p class="font-medium">Error: ' . htmlspecialchars($_GET["message"] ?? "Unknown error") . '</p>
                            <a href="appointment.php" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Try Again</a>
                        </div>';
                    }
                }
                ?>
                <!-- Appointment Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 fade-in-up-delay">
                    <?php
                    if ($result->num_rows == 0) {
                        echo '
                        <div class="col-span-full text-center py-8">
                            <img src="../photo/nodata.png" alt="No data" class="w-1/4 mx-auto nodata-img">
                            <p class="text-xl text-gray-700 font-bold uppercase mt-4">No appointments found</p>
                            <a href="schedule.php" class="inline-block mt-6">
                                <button class="px-6 py-2 rounded-lg bg-[#B3E7EB] text-black font-semibold hover:bg-[#00A7B5] shadow-md transition-colors duration-300">
                                    Show all Schedules
                                </button>
                            </a>
                        </div>';
                    } else {
                        while ($row = $result->fetch_assoc()) {
                            $scheduleid = $row['scheduleid'];
                            $title = $row['title'];
                            $docname = $row['docname'];
                            $docid = $row['docid'];
                            $scheduledate = date("F j, Y", strtotime($row['scheduledate']));
                            $scheduletime = date("g:i A", strtotime($row['scheduletime']));
                            $apponum = $row['apponum'];
                            $appodate = date("F j, Y", strtotime($row['appodate']));
                            $appoid = $row['appoid'];
                            $estimated_duration = $row['estimated_duration'];
                            $nop = $row['nop'];
                            // Calculate appointment time range
                            $startDateTime = new DateTime($row['scheduledate'] . ' ' . $row['scheduletime']);
                            list($h, $m, $s) = explode(':', $estimated_duration);
                            $durationSeconds = ($h * 3600) + ($m * 60) + $s;
                            if ($durationSeconds <= 0) {
                                $timeRange = date("h:i A", strtotime($row['scheduletime'])) . ' - N/A';
                            } else {
                                $slotOffset = $durationSeconds * ($apponum - 1);
                                $appointmentStartTime = clone $startDateTime;
                                $appointmentStartTime->modify("+$slotOffset seconds");
                                $appointmentEndTime = clone $appointmentStartTime;
                                $appointmentEndTime->modify("+$durationSeconds seconds");
                                $timeRange = $appointmentStartTime->format("h:i A") . ' - ' . $appointmentEndTime->format("h:i A");
                            }
                            if ($scheduleid == "") {
                                continue;
                            }
                            echo '
                            <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow card">
                                <h3 class="text-lg font-semibold text-gray-800 mb-2">Appointment Details</h3>
                                <p class="text-sm text-gray-600"><strong>Booking Date:</strong> ' . htmlspecialchars($appodate) . '</p>
                                <p class="text-sm text-gray-600"><strong>Title:</strong> ' . htmlspecialchars(substr($title, 0, 20)) . '</p>
                                <p class="text-sm text-gray-600"><strong>Appointment Number:</strong> 0' . htmlspecialchars($apponum) . '</p>
                                <p class="text-sm text-gray-600"><strong>Doctor:</strong> ' . htmlspecialchars(substr($docname, 0, 20)) . '</p>
                                <p class="text-sm text-gray-600"><strong>Scheduled Date:</strong> ' . htmlspecialchars($scheduledate) . '</p>
                                <p class="text-sm text-gray-600"><strong>Time:</strong> ' . htmlspecialchars($timeRange) . '</p>
                                <div class="mt-4 flex space-x-4">
                                    <a href="?action=change_time&id=' . htmlspecialchars($appoid) . '&title=' . urlencode($title) . '&doc=' . urlencode($docname) . '&scheduleid=' . htmlspecialchars($scheduleid) . '" class="inline-block text-blue-600 hover:text-blue-800 font-medium transition-colors">
                                        <i class="fas fa-clock mr-2"></i>Change Time
                                    </a>
                                    <a href="?action=reschedule&id=' . htmlspecialchars($appoid) . '&title=' . urlencode($title) . '&doc=' . urlencode($docname) . '&scheduleid=' . htmlspecialchars($scheduleid) . '&docid=' . htmlspecialchars($docid) . '" class="inline-block text-blue-600 hover:text-blue-800 font-medium transition-colors">
                                        <i class="fas fa-calendar-alt mr-2"></i>Reschedule Booking
                                    </a>
                                    <a href="?action=drop&id=' . htmlspecialchars($appoid) . '&title=' . urlencode($title) . '&doc=' . urlencode($docname) . '" class="inline-block text-red-600 hover:text-red-800 font-medium transition-colors">
                                        <i class="fas fa-trash mr-2"></i>Cancel Booking
                                    </a>
                                </div>
                            </div>';
                        }
                    }
                    ?>
                </div>
                <!-- Cancel Appointment Modal -->
                <?php
                if (isset($_GET["action"]) && $_GET["action"] == "drop" && isset($_GET["id"]) && isset($_GET["title"]) && isset($_GET["doc"])) {
                    $id = $_GET["id"];
                    $title = $_GET["title"];
                    $doc = $_GET["doc"];
                    echo '
                    <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
                        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                            <div class="flex justify-end">
                                <a href="appointment.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                            </div>
                            <div class="mt-4 text-center">
                                <h2 class="text-xl font-semibold text-gray-800 mb-4">Are you sure?</h2>
                                <p class="text-gray-600 mb-2">You want to cancel this appointment:</p>
                                <p class="text-gray-600"><strong>Schedule Title:</strong> ' . htmlspecialchars(substr($title, 0, 40)) . '</p>
                                <p class="text-gray-600"><strong>Dentist Name:</strong> ' . htmlspecialchars(substr($doc, 0, 40)) . '</p>
                                <div class="flex justify-center gap-4 mt-6">
                                    <a href="delete_appointment.php?id=' . $id . '" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600">Cancel Appointment</a>
                                    <a href="appointment.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">No</a>
                                </div>
                            </div>
                        </div>
                    </div>';
                }
                ?>
                <!-- Reschedule Appointment Modal -->
                <?php
                if (isset($_GET["action"]) && $_GET["action"] == "reschedule" && isset($_GET["id"]) && isset($_GET["title"]) && isset($_GET["doc"]) && isset($_GET["scheduleid"]) && isset($_GET["docid"])) {
                    $appoid = $_GET["id"];
                    $title = $_GET["title"];
                    $docname = $_GET["doc"];
                    $old_scheduleid = $_GET["scheduleid"];
                    $docid = $_GET["docid"];
                    $month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
                    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
                    $reschedule_date = isset($_GET['reschedule_date']) ? $_GET['reschedule_date'] : null;

                    // Fetch available schedules for the same doctor
                    $sql_schedules = "SELECT scheduleid, scheduledate, scheduletime, title, nop, estimated_duration
                                     FROM schedule
                                     WHERE scheduledate >= ? AND docid = ? AND nop < ?
                                     AND scheduleid NOT IN (SELECT scheduleid FROM appointment WHERE pid = ?)
                                     ORDER BY scheduledate, scheduletime";
                    try {
                        $max_capacity = 10; // Define max capacity
                        $stmt_schedules = $database->prepare($sql_schedules);
                        $stmt_schedules->bind_param("siii", $today, $docid, $max_capacity, $userid);
                        $stmt_schedules->execute();
                        $schedules_result = $stmt_schedules->get_result();
                    } catch (Exception $e) {
                        die("Database error: " . $e->getMessage());
                    }

                    $schedules = [];
                    while ($row = $schedules_result->fetch_assoc()) {
                        $date = $row['scheduledate'];
                        if (!isset($schedules[$date])) {
                            $schedules[$date] = [];
                        }
                        $schedules[$date][] = $row;
                    }
                    $stmt_schedules->close();

                    $prevMonth = $month - 1;
                    $prevYear = $year;
                    if ($prevMonth < 1) {
                        $prevMonth = 12;
                        $prevYear--;
                    }
                    $nextMonth = $month + 1;
                    $nextYear = $year;
                    if ($nextMonth > 12) {
                        $nextMonth = 1;
                        $nextYear++;
                    }

                    // Calendar generation function
                    function generateRescheduleCalendar($month, $year, $schedules, $today, $docid, $appoid, $title, $docname, $old_scheduleid)
                    {
                        $firstDay = new DateTime(sprintf("%04d-%02d-01", $year, $month));
                        $numDays = (int)$firstDay->format('t');
                        $startWeekday = (int)$firstDay->format('w'); // 0 = Sunday

                        $html = '<table class="table-auto w-full border-collapse">';
                        $html .= '<thead><tr class="bg-gray-200"><th class="p-2">Sun</th><th class="p-2">Mon</th><th class="p-2">Tue</th><th class="p-2">Wed</th><th class="p-2">Thu</th><th class="p-2">Fri</th><th class="p-2">Sat</th></tr></thead>';
                        $html .= '<tbody><tr>';

                        for ($i = 0; $i < $startWeekday; $i++) {
                            $html .= '<td class="p-4 border"></td>';
                        }

                        $currentMonth = sprintf("%04d-%02d", $year, $month);
                        for ($day = 1; $day <= $numDays; $day++) {
                            $date = sprintf("%s-%02d", $currentMonth, $day);
                            $hasSchedule = isset($schedules[$date]);
                            $isPast = $date < $today;
                            $isSelected = isset($_GET['reschedule_date']) && $_GET['reschedule_date'] === $date;

                            $class = 'p-4 border text-center';
                            if ($isPast) {
                                $class .= ' text-gray-400 bg-gray-100';
                            } else {
                                $class .= $hasSchedule ? ' bg-blue-100 cursor-pointer hover:bg-blue-200' : ' bg-gray-100';
                            }
                            if ($isSelected) {
                                $class .= ' bg-blue-300 font-bold';
                            }

                            $html .= '<td class="' . $class . '">';
                            if ($hasSchedule && !$isPast) {
                                $html .= '<a href="?action=reschedule&id=' . urlencode($appoid) . '&title=' . urlencode($title) . '&doc=' . urlencode($docname) . '&scheduleid=' . urlencode($old_scheduleid) . '&docid=' . urlencode($docid) . '&reschedule_date=' . $date . '&month=' . $month . '&year=' . $year . '">' . $day . '</a>';
                            } else {
                                $html .= $day;
                            }
                            $html .= '</td>';

                            if (($startWeekday + $day) % 7 === 0) {
                                $html .= '</tr><tr>';
                            }
                        }

                        $remaining = (7 - (($startWeekday + $numDays) % 7)) % 7;
                        for ($i = 0; $i < $remaining; $i++) {
                            $html .= '<td class="p-4 border"></td>';
                        }

                        $html .= '</tr></tbody></table>';
                        return $html;
                    }

                    echo '
                    <div id="reschedule-popup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
                        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-lg">
                            <div class="flex justify-end">
                                <a href="appointment.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                            </div>
                            <div class="mt-4">
                                <h2 class="text-xl font-semibold text-gray-800 mb-4 text-center">Reschedule Appointment</h2>
                                <p class="text-gray-600 mb-2 text-center">Current Appointment: ' . htmlspecialchars(substr($title, 0, 40)) . ' with ' . htmlspecialchars(substr($docname, 0, 40)) . '</p>';

                    if (empty($schedules)) {
                        echo '
                                <p class="text-gray-600 text-center mt-4">No available schedules for this doctor.</p>
                                <div class="flex justify-center gap-4 mt-6">
                                    <a href="schedule.php" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Find Other Schedules</a>
                                    <a href="appointment.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Cancel</a>
                                </div>';
                    } else {
                        if (!$reschedule_date) {
                            // Show calendar
                            echo '
                                <div class="mb-8">
                                    <div class="flex justify-between mb-4 items-center">
                                        <a href="?action=reschedule&id=' . urlencode($appoid) . '&title=' . urlencode($title) . '&doc=' . urlencode($docname) . '&scheduleid=' . urlencode($old_scheduleid) . '&docid=' . urlencode($docid) . '&month=' . $prevMonth . '&year=' . $prevYear . '" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Previous Month</a>
                                        <h2 class="text-xl font-bold text-gray-900">' . date('F Y', mktime(0, 0, 0, $month, 1, $year)) . '</h2>
                                        <a href="?action=reschedule&id=' . urlencode($appoid) . '&title=' . urlencode($title) . '&doc=' . urlencode($docname) . '&scheduleid=' . urlencode($old_scheduleid) . '&docid=' . urlencode($docid) . '&month=' . $nextMonth . '&year=' . $nextYear . '" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Next Month</a>
                                    </div>
                                    ' . generateRescheduleCalendar($month, $year, $schedules, $today, $docid, $appoid, $title, $docname, $old_scheduleid) . '
                                </div>';
                        } else {
                            // Show time slots for selected date
                            echo '
                                <h3 class="text-lg font-medium text-gray-700 mb-4">Select Time Slot for ' . date("F j, Y", strtotime($reschedule_date)) . '</h3>
                                <form action="reschedule.php" method="post" class="mt-4">
    <input type="hidden" name="appoid" value="' . htmlspecialchars($appoid) . '">
    <input type="hidden" name="old_scheduleid" value="' . htmlspecialchars($old_scheduleid) . '">
    <div class="grid grid-cols-1 gap-4">';
                            $slot_available_count = 0;
                            foreach ($schedules[$reschedule_date] as $schedule) {
                                $new_scheduleid = $schedule['scheduleid'];
                                $scheduletime = $schedule['scheduletime'];
                                $estimated_duration = $schedule['estimated_duration'];
                                $nop = $schedule['nop'];

                                // Fetch booked slots (already exists)
                                $stmt_booked = $database->prepare("SELECT apponum FROM appointment WHERE scheduleid = ?");
                                $stmt_booked->bind_param("i", $new_scheduleid);
                                $stmt_booked->execute();
                                $result_booked = $stmt_booked->get_result();
                                $booked_apponums = [];
                                while ($booked_row = $result_booked->fetch_assoc()) {
                                    $booked_apponums[] = $booked_row['apponum'];
                                }
                                $stmt_booked->close();

                                list($h, $m, $s) = explode(':', $estimated_duration);
                                $duration_minutes = ($h * 60) + $m + ($s / 60);
                                $current_slot_time = new DateTime($scheduletime);

                                for ($i = 1; $i <= $nop; $i++) {
                                    $slot_start_time = $current_slot_time->format('H:i:s');
                                    $display_start_time = $current_slot_time->format('g:i A');
                                    $current_slot_time->modify('+' . $duration_minutes . ' minutes');
                                    $display_end_time = $current_slot_time->format('g:i A');

                                    $is_booked = in_array($i, $booked_apponums);
                                    $is_past = (new DateTime($reschedule_date . ' ' . $slot_start_time)) < (new DateTime('now'));

                                    if (!$is_booked && !$is_past) {
                                        $slot_available_count++;
                                        echo '
                <label class="flex items-center p-3 rounded-lg bg-blue-100 hover:bg-blue-200 cursor-pointer transition-colors">
                    <input type="radio" name="new_schedule_info" value="' . htmlspecialchars($new_scheduleid) . ':' . $i . '" class="form-radio h-4 w-4 text-blue-600" required>
                    <span class="ml-2">' . htmlspecialchars($display_start_time . ' - ' . $display_end_time) . '</span>
                </label>';
                                    }
                                }
                            }
                            echo '
    </div>';
                            if ($slot_available_count > 0) {
                                echo '
                                    <div class="flex justify-center gap-4 mt-6">
                                        <button type="submit" name="reschedule" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Confirm Reschedule</button>
                                        <a href="appointment.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Cancel</a>
                                    </div>';
                            } else {
                                echo '
                                    <div class="mt-6 p-4 bg-orange-100 text-orange-700 rounded-lg text-center">
                                        No available time slots for this date.
                                    </div>
                                    <div class="flex justify-center gap-4 mt-6">
                                        <a href="?action=reschedule&id=' . urlencode($appoid) . '&title=' . urlencode($title) . '&doc=' . urlencode($docname) . '&scheduleid=' . urlencode($old_scheduleid) . '&docid=' . urlencode($docid) . '&month=' . $month . '&year=' . $year . '" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Back to Calendar</a>
                                        <a href="schedule.php" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Find Other Schedules</a>
                                    </div>';
                            }
                            echo '
                                </form>';
                        }
                    }
                    echo '
                            </div>
                        </div>
                    </div>';
                }
                ?>
                <!-- Change Time Modal -->
                <?php
                if (isset($_GET["action"]) && $_GET["action"] == "change_time" && isset($_GET["id"]) && isset($_GET["title"]) && isset($_GET["scheduleid"])) {
                    $appoid = $_GET["id"];
                    $title = $_GET["title"];
                    $docname = $_GET["doc"];
                    $scheduleid = $_GET["scheduleid"];

                    // Fetch schedule details
                    $stmt_schedule = $database->prepare("SELECT schedule.scheduledate, schedule.scheduletime, schedule.nop, schedule.estimated_duration, doctor.docname
                                                        FROM schedule
                                                        INNER JOIN doctor ON schedule.docid = doctor.docid
                                                        WHERE schedule.scheduleid = ?");
                    $stmt_schedule->bind_param("i", $scheduleid);
                    $stmt_schedule->execute();
                    $result_schedule = $stmt_schedule->get_result();
                    if ($result_schedule->num_rows == 0) {
                        echo '
                        <div id="change-time-popup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
                            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                                <div class="flex justify-end">
                                    <a href="appointment.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                                </div>
                                <div class="mt-4 text-center">
                                    <p class="text-gray-600">Schedule not found.</p>
                                    <div class="flex justify-center gap-4 mt-6">
                                        <a href="appointment.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Close</a>
                                    </div>
                                </div>
                            </div>
                        </div>';
                    } else {
                        $schedule = $result_schedule->fetch_assoc();
                        $scheduletime = $schedule['scheduletime'];
                        $scheduledate = $schedule['scheduledate'];
                        $nop = $schedule['nop'];
                        $estimated_duration = $schedule['estimated_duration'];
                        $docname = $schedule['docname'];

                        // Fetch booked slots
                        $stmt_booked = $database->prepare("SELECT apponum FROM appointment WHERE scheduleid = ?");
                        $stmt_booked->bind_param("i", $scheduleid);
                        $stmt_booked->execute();
                        $result_booked = $stmt_booked->get_result();
                        $booked_apponums = [];
                        while ($booked_row = $result_booked->fetch_assoc()) {
                            $booked_apponums[] = $booked_row['apponum'];
                        }
                        $stmt_booked->close();

                        echo '
                        <div id="change-time-popup" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
                            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                                <div class="flex justify-end">
                                    <a href="appointment.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                                </div>
                                <div class="mt-4">
                                    <h2 class="text-xl font-semibold text-gray-800 mb-4 text-center">Change Appointment Time</h2>
                                    <p class="text-gray-600 mb-2 text-center">Current Appointment: ' . htmlspecialchars(substr($title, 0, 40)) . ' with ' . htmlspecialchars(substr($docname, 0, 40)) . ' on ' . date("F j, Y", strtotime($scheduledate)) . '</p>
                                    <form action="reschedule.php" method="post" class="mt-4">
                                        <input type="hidden" name="appoid" value="' . htmlspecialchars($appoid) . '">
                                        <input type="hidden" name="scheduleid" value="' . htmlspecialchars($scheduleid) . '">
                                        <div class="grid grid-cols-1 gap-4">';

                        list($h, $m, $s) = explode(':', $estimated_duration);
                        $duration_minutes = ($h * 60) + $m + ($s / 60);
                        $current_slot_time = new DateTime($scheduletime);
                        $slot_available_count = 0;

                        for ($i = 1; $i <= $nop; $i++) {
                            $slot_start_time = $current_slot_time->format('H:i:s');
                            $display_start_time = $current_slot_time->format('g:i A');
                            $current_slot_time->modify('+' . $duration_minutes . ' minutes');
                            $display_end_time = $current_slot_time->format('g:i A');

                            $is_booked = in_array($i, $booked_apponums);
                            $is_past = (new DateTime($scheduledate . ' ' . $slot_start_time)) < (new DateTime('now'));

                            if (!$is_booked && !$is_past) {
                                $slot_available_count++;
                                echo '
                                <label class="flex items-center p-3 rounded-lg bg-blue-100 hover:bg-blue-200 cursor-pointer transition-colors">
                                    <input type="radio" name="new_apponum" value="' . $i . '" class="form-radio h-4 w-4 text-blue-600" required>
                                    <span class="ml-2">' . htmlspecialchars($display_start_time . ' - ' . $display_end_time) . '</span>
                                </label>';
                            }
                        }

                        echo '
                                        </div>';
                        if ($slot_available_count > 0) {
                            echo '
                                        <div class="flex justify-center gap-4 mt-6">
                                            <button type="submit" name="change_time" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Confirm Time Change</button>
                                            <a href="appointment.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Cancel</a>
                                        </div>';
                        } else {
                            echo '
                                        <div class="mt-6 p-4 bg-orange-100 text-orange-700 rounded-lg text-center">
                                            No available time slots for this date.
                                        </div>
                                        <div class="flex justify-center gap-4 mt-6">
                                            <a href="?action=reschedule&id=' . urlencode($appoid) . '&title=' . urlencode($title) . '&doc=' . urlencode($docname) . '&scheduleid=' . urlencode($scheduleid) . '&docid=' . urlencode($docid) . '" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Reschedule to Another Date</a>
                                            <a href="appointment.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Cancel</a>
                                        </div>';
                        }
                        echo '
                                    </form>
                                </div>
                            </div>
                        </div>';
                    }
                    $stmt_schedule->close();
                }
                ?>
            </div>
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
        setTimeout(() => {
            const alert = document.getElementById("alert");
            if (alert) {
                alert.style.transition = "opacity 0.5s ease";
                alert.style.opacity = "0";
                setTimeout(() => alert.remove(), 500);
            }
        }, 3000);
    </script>
    <script src="../js/active-link.js"></script>
    <script src="../js/date-time.js"></script>
</body>

</html>
original