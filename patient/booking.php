<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
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
            exit(); // Always exit after redirect
        } else {
            $useremail = $_SESSION["user"];
        }
    } else {
        header("location: ../login.php");
        exit(); // Always exit after redirect
    }

    include "../config.php";
    if (!isset($database) || !$database) {
        die("Database connection failed."); // Ensure connection exists
    }

    $userrow = $database->query("SELECT pid, pname FROM patient WHERE pemail = '$useremail'");
    $userfetch = $userrow->fetch_assoc();
    $userid = $userfetch['pid'];
    $username = $userfetch['pname'];

    date_default_timezone_set('Asia/Manila');
    $today = date('Y-m-d'); // Current date, not necessarily appointment date
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
                        <h1 class="text-3xl font-bold text-gray-900">Book Appointment</h1>
                    </div>
                    <div class="text-gray-600" id="datetime"></div>
                </div>

                <!-- Booking Form -->
                <?php
                if (isset($_GET["id"])) {
                    $id = (int)$_GET["id"]; // Sanitize input

                    // Prepared statement to fetch schedule details
                    $stmt_schedule = $database->prepare("SELECT schedule.scheduleid, schedule.title, doctor.docname, doctor.docemail, schedule.scheduledate, schedule.scheduletime, schedule.nop, schedule.estimated_duration FROM schedule INNER JOIN doctor ON schedule.docid=doctor.docid WHERE schedule.scheduleid=? ORDER BY schedule.scheduledate DESC");
                    $stmt_schedule->bind_param("i", $id);
                    $stmt_schedule->execute();
                    $result_schedule = $stmt_schedule->get_result();

                    if ($result_schedule->num_rows > 0) {
                        $row = $result_schedule->fetch_assoc();
                        $scheduleid = $row["scheduleid"];
                        $title = $row["title"];
                        $docname = $row["docname"];
                        $docemail = $row["docemail"];
                        $scheduledate = $row["scheduledate"];
                        $scheduletime = $row["scheduletime"];
                        $nop = $row["nop"]; // Max number of patients for this schedule
                        // Convert estimated_duration to minutes for calculation
                        $estimated_duration_str = $row["estimated_duration"]; // Format 'HH:MM:SS'
                        list($h, $m, $s) = explode(':', $estimated_duration_str);
                        $estimated_duration_minutes = ($h * 60) + $m + ($s / 60);

                        $formattedDate = date("F j, Y", strtotime($scheduledate));

                        // Fetch already booked slots for this schedule
                        $stmt_booked_slots = $database->prepare("SELECT apponum FROM appointment WHERE scheduleid = ?");
                        $stmt_booked_slots->bind_param("i", $scheduleid);
                        $stmt_booked_slots->execute();
                        $result_booked_slots = $stmt_booked_slots->get_result();
                        $booked_apponums = [];
                        while ($booked_row = $result_booked_slots->fetch_assoc()) {
                            $booked_apponums[] = $booked_row['apponum'];
                        }
                        $stmt_booked_slots->close();

                        // Check if patient already has an appointment for this schedule
                        $stmt_patient_booked = $database->prepare("SELECT appoid FROM appointment WHERE pid = ? AND scheduleid = ?");
                        $stmt_patient_booked->bind_param("ii", $userid, $scheduleid);
                        $stmt_patient_booked->execute();
                        $result_patient_booked = $stmt_patient_booked->get_result();
                        $patient_already_booked = ($result_patient_booked->num_rows > 0);
                        $stmt_patient_booked->close();

                        echo '
                        <div class="welcome-card p-6 mb-8 fade-in-up-delay">
                            <form action="booking_complete.php" method="post" class="max-w-lg mx-auto">
                                <input type="hidden" name="scheduleid" value="' . $scheduleid . '">
                                <input type="hidden" name="date" value="' . $today . '"> <!-- Still pass current date for logging/reference -->
                                <h2 class="text-2xl font-bold mb-4 text-center">Schedule Details</h2>
                                <div class="space-y-4">
                                    <p class="text-gray-100"><span class="font-semibold">Dentist Name:</span> ' . htmlspecialchars($docname) . '</p>
                                    <p class="text-gray-100"><span class="font-semibold">Dentist Email:</span> ' . htmlspecialchars($docemail) . '</p>
                                    <p class="text-gray-100"><span class="font-semibold">Scheduled Title:</span> ' . htmlspecialchars($title) . '</p>
                                    <p class="text-gray-100"><span class="font-semibold">Scheduled Date:</span> ' . htmlspecialchars($formattedDate) . '</p>
                                </div>';

                        if ($patient_already_booked) {
                            echo '<div class="mt-6 p-4 bg-red-100 text-red-700 rounded-lg text-center">
                                    You have already booked an appointment for this schedule.
                                  </div>';
                            echo '<div class="flex justify-center mt-6">
                                    <a href="appointment.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">View My Appointments</a>
                                  </div>';
                        } else {
                            echo '<div class="mt-6">
                                    <h3 class="text-xl font-semibold text-gray-100 mb-4 text-center">Select Your Time Slot:</h3>
                                    <div class="grid grid-cols-2 gap-4">'; // Grid for time slots

                            $current_slot_time = new DateTime($scheduletime);
                            $slot_available_count = 0;

                            for ($i = 1; $i <= $nop; $i++) {
                                $slot_start_time = $current_slot_time->format('H:i:s'); // For POSTing to booking_complete
                                $display_start_time = $current_slot_time->format('g:i A');

                                $current_slot_time->modify('+' . $estimated_duration_minutes . ' minutes');
                                $display_end_time = $current_slot_time->format('g:i A');

                                $is_booked = in_array($i, $booked_apponums);
                                $is_past = (new DateTime($scheduledate . ' ' . $slot_start_time)) < (new DateTime('now'));

                                $disabled_attr = ($is_booked || $is_past) ? 'disabled' : '';
                                $label_class = ($is_booked || $is_past) ? 'text-gray-400 bg-gray-700 cursor-not-allowed' : 'hover:bg-blue-600 cursor-pointer';

                                if (!$is_booked && !$is_past) {
                                    $slot_available_count++;
                                }

                                echo '
                                <label class="flex items-center p-3 rounded-lg bg-blue-500 text-white ' . $label_class . ' transition-colors duration-200 shadow-md">
                                    <input type="radio" name="selected_slot_time" value="' . htmlspecialchars($slot_start_time) . '" class="form-radio h-4 w-4 text-blue-600" ' . $disabled_attr . ' required>
                                    <span class="ml-2">' . htmlspecialchars($display_start_time . ' - ' . $display_end_time) . '</span>
                                    ' . ($is_booked ? '<span class="ml-auto text-red-300">Booked</span>' : '') . '
                                    ' . ($is_past ? '<span class="ml-auto text-gray-300">Past</span>' : '') . '
                                </label>';
                            }
                            echo '</div>'; // End grid for time slots

                            if ($slot_available_count > 0) {
                                echo '<div class="flex justify-center gap-4 mt-6">
                                        <button type="submit" name="booknow" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition duration-200">
                                            <i class="fas fa-calendar-check mr-2"></i> Confirm Booking
                                        </button>
                                        <a href="schedule.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Cancel</a>
                                    </div>';
                            } else {
                                echo '<div class="mt-6 p-4 bg-orange-100 text-orange-700 rounded-lg text-center">
                                        All time slots for this schedule are currently booked or in the past.
                                    </div>';
                                echo '<div class="flex justify-center mt-6">
                                        <a href="schedule.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">Back to Schedules</a>
                                    </div>';
                            }
                        }
                        echo '</form></div>';
                    } else {
                        echo '
                        <div class="col-span-full text-center py-8">
                            <img src="../photo/nodata.png" alt="No data" class="w-1/4 mx-auto nodata-img">
                            <p class="text-xl text-gray-700 font-bold uppercase mt-4">Schedule not found or invalid.</p>
                            <a href="schedule.php" class="inline-block mt-6">
                                <button class="px-6 py-2 rounded-lg bg-[#B3E7EB] text-black font-semibold hover:bg-[#00A7B5] shadow-md transition-colors duration-300">
                                    Back to Schedules
                                </button>
                            </a>
                        </div>';
                    }
                    $stmt_schedule->close();
                } else {
                    echo '
                    <div class="col-span-full text-center py-8">
                        <img src="../photo/nodata.png" alt="No data" class="w-1/4 mx-auto nodata-img">
                        <p class="text-xl text-gray-700 font-bold uppercase mt-4">No schedule ID provided.</p>
                        <a href="schedule.php" class="inline-block mt-6">
                            <button class="px-6 py-2 rounded-lg bg-[#B3E7EB] text-black font-semibold hover:bg-[#00A7B5] shadow-md transition-colors duration-300">
                                Back to Schedules
                            </button>
                        </a>
                    </div>';
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
    </script>
    <script src="../js/active-link.js"></script>
    <script src="../js/date-time.js"></script>
</body>

</html>