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

 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="UTF-8">
     <meta http-equiv="X-UA-Compatible" content="IE=edge">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Dashboard</title>
     <link rel="stylesheet" href="../css/aindex.css">
     <link rel="stylesheet" href="../css/table.css">
     <script src="https://cdn.tailwindcss.com"></script>
     <script src="../css/tailwind.js"></script>
     <link rel="icon" type="image/png" href="../photo/logo_cropped.png">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
     <script type="text/javascript">
         function prevent() {
             window.history.forward()
         };
         setTimeout("prevent()", 0);
         window.onunload = function() {
             null
         };
     </script>
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

         .welcome-card {
             background: linear-gradient(135deg, #00A7B5 0%, #0891b2 100%);
             color: white;
             border-radius: 12px;
             padding: 24px;
             margin: 24px 0;
             box-shadow: 0 4px 12px rgba(0, 167, 181, 0.2);
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

         /*animation to the no data image */
         .nodata-img {
             animation: float 3s ease-in-out infinite;
             /* 3-second duration, smooth easing, repeats indefinitely */
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
     <link rel="stylesheet" href="../css/dashboard.css">
 </head>

 <body>

     <div class="cotainer">
         <div class="sidebar">
             <div class="p-6 text-center border-b border-gray-200">
                 <img src="../photo/form-logo.png" alt="Dental Shyne Logo" class=" mx-auto mb-4">
                 <h2 class="text-sm font-semibold text-gray-800 mb-1">Dr. <?php echo explode(" ", trim($username))[0]; ?></h2>
                 <p class="text-xs text-gray-500"><?php echo substr($useremail, 0, 30) ?></p>
             </div>
             <hr>
             <ul class="space-y-2">
                 <li>
                     <a href="index.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 active text-blue-600 font-semibold">
                         <i class="fas fa-tachometer-alt"></i> Dashboard
                     </a>
                 </li>
                 <li>
                     <a href="appointment.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200">
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
                 <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
                 <div class="user"></div>
                 <div class="datetime" id="datetime"></div>
             </div>
             <div class="welcome-card fade-in-up-delay">
                 <h2 class="text-xl font-bold mb-2">Welcome back, Dr. <?php echo htmlspecialchars($username); ?>! 👋</h2>
                 <p class="text-black-100">Ready to make your patients smile brighter today? Here's your dashboard overview.</p>
             </div>
             <div>

                 <?php
                    date_default_timezone_set('Asia/Manila');
                    $today = date('Y-m-d');

                    $patientrow = $database->query("SELECT * FROM patient");
                    $doctorrow = $database->query("SELECT * FROM doctor");
                    $appointmentrow = $database->query("SELECT * FROM appointment WHERE appodate >= '$today'");
                    $schedulerow = $database->query("SELECT * FROM schedule WHERE scheduledate = '$today'");
                    ?>
             </div>

             <br>
             <div class="dashboard">
                 <div class="card fade-in-up-delay">
                     <div class="card-content">
                         <i class="fas fa-user-doctor"></i>
                         <div>
                             <h3>Doctors</h3>
                             <p><?php echo $doctorrow->num_rows ?></p>
                         </div>
                     </div>
                 </div>
                 <div class="card fade-in-up-delay">
                     <div class="card-content">
                         <i class="fas fa-user-injured"></i>
                         <div>
                             <h3>Patients</h3>
                             <p><?php echo $patientrow->num_rows ?></p>
                         </div>
                     </div>
                 </div>
                 <div class="card fade-in-up-delay">
                     <div class="card-content">
                         <i class="fas fa-calendar-plus"></i>
                         <div>
                             <h3>New Bookings</h3>
                             <p><?php echo $appointmentrow->num_rows ?></p>
                         </div>
                     </div>
                 </div>
                 <div class="card fade-in-up-delay">
                     <div class="card-content">
                         <i class="fas fa-hourglass-start"></i>
                         <div>
                             <h3>Todays Schedule</h3>
                             <p><?php echo $schedulerow->num_rows ?></p>
                         </div>
                     </div>
                 </div>
             </div>
             <div class="appointments fade-in-up-delay">
                 <h2 class="text-2xl font-bold text-gray-800 mb-4">Upcoming Appointments</h2>
                 <table id="doctors-table" class="min-w-[800px] w-full border-collapse fade-in-up-delay">
                     <thead>
                         <tr class="bg-gray-200">
                             <th class="text-center py-2 px-4">Title</th>
                             <th class="text-center py-2 px-4"> Appointment Date</th>
                             <th class="text-center py-2 px-4">Time</th>
                         </tr>
                     </thead>
                     <tbody>
                         <?php
                            $nextweek = date('Y-m-d', strtotime('+1 week'));
                            $sqlmain = "SELECT schedule.scheduleid, schedule.title, doctor.docname, schedule.scheduletime, schedule.nop, schedule.scheduledate FROM schedule INNER JOIN doctor ON schedule.docid = doctor.docid WHERE schedule.scheduledate >= '$today' AND schedule.scheduledate <= '$nextweek' ORDER BY schedule.scheduledate DESC";
                            $result = $database->query($sqlmain);
                            if ($result->num_rows == 0) {
                                echo '
                                <tr>
                                    <td colspan="3" style="text-align:center;">
                                        <img src="../photo/nodata.png" alt="No data" style="width:25%; display:block; margin:0 auto;" class="nodata-img">
                                        <br>
                                        <p class="heading-main12 nodata-img" style="margin-left: 45px; font-size: 20px; color: rgb(49, 49, 49); font-family: Arial, sans-serif; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; line-height: 1.5;">We dont have any Upcoming Appointment !</p>
                                    </td>
                                </tr>';
                            } else {
                                for ($x = 0; $x < $result->num_rows; $x++) {
                                    $row = $result->fetch_assoc();
                                    $scheduleid = $row['scheduleid'];
                                    $title = $row['title'];
                                    $docname = $row['docname'];
                                    $scheduledate = $row["scheduledate"];
                                    $scheduletime = $row['scheduletime'];
                                    $nop = $row['nop'];
                                    echo '<tr>
                                        <td class="text-center py-2 px-4">' . substr($title, 0, 30) . '</td>
                                        <td class="text-center py-2 px-4">' . date("F d, Y", strtotime($scheduledate)) . '</td>
                                        <td class="text-center py-2 px-4">' . date("h:i A", strtotime($scheduletime)) . '</td>
                                    </tr>';
                                }
                            }
                            ?>
                     </tbody>
                 </table>
             </div>
         </div>
     </div>
     <script src="../js/active-link.js"></script>
     <script src="../js/date-time.js"></script>
 </body>

 </html>