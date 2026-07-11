<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule</title>
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

    date_default_timezone_set('Asia/Manila');
    $today = date('Y-m-d');

    $month = isset($_GET['month']) ? intval($_GET['month']) : date('n');
    $year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
    $selected_date = isset($_GET['date']) ? $_GET['date'] : null;

    $sqlmain = "SELECT * FROM schedule INNER JOIN doctor ON schedule.docid = doctor.docid WHERE schedule.scheduledate >= '$today' ORDER BY schedule.scheduledate ASC";
    $sqlpt1 = "";
    $insertkey = "";
    $p = '';
    $searchtype = "All";
    if ($_POST && !empty($_POST["search"])) {
        $keyword = $_POST["search"];
        $sqlmain = "SELECT * FROM schedule INNER JOIN doctor ON schedule.docid = doctor.docid
                    WHERE schedule.scheduledate >= '$today'
                    AND (doctor.docname = '$keyword'
                    OR doctor.docname LIKE '$keyword%'
                    OR doctor.docname LIKE '%$keyword'
                    OR doctor.docname LIKE '%$keyword%'
                    OR schedule.title = '$keyword'
                    OR schedule.title LIKE '$keyword%'
                    OR schedule.title LIKE '%$keyword'
                    OR schedule.title LIKE '%$keyword%'
                    OR schedule.scheduledate = '$keyword'
                    OR schedule.scheduledate LIKE '$keyword%'
                    OR schedule.scheduledate LIKE '%$keyword'
                    OR schedule.scheduledate LIKE '%$keyword%')
                    ORDER BY schedule.scheduledate ASC";
        $insertkey = $keyword;
        $searchtype = "Search Result: ";
        $p = " '' ";
    }
    $result = $database->query($sqlmain);

    $schedules = [];
    while ($row = $result->fetch_assoc()) {
        $date = $row['scheduledate'];
        if (!isset($schedules[$date])) {
            $schedules[$date] = [];
        }
        $schedules[$date][] = $row;
    }

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

    function generateCalendar($month, $year, $schedules, $today)
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
            $isSelected = isset($_GET['date']) && $_GET['date'] === $date;

            $class = 'p-4 border text-center';
            if ($isPast) {
                $class .= ' text-gray-400 bg-gray-100';
            } else {
                $class .= ' bg-blue-100 cursor-pointer hover:bg-blue-200';
            }
            if ($isSelected) {
                $class .= ' bg-blue-300 font-bold';
            }

            $html .= '<td class="' . $class . '">';
            $html .= '<a href="?date=' . $date . '&month=' . $month . '&year=' . $year . '">' . $day . '</a>';
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
    ?>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <div class="sidebar fixed top-0 left-0 w-64 bg-white shadow-lg h-full z-20 sm:translate-x-0">
            <div class="p-6 text-center border-b border-gray-200">
                <img src="../photo/form-logo.png" alt="Dental Shyne Logo" class="mx-auto mb-4 w-24">
                <h2 class="text-sm font-semibold text-gray-800"><?php echo explode(" ", trim($username))[0]; ?></h2>
                <p class="text-xs text-gray-500"><?php echo substr($useremail, 0, 30); ?></p>
            </div>
            <ul class="space-y-2 p-4">
                <li><a href="index.php" class="flex items-center gap-2 p-3 rounded-lg hover:bg-gray-200"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="schedule.php" class="flex items-center gap-2 p-3 rounded-lg bg-gray-200 active"><i class="fas fa-clock"></i> Calendar Schedule</a></li>
                <li><a href="appointment.php" class="flex items-center gap-2 p-3 rounded-lg hover:bg-gray-200"><i class="fas fa-clipboard-list"></i> My Appointment</a></li>
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
                        <h1 class="text-3xl font-bold text-gray-900"><?php echo $searchtype . " Schedules (" . $result->num_rows . ")"; ?></h1>
                    </div>
                    <div class="text-gray-600" id="datetime"></div>
                </div>

                <!-- Search Bar -->
                <div class="welcome-card p-6 mb-8 fade-in-up-delay">
                    <h2 class="text-2xl font-bold mb-2">Find a Schedule</h2>
                    <form action="" method="POST" class="flex items-center bg-white border border-gray-300 rounded-lg p-2 w-full max-w-md color-black">
                        <input type="search" style="color: black;" name="search" list="doctors" placeholder="Search dentists Name or Email..." class="flex-1 border-none outline-none p-2" value="<?php echo htmlspecialchars($insertkey); ?>">
                        <?php
                        echo '<datalist id="doctors">';
                        $list11 = $database->query("SELECT DISTINCT docname FROM doctor");
                        $list12 = $database->query("SELECT DISTINCT title FROM schedule");
                        for ($y = 0; $y < $list11->num_rows; $y++) {
                            $row00 = $list11->fetch_assoc();
                            $dname = $row00["docname"];
                            echo "<option value='$dname'>";
                        }
                        for ($y = 0; $y < $list12->num_rows; $y++) {
                            $row00 = $list12->fetch_assoc();
                            $dtitle = $row00["title"];
                            echo "<option value='$dtitle'>";
                        }
                        echo '</datalist>';
                        ?>
                        <button type="submit" class="bg-transparent border-none p-2"><i class="fas fa-search text-gray-500"></i></button>
                    </form>
                </div>

                <?php if ($result->num_rows == 0 && !$selected_date): ?>
                    <div class="col-span-full text-center py-8">
                        <img src="../photo/nodata.png" alt="No data" class="w-1/4 mx-auto nodata-img">
                        <p class="text-xl text-gray-700 font-bold uppercase mt-4">No schedules found</p>
                        <a href="schedule.php" class="inline-block mt-6">
                            <button class="px-6 py-2 rounded-lg bg-[#B3E7EB] text-black font-semibold hover:bg-[#00A7B5] shadow-md transition-colors duration-300">
                                Show all Schedules
                            </button>
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Calendar -->
                    <div class="mb-8 bg-white rounded-lg shadow-lg p-6">
                        <div class="flex justify-between mb-4 items-center">
                            <a href="?month=<?php echo $prevMonth; ?>&year=<?php echo $prevYear; ?>" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Previous Month</a>
                            <h2 class="text-2xl font-bold text-gray-900"><?php echo date('F Y', mktime(0, 0, 0, $month, 1, $year)); ?></h2>
                            <a href="?month=<?php echo $nextMonth; ?>&year=<?php echo $nextYear; ?>" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Next Month</a>
                        </div>
                        <?php echo generateCalendar($month, $year, $schedules, $today); ?>
                    </div>

                    <!-- Selected Date Schedules -->
                    <?php if ($selected_date): ?>
                        <div class="mb-4">
                            <h2 class="text-2xl font-bold text-gray-900">Schedules on <?php echo date("F j, Y", strtotime($selected_date)); ?></h2>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 fade-in-up-delay">
                            <?php
                            if (empty($schedules[$selected_date])) {
                                echo '
                                <div class="col-span-full text-center py-8">
                                    <!--<img src="../photo/nodata.png" alt="No data" class="w-1/4 mx-auto nodata-img">-->
                                    <p class="text-xl text-gray-700 font-bold uppercase mt-4">No schedules on this day</p>
                                </div>';
                            } else {
                                foreach ($schedules[$selected_date] as $row) {
                                    $scheduleid = $row["scheduleid"];
                                    $title = $row["title"];
                                    $docname = $row["docname"];
                                    $scheduledate = $row["scheduledate"];
                                    $scheduletime = $row["scheduletime"];
                                    $formattedDate = date("F j, Y", strtotime($scheduledate));
                                    $formattedTime = date("g:i A", strtotime($scheduletime));
                                    $nop = $row["nop"];

                                    if (empty($scheduleid)) {
                                        continue;
                                    }

                                    $sqlCount = "SELECT COUNT(*) as booked FROM appointment WHERE scheduleid = '$scheduleid'";
                                    $resultCount = $database->query($sqlCount);
                                    $countRow = $resultCount->fetch_assoc();
                                    $bookedSlots = $countRow['booked'];
                                    $availableSlots = $nop - $bookedSlots;

                                    $slotClass = '';
                                    $slotText = '';
                                    if ($availableSlots <= 0) {
                                        $slotClass = 'text-red-600 font-semibold';
                                        $slotText = '(' . $bookedSlots . '/' . $nop . ') - Fully Booked';
                                    } else if ($availableSlots <= 2) {
                                        $slotClass = 'text-orange-600 font-semibold';
                                        $slotText = '(' . $bookedSlots . '/' . $nop . ') - Few Left';
                                    } else {
                                        $slotClass = 'text-green-600 font-semibold';
                                        $slotText = '(' . $bookedSlots . '/' . $nop . ') - Available';
                                    }

                                    echo '
                                    <div class="bg-white rounded-lg shadow-lg p-6 hover:shadow-xl transition-shadow card">
                                        <h3 class="text-lg font-semibold text-gray-800">' . htmlspecialchars(substr($title, 0, 20)) . '</h3>
                                        <p class="text-sm text-gray-600"><strong>Doctor:</strong> ' . htmlspecialchars(substr($docname, 0, 20)) . '</p>
                                        <p class="text-sm text-gray-600"><strong>Date:</strong> ' . htmlspecialchars($formattedDate) . '</p>
                                        <p class="text-sm text-gray-600"><strong>Time:</strong> ' . htmlspecialchars($formattedTime) . '</p>
                                        <p class="text-sm text-gray-600"><strong>Slots:</strong> <span class="' . $slotClass . '">' . $slotText . '</span></p>
                                        <div class="mt-4">';
                                    if ($availableSlots > 0) {
                                        echo '
                                            <a href="booking.php?id=' . $scheduleid . '" class="inline-block px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                                                <i class="fas fa-calendar-check mr-2"></i> Book Now
                                            </a>';
                                    } else {
                                        echo '
                                            <button class="inline-block px-4 py-2 bg-gray-400 text-white rounded-lg cursor-not-allowed" disabled>
                                                Fully Booked
                                            </button>';
                                    }
                                    echo '</div></div>';
                                }
                            }
                            ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
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
    </script>
    <script src="../js/active-link.js"></script>
    <script src="../js/date-time.js"></script>
</body>

</html>