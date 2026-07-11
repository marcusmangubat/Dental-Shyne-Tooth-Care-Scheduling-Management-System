<?php
session_start();

// Security: Regenerate session ID periodically
if (!isset($_SESSION['last_regeneration'])) {
    $_SESSION['last_regeneration'] = time();
} elseif (time() - $_SESSION['last_regeneration'] > 300) {
    session_regenerate_id(true);
    $_SESSION['last_regeneration'] = time();
}

// Authentication check
if (!isset($_SESSION["user"]) || $_SESSION["user"] == "" || $_SESSION['usertype'] != 'p') {
    header("location: ../login.php");
    exit();
}

$useremail = $_SESSION["user"];

// Include database connection
require_once "../config.php";

// Initialize variables
$error_message = '';
$success_message = '';
$userrow = null;
$userid = null;
$username = 'Guest';

try {
    $stmt = $database->prepare("SELECT pid, pname, pemail FROM patient WHERE pemail = ?");
    if (!$stmt) {
        throw new Exception("Database prepare failed: " . $database->error);
    }

    $stmt->bind_param("s", $useremail);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        session_destroy();
        header("location: ../login.php");
        exit();
    }

    $userfetch = $result->fetch_assoc();
    $userid = $userfetch['pid'];
    $username = htmlspecialchars($userfetch['pname'], ENT_QUOTES, 'UTF-8');
    $stmt->close();
} catch (Exception $e) {
    error_log("Schedule page error: " . $e->getMessage());
    $error_message = "An error occurred while loading your profile. Please try again.";
}

// Set timezone
date_default_timezone_set('Asia/Manila');
$today = date('Y-m-d');
$month_start = date('Y-m-01');
$month_end = date('Y-m-t');

$selected_date = null;
if (isset($_GET['date'])) {
    $input_date = $_GET['date'];

    // Validate date format
    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $input_date)) {
        $date_obj = DateTime::createFromFormat('Y-m-d', $input_date);

        // Check if date is valid and not in the past
        if ($date_obj && $date_obj->format('Y-m-d') === $input_date) {
            if ($input_date >= $today) {
                $selected_date = $input_date;
            } else {
                $error_message = "Cannot select a date in the past.";
            }
        } else {
            $error_message = "Invalid date format.";
        }
    } else {
        $error_message = "Invalid date format.";
    }
}

$slots = [];
if ($selected_date && !$error_message) {
    try {
        // Fixed: Cast schedule.docid to INT to match doctor.docid type
        $sql_slots = "SELECT s.scheduleid, s.scheduledate, s.scheduletime, 
                             CAST(s.docid AS UNSIGNED) as docid, d.docname, s.nop,
                             COUNT(a.appoid) as booked, s.title
                      FROM schedule s
                      JOIN doctor d ON CAST(s.docid AS UNSIGNED) = d.docid
                      LEFT JOIN appointment a ON s.scheduleid = a.scheduleid
                      WHERE s.scheduledate = ?
                      GROUP BY s.scheduleid, s.scheduledate, s.scheduletime, d.docid, d.docname, s.nop, s.title
                      ORDER BY d.docname, s.scheduletime";

        $stmt = $database->prepare($sql_slots);
        if (!$stmt) {
            throw new Exception("Failed to prepare slots query: " . $database->error);
        }

        $stmt->bind_param("s", $selected_date);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $row['status'] = ($row['booked'] < $row['nop']) ? 'Available' : 'Unavailable';
            // Sanitize output data
            $row['docname'] = htmlspecialchars($row['docname'], ENT_QUOTES, 'UTF-8');
            $row['title'] = htmlspecialchars($row['title'], ENT_QUOTES, 'UTF-8');
            $slots[$row['docid']][] = $row;
        }

        $stmt->close();
    } catch (Exception $e) {
        error_log("Slots query error: " . $e->getMessage());
        $error_message = "Unable to load available slots. Please try again.";
    }
}

$confirmation = null;
if (isset($_SESSION['confirmation'])) {
    try {
        $conf_data = $_SESSION['confirmation'];

        if (isset($conf_data['appoid']) && is_numeric($conf_data['appoid'])) {
            $sql_confirm = "SELECT a.appoid, a.apponum, a.appodate, 
                                   s.scheduledate, s.scheduletime, d.docname, 
                                   p.pname, s.title
                            FROM appointment a
                            JOIN schedule s ON a.scheduleid = s.scheduleid
                            JOIN doctor d ON CAST(s.docid AS UNSIGNED) = d.docid
                            JOIN patient p ON a.pid = p.pid
                            WHERE a.appoid = ?";

            $stmt = $database->prepare($sql_confirm);
            if ($stmt) {
                $stmt->bind_param("i", $conf_data['appoid']);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $confirmation = $result->fetch_assoc();
                    // Sanitize confirmation data
                    foreach ($confirmation as $key => $value) {
                        if (is_string($value)) {
                            $confirmation[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                        }
                    }
                }

                $stmt->close();
            }
        }

        // Clear confirmation from session after displaying
        unset($_SESSION['confirmation']);
    } catch (Exception $e) {
        error_log("Confirmation error: " . $e->getMessage());
        unset($_SESSION['confirmation']);
    }
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Schedule - Dental Shyne</title>
    <link rel="stylesheet" href="../css/aindex.css">
    <link rel="stylesheet" href="../css/table.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
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

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
        }

        .calendar-day {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
            min-height: 80px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .calendar-day:hover:not(.calendar-day-past) {
            background-color: #f0f0f0;
            transform: scale(1.02);
        }

        .calendar-day-today {
            background-color: #e0f7fa;
            border: 2px solid #00A7B5;
            font-weight: bold;
        }

        .calendar-day-selected {
            background-color: #00A7B5;
            color: white;
        }

        .calendar-day-past {
            background-color: #f3f3f3;
            color: #999;
            cursor: not-allowed;
            opacity: 0.6;
        }

        .slot-available {
            background-color: #10b981;
            color: white;
            padding: 8px;
            margin: 5px 0;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            width: 100%;
        }

        .slot-available:hover {
            background-color: #059669;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .slot-unavailable {
            background-color: #ef4444;
            color: white;
            padding: 8px;
            margin: 5px 0;
            border-radius: 6px;
            cursor: not-allowed;
            opacity: 0.7;
        }

        .calendar-header {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            text-align: center;
            font-weight: bold;
            margin-bottom: 10px;
            padding: 10px 0;
            background-color: #f8f9fa;
            border-radius: 6px;
        }

        /* Added alert styles */
        .alert {
            padding: 12px 16px;
            border-radius: 6px;
            margin-bottom: 16px;
            animation: fadeInUp 0.4s ease-out;
        }

        .alert-error {
            background-color: #fee;
            border-left: 4px solid #ef4444;
            color: #991b1b;
        }

        .alert-success {
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
            color: #065f46;
        }

        .doctor-card {
            transition: all 0.3s ease;
        }

        .doctor-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        /* Loading state */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: white;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="container flex">
        Sidebar
        <aside class="sidebar w-64 bg-white shadow-md" role="navigation" aria-label="Main navigation">
            <div class="p-6 text-center border-b border-gray-200">
                <img src="../photo/form-logo.png" alt="Dental Shyne Logo" class="mx-auto mb-4">
                <h2 class="text-sm font-semibold text-gray-800 mb-1"><?php echo explode(" ", trim($username))[0]; ?></h2>
                <p class="text-xs text-gray-500" title="<?php echo $useremail; ?>"><?php echo substr($useremail, 0, 30); ?></p>
            </div>
            <hr>
            <nav>
                <ul class="space-y-2 p-4">
                    <li><a href="index.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200"><i class="fas fa-home" aria-hidden="true"></i> Home</a></li>
                    <li><a href="schedule.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 active text-blue-600 font-semibold" aria-current="page"><i class="fas fa-clock" aria-hidden="true"></i> Doctor Schedule</a></li>
                    <li><a href="appointment.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200"><i class="fas fa-clipboard-list" aria-hidden="true"></i> My Appointment</a></li>
                    <li><a href="settings.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200"><i class="fas fa-cog" aria-hidden="true"></i> Settings</a></li>
                    <hr>
                    <li><a href="../logout.php" class="flex items-center gap-2 px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 transition"><i class="fas fa-sign-out-alt" aria-hidden="true"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>

        Main Content
        <main class="main-content flex-1 p-6" role="main">
            <header class="header flex justify-between items-center mb-6">
                <a href="index.php">
                    <button class="back-btn flex items-center bg-white border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-100 transition" aria-label="Go back to home">
                        <img src="../icons/arrow-left.png" alt="" class="w-4 h-4 mr-2" aria-hidden="true">
                        Back
                    </button>
                </a>
                <div class="datetime" id="datetime" aria-live="polite"></div>
            </header>

            <?php if ($error_message): ?>
                Added error message display
                <div class="alert alert-error" role="alert">
                    <strong>Error:</strong> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($success_message): ?>
                Added success message display
                <div class="alert alert-success" role="alert">
                    <strong>Success:</strong> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($confirmation): ?>
                Confirmation Page
                <article class="max-w-md mx-auto bg-white p-6 rounded-xl shadow-md fade-in-up" role="article" aria-labelledby="confirmation-heading">
                    <h2 id="confirmation-heading" class="text-2xl font-bold mb-4 text-green-600">
                        <i class="fas fa-check-circle" aria-hidden="true"></i> Appointment Confirmed
                    </h2>
                    <dl class="space-y-2">
                        <div>
                            <dt class="text-sm font-semibold text-gray-600">Appointment Number:</dt>
                            <dd class="text-lg font-bold text-gray-900"><?php echo $confirmation['apponum']; ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-600">Patient:</dt>
                            <dd class="text-sm text-gray-900"><?php echo $confirmation['pname']; ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-600">Doctor:</dt>
                            <dd class="text-sm text-gray-900"><?php echo $confirmation['docname']; ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-600">Service:</dt>
                            <dd class="text-sm text-gray-900"><?php echo $confirmation['title']; ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-600">Date:</dt>
                            <dd class="text-sm text-gray-900"><?php echo date('F j, Y', strtotime($confirmation['scheduledate'])); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-semibold text-gray-600">Time:</dt>
                            <dd class="text-sm text-gray-900"><?php echo date('g:i A', strtotime($confirmation['scheduletime'])); ?></dd>
                        </div>
                    </dl>
                    <p class="mt-4 text-green-600 text-sm">
                        <i class="fas fa-envelope" aria-hidden="true"></i> Confirmation sent to your email.
                    </p>
                    <a href="test.php" class="inline-block mt-4 py-2 px-6 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition">
                        Book Another Appointment
                    </a>
                </article>

            <?php else: ?>
                Calendar Page
                <div class="bg-white p-6 rounded-xl shadow-md fade-in-up">
                    <h1 class="text-2xl font-bold mb-4">
                        <i class="fas fa-calendar-alt" aria-hidden="true"></i>
                        <?php echo date('F Y'); ?> Appointment Calendar
                    </h1>

                    <div class="calendar-header" role="row">
                        <div role="columnheader">Sun</div>
                        <div role="columnheader">Mon</div>
                        <div role="columnheader">Tue</div>
                        <div role="columnheader">Wed</div>
                        <div role="columnheader">Thu</div>
                        <div role="columnheader">Fri</div>
                        <div role="columnheader">Sat</div>
                    </div>

                    <div class="calendar-grid" role="grid" aria-label="Calendar days">
                        <?php
                        $month_start_ts = strtotime($month_start);
                        $month_end_ts = strtotime($month_end);
                        $today_ts = strtotime($today);
                        $first_day_of_month = date('w', $month_start_ts);

                        // Add padding for first week
                        for ($i = 0; $i < $first_day_of_month; $i++) {
                            echo '<div class="calendar-day bg-gray-100" role="gridcell" aria-hidden="true"></div>';
                        }

                        $current_date = $month_start_ts;
                        while ($current_date <= $month_end_ts) {
                            $date_str = date('Y-m-d', $current_date);
                            $day_num = date('j', $current_date);
                            $is_past = $current_date < $today_ts;

                            $class = 'calendar-day';
                            if ($date_str === $today) {
                                $class .= ' calendar-day-today';
                            }
                            if ($date_str === $selected_date) {
                                $class .= ' calendar-day-selected';
                            }
                            if ($is_past) {
                                $class .= ' calendar-day-past';
                            }

                            echo '<div class="' . $class . '" role="gridcell">';

                            if (!$is_past) {
                                $aria_label = date('F j, Y', $current_date);
                                if ($date_str === $today) {
                                    $aria_label .= ' (Today)';
                                }
                                echo '<a href="?date=' . $date_str . '" class="block text-sm font-bold" aria-label="Select ' . $aria_label . '">' . $day_num . '</a>';
                            } else {
                                echo '<span class="block text-sm font-bold" aria-label="' . date('F j, Y', $current_date) . ' (Past date)">' . $day_num . '</span>';
                            }

                            echo '</div>';
                            $current_date = strtotime('+1 day', $current_date);
                        }

                        // Add padding for last week
                        $last_day_of_month = date('w', $month_end_ts);
                        for ($i = $last_day_of_month + 1; $i < 7; $i++) {
                            echo '<div class="calendar-day bg-gray-100" role="gridcell" aria-hidden="true"></div>';
                        }
                        ?>
                    </div>

                    <?php if ($selected_date): ?>
                        Slots for Selected Date
                        <section class="mt-6" aria-labelledby="slots-heading">
                            <h2 id="slots-heading" class="text-xl font-semibold mb-4">
                                Available Slots for <?php echo date('F j, Y', strtotime($selected_date)); ?>
                            </h2>

                            <?php if (empty($slots)): ?>
                                <div class="text-center py-8 bg-white rounded-xl shadow-md">
                                    <img src="../photo/nodata.png" alt="" class="mx-auto w-40 opacity-80" aria-hidden="true">
                                    <p class="text-lg font-bold text-gray-700 mt-4">No available slots for this date</p>
                                    <a href="test.php" class="inline-block mt-4 py-2 px-6 rounded-lg bg-blue-600 text-white font-medium hover:bg-blue-700 transition">
                                        Back to Calendar
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                                    <?php foreach ($slots as $docid => $doc_slots): ?>
                                        <?php $docname = $doc_slots[0]['docname']; ?>
                                        <article class="bg-gray-50 p-4 rounded-lg shadow doctor-card" aria-labelledby="doctor-<?php echo $docid; ?>">
                                            <h3 id="doctor-<?php echo $docid; ?>" class="text-lg font-semibold text-gray-800 mb-3">
                                                <i class="fas fa-user-md" aria-hidden="true"></i> <?php echo $docname; ?>
                                            </h3>

                                            <?php foreach ($doc_slots as $slot): ?>
                                                <form method="GET" action="booking.php" class="mb-2">

                                                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                                    <input type="hidden" name="scheduleid" value="<?php echo $slot['scheduleid']; ?>">
                                                    <input type="hidden" name="scheduledate" value="<?php echo $slot['scheduledate']; ?>">

                                                    <?php if ($slot['status'] === 'Available'): ?>
                                                        <button type="submit"
                                                            class="slot-available text-sm"
                                                            aria-label="Book appointment with <?php echo $docname; ?> at <?php echo date('g:i A', strtotime($slot['scheduletime'])); ?> for <?php echo $slot['title']; ?>">
                                                            <i class="fas fa-clock" aria-hidden="true"></i>
                                                            <?php echo date('g:i A', strtotime($slot['scheduletime'])); ?> - <?php echo $slot['title']; ?>
                                                            <br>
                                                            <small>(<?php echo ($slot['nop'] - $slot['booked']); ?> slots left)</small>
                                                        </button>
                                                    <?php else: ?>
                                                        <div class="slot-unavailable text-sm" aria-label="Fully booked">
                                                            <i class="fas fa-times-circle" aria-hidden="true"></i>
                                                            <?php echo date('g:i A', strtotime($slot['scheduletime'])); ?> - <?php echo $slot['title']; ?>
                                                            <br>
                                                            <small>(Fully Booked)</small>
                                                        </div>
                                                    <?php endif; ?>
                                                </form>
                                            <?php endforeach; ?>
                                        </article>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </section>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="../js/active-link.js"></script>
    <script src="../js/date-time.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Prevent double submission
            const forms = document.querySelectorAll('form[action="booking.php"]');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const button = this.querySelector('button[type="submit"]');
                    if (button && !button.disabled) {
                        button.disabled = true;
                        button.innerHTML = '<span class="loading"></span> Booking...';

                        // Re-enable after 3 seconds as fallback
                        setTimeout(() => {
                            button.disabled = false;
                            button.innerHTML = button.getAttribute('aria-label');
                        }, 3000);
                    }
                });
            });

            // Handle unavailable slot clicks
            document.querySelectorAll('.slot-unavailable').forEach(slot => {
                slot.style.cursor = 'not-allowed';
            });

            // Add keyboard navigation for calendar
            document.querySelectorAll('.calendar-day a').forEach(link => {
                link.addEventListener('keydown', function(e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.click();
                    }
                });
            });

            // Auto-dismiss alerts after 5 seconds
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    alert.style.transition = 'opacity 0.5s ease';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }, 5000);
            });
        });
    </script>
</body>

</html>