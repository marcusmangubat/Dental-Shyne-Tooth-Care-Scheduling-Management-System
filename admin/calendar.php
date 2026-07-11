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
        <title>Calendar</title>
        <script src="../css/tailwind.js"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
        <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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

            #doctors-table th,
            #doctors-table td {
                border: 1px solid #e5e7eb;
            }

            .calendar-day {
                min-height: 120px;
                border: 1px solid #e5e7eb;
                position: relative;
            }

            .appointment-item {
                font-size: 0.75rem;
                padding: 2px 4px;
                margin: 1px 0;
                border-radius: 3px;
                cursor: pointer;
                transition: all 0.2s ease;
            }

            .appointment-item:hover {
                transform: scale(1.02);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0, 0, 0, 0.5);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 1000;
            }

            .modal-content {
                background: white;
                border-radius: 8px;
                padding: 24px;
                max-width: 500px;
                width: 90%;
                max-height: 80vh;
                overflow-y: auto;
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
        <div class="conainer">
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
                        <a href="calendar.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 active text-blue-600 font-semibold">
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
            </div>
            <div class="main-content flex-1 p-6">
                <div class="header">
                    <h1 class="text-2xl font-bold text-gray-900">Appointment Calendar</h1>
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
                <div class="calendar-container bg-white rounded-lg shadow-lg p-6 fade-in-up-delay">
                    <?php
                    // Get current month and year for calendar navigation
                    $currentMonth = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
                    $currentYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
                    // Calculate previous and next month/year
                    $prevMonth = $currentMonth - 1;
                    $prevYear = $currentYear;
                    if ($prevMonth < 1) {
                        $prevMonth = 12;
                        $prevYear--;
                    }
                    $nextMonth = $currentMonth + 1;
                    $nextYear = $currentYear;
                    if ($nextMonth > 12) {
                        $nextMonth = 1;
                        $nextYear++;
                    }
                    // Prepare appointments query with filters
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
                    $sqlmain .= " ORDER BY schedule.scheduledate DESC";
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
                        $date = $row['scheduledate'];
                        if (!isset($appointments[$date])) {
                            $appointments[$date] = [];
                        }
                        // Calculate time range
                        $startDateTime = new DateTime($row['scheduledate'] . ' ' . $row['scheduletime']);
                        list($h, $m, $s) = explode(':', $row['estimated_duration']);
                        $durationSeconds = ($h * 3600) + ($m * 60) + $s;
                        if ($durationSeconds <= 0) {
                            $timeRange = date("h:i A", strtotime($row['scheduletime'])) . ' - N/A';
                            $sortTime = $startDateTime->getTimestamp();
                        } else {
                            $slotOffset = $durationSeconds * ($row['apponum'] - 1);
                            $appointmentStartTime = clone $startDateTime;
                            $appointmentStartTime->modify("+$slotOffset seconds");
                            $appointmentEndTime = clone $appointmentStartTime;
                            $appointmentEndTime->modify("+$durationSeconds seconds");
                            $timeRange = $appointmentStartTime->format("h:i A") . ' - ' . $appointmentEndTime->format("h:i A");
                            $sortTime = $appointmentStartTime->getTimestamp();
                        }
                        $row['timeRange'] = $timeRange;
                        $row['sortTime'] = $sortTime;
                        $appointments[$date][] = $row;
                    }
                    // Sort appointments within each day by start time
                    foreach ($appointments as $date => &$apps) {
                        usort($apps, function ($a, $b) {
                            return $a['sortTime'] <=> $b['sortTime'];
                        });
                    }
                    unset($apps); // Unset reference to avoid issues
                    ?>
                    <!-- Calendar Header -->
                    <div class="flex items-center justify-between mb-6">
                        <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>"
                            class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg transition">
                            ← Previous
                        </a>
                        <h2 class="text-2xl font-bold text-gray-800">
                            <?php echo date('F Y', mktime(0, 0, 0, $currentMonth, 1, $currentYear)); ?>
                        </h2>
                        <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>"
                            class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg transition">
                            Next →
                        </a>
                    </div>
                    <!-- Calendar Grid -->
                    <div class="grid grid-cols-7 gap-1">
                        <!-- Day headers -->
                        <div class="bg-gray-100 p-3 text-center font-semibold text-gray-700">Sun</div>
                        <div class="bg-gray-100 p-3 text-center font-semibold text-gray-700">Mon</div>
                        <div class="bg-gray-100 p-3 text-center font-semibold text-gray-700">Tue</div>
                        <div class="bg-gray-100 p-3 text-center font-semibold text-gray-700">Wed</div>
                        <div class="bg-gray-100 p-3 text-center font-semibold text-gray-700">Thu</div>
                        <div class="bg-gray-100 p-3 text-center font-semibold text-gray-700">Fri</div>
                        <div class="bg-gray-100 p-3 text-center font-semibold text-gray-700">Sat</div>
                        <?php
                        // Calculate first day of month and number of days
                        $firstDay = mktime(0, 0, 0, $currentMonth, 1, $currentYear);
                        $firstDayOfWeek = date('w', $firstDay);
                        $daysInMonth = date('t', $firstDay);
                        // Add empty cells for days before the first day
                        for ($i = 0; $i < $firstDayOfWeek; $i++) {
                            echo '<div class="calendar-day bg-gray-50"></div>';
                        }
                        // Add days of the month
                        for ($day = 1; $day <= $daysInMonth; $day++) {
                            $currentDate = sprintf('%04d-%02d-%02d', $currentYear, $currentMonth, $day);
                            $isToday = ($currentDate == date('Y-m-d')) ? 'bg-blue-50 border-blue-200' : 'bg-white';
                            echo '<div class="calendar-day ' . $isToday . ' p-2">';
                            echo '<div class="font-semibold text-gray-700 mb-1">' . $day . '</div>';
                            // Display appointments for this date (already sorted)
                            if (isset($appointments[$currentDate])) {
                                foreach ($appointments[$currentDate] as $appointment) {
                                    echo '<div class="appointment-item bg-blue-100 text-blue-800 hover:bg-blue-200" 
                                           onclick="showAppointmentModal(\'' .
                                        htmlspecialchars($appointment['pname']) . '\', \'' .
                                        htmlspecialchars($appointment['apponum']) . '\', \'' .
                                        htmlspecialchars($appointment['docname']) . '\', \'' .
                                        htmlspecialchars($appointment['title']) . '\', \'' .
                                        htmlspecialchars($appointment['timeRange']) . '\', \'' .
                                        date('M d, Y', strtotime($appointment['appodate'])) . '\', \'' .
                                        $appointment['appoid'] . '\', \'' .
                                        htmlspecialchars($appointment['pname']) . '\', \'' .
                                        htmlspecialchars($appointment['title']) . '\', \'' .
                                        htmlspecialchars($appointment['apponum']) . '\')">';
                                    echo '<div class="font-medium">' . htmlspecialchars($appointment['pname']) . '</div>';
                                    echo '<div>' . htmlspecialchars($appointment['timeRange']) . '</div>';
                                    echo '</div>';
                                }
                            }
                            echo '</div>';
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div id="appointmentModal" class="modal-overlay hidden">
                <div class="modal-content">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xl font-bold text-gray-800">Appointment Details</h3>
                        <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
                    </div>
                    <div class="space-y-3">
                        <div><strong>Patient Name:</strong> <span id="modalPatientName"></span></div>
                        <div><strong>Appointment ID:</strong> <span id="modalAppointmentId"></span></div>
                        <div><strong>Doctor:</strong> <span id="modalDoctor"></span></div>
                        <div><strong>Schedule Title:</strong> <span id="modalScheduleTitle"></span></div>
                        <div><strong>Time Range:</strong> <span id="modalScheduleDateTime"></span></div>
                        <div><strong>Appointment Date:</strong> <span id="modalAppointmentDate"></span></div>
                        <div><strong>Events:</strong></div>
                        <div class="mt-4">
                            <a id="deleteLink" href="#" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg transition inline-flex items-center">
                                <img src="../icons/trash.png" alt="Delete" style="width: 16px; height: 16px; margin-right: 5px;">
                                Delete Appointment
                            </a>
                        </div>
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
                <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                        <div class="flex justify-end">
                            <a href="calendar.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
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
                                <a href="calendar.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none">
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
        <script>
            function showAppointmentModal(patientName, appointmentId, doctor, scheduleTitle, scheduleDateTime, appointmentDate, appoid, name, session, apponum) {
                document.getElementById('modalPatientName').textContent = patientName;
                document.getElementById('modalAppointmentId').textContent = appointmentId;
                document.getElementById('modalDoctor').textContent = doctor;
                document.getElementById('modalScheduleTitle').textContent = scheduleTitle;
                document.getElementById('modalScheduleDateTime').textContent = scheduleDateTime;
                document.getElementById('modalAppointmentDate').textContent = appointmentDate;
                document.getElementById('deleteLink').href = '?action=drop&id=' + appoid + '&name=' + encodeURIComponent(name) + '&session=' + encodeURIComponent(session) + '&apponum=' + encodeURIComponent(apponum);
                document.getElementById('appointmentModal').classList.remove('hidden');
            }

            function closeModal() {
                document.getElementById('appointmentModal').classList.add('hidden');
            }
            document.getElementById('appointmentModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });
            const toggleBtn = document.getElementById('accounts-toggle');
            const dropdown = document.getElementById('accounts-dropdown');
            toggleBtn.addEventListener('click', () => {
                dropdown.classList.toggle('hidden');
            });
        </script>
        <script src="../js/date-time.js"></script>
        <script src="../js/active-link.js"></script>
    </body>

    </html>