 <?php
    session_start();
    if (isset($_SESSION['user'])) {
        if (($_SESSION['user']) == "" or $_SESSION['usertype'] != 'a') {
            header("location: ../login.php");
        }
    } else {
        header("location: ../login.php");
    }

    include "../config.php";

    $userrow = $database->query("SELECT aemail FROM admin");
    $userfetch = $userrow->fetch_assoc();
    $username = $userfetch['aemail'];
    ?>
 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <meta http-equiv="X-UA-Compatible" content="IE=edge">
     <title>Dashboard</title>
     <link rel="stylesheet" href="../css/aindex.css">
     <script src="../css/tailwind.js"></script>
     <script src="https://cdn.tailwindcss.com"></script>
     <link rel="icon" type="image/png" href="../photo/logo_cropped.png">
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
     <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
     <link rel="stylesheet" href="../css/dashboard.css">
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

         .card:hover {
             transform: translateY(-2px);
             box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
         }

         #doctors-table th,
         #doctors-table td {
             border: 1px solid #e5e7eb;
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
     <div class="continer">
         <div class="sidebar">
             <div class="p-6 text-center border-b border-gray-200">
                 <img src="../photo/form-logo.png" alt="Dental Shyne Logo" class="mx-auto mb-4">
                 <h2 class="text-sm font-semibold text-gray-800 mb-1">Administrator</h2>
                 <p class="text-xs text-gray-500"><?php echo $username ?></p>
             </div>
             <ul class="space-y-2">
                 <li>
                     <a href="index.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 active text-blue-600 font-semibold">
                         <i class="fas fa-tachometer-alt"></i> Dashboard
                     </a>
                 </li>
                 <li>
                     <a href="schedule.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200">
                         <i class="fas fa-clock"></i> Schedule
                     </a>
                 </li>
                 <!-- Accounts Dropdown -->
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

             <!-- Script for Dropdown -->
             <script>
                 const toggleBtn = document.getElementById('accounts-toggle');
                 const dropdown = document.getElementById('accounts-dropdown');

                 toggleBtn.addEventListener('click', () => {
                     dropdown.classList.toggle('hidden');
                 });
             </script>
         </div>

         <?php
            date_default_timezone_set('Asia/Manila');
            $today = date('Y-m-d');

            $patientrow = $database->query("SELECT * FROM patient");
            $doctorrow = $database->query("SELECT * FROM doctor");
            $appointmentrow = $database->query("SELECT * FROM appointment where appodate>='$today';");
            $schedulerow = $database->query("SELECT * FROM schedule where scheduledate>='$today';");

            ?>
         <div class=" main-content flex-1 p-6">
             <div class="header">
                 <h1 class="text-2xl font-bold text-gray-900">Admin</h1>
                 <div class="user"></div>
                 <div class="datetime" id="datetime"></div>
             </div>
             <h1>Status</h1><br>
             <div class="dashboard">
                 <div class="card fade-in-up">
                     <div class="card-content">
                         <i class="fas fa-user-doctor"></i>
                         <div>
                             <h3>Doctors</h3>
                             <p><?php echo $doctorrow->num_rows ?></p>
                         </div>
                     </div>
                 </div>
                 <div class="card fade-in-up">
                     <div class="card-content">
                         <i class="fas fa-user-injured"></i>
                         <div>
                             <h3>Patients</h3>
                             <p><?php echo $patientrow->num_rows ?></p>
                         </div>
                     </div>
                 </div>
                 <div class="card fade-in-up">
                     <div class="card-content">
                         <i class="fas fa-calendar-plus"></i>
                         <div>
                             <h3>New Bookings</h3>
                             <p><?php echo $appointmentrow->num_rows ?></p>
                         </div>
                     </div>
                 </div>
                 <div class="card fade-in-up">
                     <div class="card-content">
                         <i class="fas fa-hourglass-start"></i>
                         <div>
                             <h3>Schedules</h3>
                             <p><?php echo $schedulerow->num_rows ?></p>
                         </div>
                     </div>
                 </div>
             </div>
             <div class="table-container overflow-x-auto appointments fade-in-up-delay">
                 <h2>Upcoming Appointments Until Next <?php echo date("l", strtotime("+1 week")); ?></h2>
                 <table id="doctors-table" class="min-w-[800px] w-full border-collapse fade-in-up-delay">
                     <thead>
                         <tr>
                             <th class="text-center py-2 px-4">Appointment Number</th>
                             <th class="text-center py-2 px-4">Patient Name</th>
                             <th class="text-center py-2 px-4">Doctor</th>
                             <th class="text-center py-2 px-4">Session</th>
                         </tr>
                     </thead>
                     <tbody>
                         <?php
                            $nextweek = date('Y-m-d', strtotime('+1 week'));
                            $sqlmain = "SELECT appointment.appoid, schedule.scheduleid, schedule.title, doctor.docname, patient.pname, schedule.scheduledate, schedule.scheduletime, appointment.apponum, appointment.appodate FROM schedule INNER JOIN appointment ON schedule.scheduleid = appointment.scheduleid INNER JOIN patient ON patient.pid = appointment.pid INNER JOIN doctor ON doctor.docid = schedule.docid WHERE schedule.scheduledate >= '$today' AND schedule.scheduledate <= '$nextweek' ORDER BY schedule.scheduledate DESC";

                            $result = $database->query($sqlmain);
                            if ($result->num_rows == 0) {
                                echo '
                                <tr>
                                    <td colspan="4" style="text-align:center;">
                                        <img src="../photo/nodata.png" alt="No data" style="width:25%; display:block; margin:0 auto;" class="nodata-img">
                                        <br>
                                        <p class="heading-main12 nodata-img" style="margin-left: 45px; font-size: 20px; color: rgb(49, 49, 49); font-family: Arial, sans-serif; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; line-height: 1.5;">We dont have any Upcoming Appointment !</p>
                                    </td>
                                </tr>';
                            } else {
                                for ($x = 0; $x < $result->num_rows; $x++) {
                                    $row = $result->fetch_assoc();
                                    $appoid = $row['appoid'];
                                    $scheduleid = $row['scheduleid'];
                                    $title = $row['title'];
                                    $docname = $row['docname'];
                                    $scheduledate = $row['scheduledate'];
                                    $scheduletime = $row['scheduletime'];
                                    $pname = $row['pname'];
                                    $apponum = $row['apponum'];
                                    $appodate = $row['appodate'];
                                    echo '
                                <tr>
                                    <td style="text-align:center;">' . $apponum . '</td>
                                    <td style="text-align:center;">' . $pname . '</td>
                                    <td style="text-align:center;">' . $docname . '</td>
                                    <td style="text-align:center;">' . $title . '</td>

                                </tr>
                                ';
                                }
                            }
                            ?>
                     </tbody>
                 </table>
             </div>
             <div class=" table-container overflow-x-auto appointments fade-in-up-delay">
                 <h2>Upcoming Sessions Until Next <?php echo date("l", strtotime("+1 week")); ?></h2>
                 <table id="doctors-table" class="min-w-[800px] w-full border-collapse fade-in-up-delay">
                     <thead>
                         <tr>
                             <th class="text-center py-2 px-4">Session Title</th>
                             <th class="text-center py-2 px-4">Doctor</th>
                             <th class="text-center py-2 px-4">Scheduled Date & Time</th>
                         </tr>
                     </thead>
                     <tbody>
                         <?php
                            $nextweek = date('Y-m-d', strtotime('+1 week'));
                            $sqlmain = "SELECT schedule.scheduleid, schedule.title, doctor.docname, schedule.scheduledate, schedule.scheduletime, schedule.nop FROM schedule INNER JOIN doctor ON schedule.docid = doctor.docid WHERE schedule.scheduledate >= '$today' AND schedule.scheduledate <= '$nextweek' ORDER BY schedule.scheduledate DESC";

                            $result = $database->query($sqlmain);
                            if ($result->num_rows == 0) {
                                echo '
                                <tr>
                                    <td colspan="3" style="text-align:center;">
                                        <img src="../photo/nodata.png" alt="No data" style="width:25%; display:block; margin:0 auto;" class="nodata-img">
                                        <br>
                                        <p class="heading-main12 nodata-img" style="margin-left: 45px; font-size: 20px; color: rgb(49, 49, 49); font-family: Arial, sans-serif; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; line-height: 1.5;">We dont have any Upcoming Sessions !</p>
                                    </td>
                                </tr>';
                            } else {
                                for ($x = 0; $x < $result->num_rows; $x++) {
                                    $row = $result->fetch_assoc();
                                    $scheduleid = $row['scheduleid'];
                                    $title = $row['title'];
                                    $docname = $row['docname'];
                                    $scheduledate = $row['scheduledate'];
                                    $scheduletime = $row['scheduletime'];
                                    $nop = $row['nop'];
                                    echo '
                                <tr>
                                    <td style="text-align:center;">' . $title . '</td>
                                    <td style="text-align:center;">' . $docname . '</td>
                                      <td style="text-align:center;">' . date("D, M j, Y", strtotime($scheduledate)) . ' ' . date("h:i A", strtotime($scheduletime)) . '</td>

                                </tr>
                                ';
                                }
                            }
                            ?>
                     </tbody>
                 </table>
             </div>
         </div>
     </div>
     <script src="../js/date-time.js"></script>
     <script src="../js/active-link.js"></script>
 </body>

 </html>