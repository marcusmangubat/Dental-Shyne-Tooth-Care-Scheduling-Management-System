<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="../css/aindex.css">
    <link rel="stylesheet" href="../css/table.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="../css/tailwind.js"></script>
    <link rel="icon" type="image/png" href="../photo/logo_cropped.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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

        /* Apply the animation to the image */
        .nodata-img {
            animation: float 3s ease-in-out infinite;
            /* 3-second duration, smooth easing, repeats indefinitely */
        }

        .welcome-card {
            background: linear-gradient(135deg, #00A7B5 0%, #0891b2 100%);
            color: white;
            border-radius: 12px;
            padding: 24px;
            margin: 24px 0;
            box-shadow: 0 4px 12px rgba(0, 167, 181, 0.2);
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }

        #doctors-table th,
        #doctors-table td {
            border: 1px solid #e5e7eb;
            /* Tailwind’s gray-200 */
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
    if (isset($_SESSION["user"])) {
        if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'p') {
            header("location: .//login.php");
        } else {
            $useremail = $_SESSION["user"];
        }
    } else {
        header("location: ../login.php");
    }

    // Include database connection file
    include "../config.php";
    $userrow = $database->query("SELECT * FROM patient WHERE pemail = '$useremail'");
    $userfetch = $userrow->fetch_assoc();
    $userid = $userfetch['pid'];
    $username = $userfetch['pname'];
    ?>
    <div class="cntainer">
        <div class="sidebar">
            <div class="p-6 text-center border-b border-gray-200">
                <img src="../photo/form-logo.png" alt="Dental Shyne Logo" class=" mx-auto mb-4">
                <h2 class="text-sm font-semibold text-gray-800 mb-1"><?php echo explode(" ", trim($username))[0]; ?></h2>
                <p class="text-xs text-gray-500"><?php echo substr($useremail, 0, 30) ?></p>
            </div>
            <hr>
            <ul class="space-y-2">
                <li>
                    <a href="index.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 active text-blue-600 font-semibold">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li>
                    <a href="doctors.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200">
                        <i class="fas fa-user-md"></i> Doctors
                    </a>
                </li>
                <li>
                    <a href="schedule.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200">
                        <i class="fas fa-clock"></i> Doctor Schedule
                    </a>
                </li>
                <li>
                    <a href="appointment.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 ">
                        <i class="fas fa-clipboard-list"></i> My Appointment
                    </a>
                </li>
                <li>
                    <a href="settings.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200">
                        <i class="fas fa-cog"></i> Settings
                    </a>
                </li>
                <hr>
                <li class="list-none">
                    <a href="../logout.php" class="flex items-center gap-2 px-4 py-2 text-white bg-red-600 rounded-lg hover:bg-red-700 transition">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
        <div class="main-content flex-1 p-6">
            <div class="header">
                <h1 class="text-2xl font-bold text-gray-900">Dashboard
                </h1>
                <div class="user"></div>
                <div class="datetime" id="datetime"></div>
            </div>
            <?php
            date_default_timezone_set('Asia/Manila');
            $today = date('Y-m-d');

            ?>
            <div class="welcome-card shadow-xl/50 fade-in-up-delay">
                <h2 class="text-xl font-bold mb-2">Welcome back, <?php echo substr($username, 0, 30) ?>! 👋</h2>
                <p class="text-black-100">Ready to keep your smile shining? Here's your dental clinic portal overview.</p>
            </div>

            <br>
            <h1 class="text-2xl font-bold text-gray-800 mb-4 fade-in-up-delay">Your Upcomming Booking</h1>
            <div class="table-container overflow-x-auto fade-in-up-delay">
                <table id="doctors-table" class="min-w-[800px] w-full border-collapse fade-in-up-delay">
                    <thead>
                        <tr class="bg-gray-200">
                            <th class="text-center py-2 px-4">Appointment ID</th>
                            <th class="text-center py-2 px-4">Schedule Title</th>
                            <th class="text-center py-2 px-4">Doctor</th>
                            <th class="text-center py-2 px-4">Schedule Date&Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $nextweek = date("Y-m-d", strtotime("+1 week"));
                        $sqlmain = "SELECT * FROM schedule INNER JOIn appointment ON schedule.scheduleid = appointment.scheduleid INNER JOIN patient ON patient.pid = appointment.pid INNER JOIN doctor ON schedule.docid = doctor.docid WHERE patient.pid = $userid AND schedule.scheduledate >= '$today' ORDER BY schedule.scheduledate ASC";

                        $result = $database->query($sqlmain);
                        if ($result->num_rows == 0) {
                            echo '
                                <tr>
                                    <td colspan="4" style="text-align:center;">
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
                                $apponum = $row["apponum"];
                                $docname = $row["docname"];
                                $scheduledate = $row["scheduledate"];
                                $scheduletime = $row["scheduletime"];
                                $formattedDateTime = date("D, M j, Y g:i A", strtotime($scheduledate . ' ' . $scheduletime));
                                echo '<tr>
                                    <td class="text-center py-2 px-4">' . substr($apponum, 0, 20) . '</td>
                                    <td class="text-center py-2 px-4">' . substr($title, 0, 20) . '</td>
                                    <td class="text-center py-2 px-4">' . substr($docname, 0, 20) . '</td>
                                    <td class="text-center py-2 px-4">' . $formattedDateTime . '</td>
                                    </tr>
                                    ';
                            }
                        }

                        ?>
                    </tbody>
                </table>

            </div>

        </div>




        <script src="../js/active-link.js"></script>
        <script src="../js/date-time.js"></script>
</body>

</html>