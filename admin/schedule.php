 <?php
    session_start();
    if (isset($_SESSION["user"])) {
        if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'a') {
            header("location: ../login.php");
        }
    } else {
        header("location: ../login.php");
    }

    // Include database connection
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
     <title>Schedule</title>
     <script src="https://cdn.tailwindcss.com"></script>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
     <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
     <script src="../css/tailwind.js"></script>
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

         .table-container {
             max-width: 100%;
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
     <div class="contaier">
         <div class="sidebar">
             <div class="p-6 text-center border-b border-gray-200">
                 <img src="../photo/form-logo.png" alt="Dental Shyne Logo" class="mx-auto mb-4">
                 <h2 class="text-sm font-semibold text-gray-800 mb-1">Administrator</h2>
                 <p class="text-xs text-gray-500"><?php echo $username; ?></p>
             </div>

             <ul class="space-y-2">
                 <li>
                     <a href="index.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 text-blue-600">
                         <i class="fas fa-tachometer-alt"></i> Dashboard
                     </a>
                 </li>
                 <li>
                     <a href="schedule.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 active text-blue-600 font-semibold">
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
                 <h1 class="text-2xl font-bold text-gray-900">Schedule</h1>
                 <div class="user"></div>
                 <div class="datetime" id="datetime"></div>
             </div>
             <?php
                date_default_timezone_set('Asia/Manila');
                $today = date('Y-m-d');
                $list110 = $database->query("SELECT * FROM schedule;");

                ?>

             <div class="content-header">
                 <div>
                     <h1>Add New Schedule</h1>
                     <p>All Schedule <?php echo $list110->num_rows; ?></p>
                 </div>
                 <a href="?action=add-session&id=none&error=0" class="add-btn">
                     <img src="../icons/plus.png" alt="Plus Icon" style="width: 16px; height: 16px; margin-right: 5px;">
                     Add New Schedule
                 </a>
             </div>
             <form action="" method="post" class="flex items-center justify-center gap-4 p-4">
                 <label for="docid" class="text-center font-medium">Doctor:</label>
                 <select name="docid" id="docid"
                     class="border border-gray-300 rounded-md p-2 w-1/3 h-10 focus:outline-none focus:ring-2 focus:ring-blue-500">
                     <option value="" disabled selected hidden>Choose Doctor Name from the list</option>
                     <?php
                        $list11 = $database->query("select * from doctor order by docname asc;");
                        for ($y = 0; $y < $list11->num_rows; $y++) {
                            $row00 = $list11->fetch_assoc();
                            $sn = $row00["docname"];
                            $id00 = $row00["docid"];
                            echo "<option value= '"  . $id00 . "'> Dr. " . $sn . "</option>";
                        };
                        ?>
                 </select>
                 <input type="submit" name="filter" value="Filter"
                     class="bg-blue-500 text-white font-medium rounded-md p-2 w-1/6 hover:bg-blue-600 transition">
             </form>
             <?php
                if ($_POST) {
                    $sqlpt1 = "";
                    if (!empty($_POST["scheduledate"])) {
                        $scheduledate = $_POST["scheduledate"];
                        $sqlpt1 = "schedule.scheduledate = '$scheduledate'";
                    }

                    $sqlpt2 = "";
                    if (!empty($_POST["docid"])) {
                        $docid = $_POST["docid"];
                        $sqlpt2 = "doctor.docid = '$docid'";
                    }

                    $sqlmain = "SELECT schedule.scheduleid, schedule.title, doctor.docname, schedule.scheduledate, schedule.scheduletime, schedule.nop FROM schedule INNER JOIN doctor ON schedule.docid=doctor.docid";
                    $sqllist = array($sqlpt1, $sqlpt2);
                    $sqlkeywords = array(" where ", " and ");
                    $key2 = 0;
                    foreach ($sqllist as $key) {
                        if (!empty($key)) {
                            $sqlmain .= $sqlkeywords[$key2] . $key;
                            $key2++;
                        };
                    };
                } else {
                    $sqlmain = "SELECT schedule.scheduleid, schedule.title, doctor.docname, schedule.scheduledate, schedule.scheduletime, schedule.nop FROM schedule INNER JOIN doctor ON schedule.docid=doctor.docid ORDER BY schedule.scheduledate DESC";
                }

                ?>

             <div class="table-container overflow-x-auto fade-in-up-delay">
                 <table id="doctors-table" class="min-w-[800px] w-full border-collapse fade-in-up-delay">
                     <thead>
                         <tr class="bg-gray-200">
                             <th class="text-center py-2 px-4">Schedule Title</th>
                             <th class="text-center py-2 px-4">Doctor</th>
                             <th class="text-center py-2 px-4">Schedule Date & Time</th>
                             <th class="text-center py-2 px-4">Number of Appointment</th>
                             <th class="text-center py-2 px-4">Events</th>
                         </tr>
                     </thead>
                     <tbody>
                         <?php
                            $result = $database->query($sqlmain);
                            if ($result->num_rows == 0) {
                                echo '
                                <tr>
                                    <td colspan="5" style="text-align:center;">
                                        <img src="../photo/nodata.png" alt="No data" style="width:25%; display:block; margin:0 auto;" class="nodata-img">
                                        <br>
                                        <p class="heading-main12 nodata-img" style="margin-left: 45px; font-size: 20px; color: rgb(49, 49, 49); font-family: Arial, sans-serif; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; line-height: 1.5;">We couldnt find anything related</p>
                                    </td>
                                </tr>';
                            } else {
                                for ($x = 0; $x < $result->num_rows; $x++) {
                                    $row = $result->fetch_assoc();
                                    $scheduleid = $row["scheduleid"];
                                    $title = $row["title"];
                                    $docname = $row["docname"];
                                    $scheduledate = $row["scheduledate"];
                                    $scheduletime = $row["scheduletime"];
                                    $nop = $row["nop"];
                                    echo '<tr class="hover:bg-gray-50">
                                <td class="hover:bg-gray-50">' . substr($title, 0, 30) . '</td>
                                <td class="py-2 px-4">' . substr($docname, 0, 50) . '</td>
                                <td class="py-2 px-4">' . date("D, M j, Y", strtotime($scheduledate)) . ' ' . date("h:i A", strtotime($scheduletime)) . '</td>
                                <td class="text-center py-2 px-4">' . $nop . '</td>
                                <td class="py-2 px-4">
                                    <div class="action-buttons flex justify-center gap-2">
                                        <a href="?action=edit&id=' . $scheduleid . '">
                                            <button class="btn btn-edit flex items-center px-3 py-1 rounded bg-yellow-50 hover:bg-yellow-100 transition-colors" title="Edit">
                                                <i class="fa-solid fa-edit" style="font-size: 16px; color: #f59e0b;"></i>
                                            </button>
                                        </a>
                                        <a href="?action=view&id=' . $scheduleid . '">
                                            <button class="btn btn-view flex items-center px-3 py-1 rounded bg-blue-50 hover:bg-blue-100 transition-colors" title="View">
                                                <i class="fa-solid fa-eye" style="font-size: 16px; color: #3b82f6;"></i>
                                            </button>
                                        </a>
                                        
                                        <a href="?action=drop&id=' . $scheduleid . '&name=' . $title . '">
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

             <?php
                if ($_GET) {
                    $id = $_GET['id'] ?? null;
                    $action = $_GET['action'];
                    if ($action == 'add-session') {
                        echo '
                    <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
                        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                            <div class="flex justify-end">
                                <a href="schedule.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                            </div>
                            <div class="mt-4">
                                <h2 class="text-2xl font-semibold text-gray-800 text-center mb-6">Add New Appointment</h2>
                                <form action="add_appointment.php" method="POST" class="space-y-6">
                                    <div>
                                        <label for="title" class="block text-sm font-medium text-gray-700">Schedule Title:</label>
                                        <input type="text" name="title" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Schedule Title" required>
                                    </div>
                                    <div>
                                        <label for="docid" class="block text-sm font-medium text-gray-700">Select Dentist:</label>
                                        <select name="docid" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="" disabled selected hidden>Choose Doctor Name from the list</option>';
                        $list11 = $database->query("SELECT * FROM doctor ORDER BY docname ASC;");
                        for ($y = 0; $y < $list11->num_rows; $y++) {
                            $row00 = $list11->fetch_assoc();
                            $sn = $row00["docname"];
                            $id00 = $row00["docid"];
                            echo "<option value= '" . $id00 . "'> Dr. " . $sn . "</option>";
                        };
                        echo '
                                        </select>
                                    </div>
                                    <div>
                                        <label for="nop" class="block text-sm font-medium text-gray-700">Appointment Numbers:</label>
                                        <input type="number" name="nop" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" min="0" placeholder="The final appointment number for this session depends on this number" required>
                                    </div>
                                    <div>
                                        <label for="date" class="block text-sm font-medium text-gray-700">Session Date:</label>
                                        <input type="date" name="date" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" min="' . date('Y-m-d') . '" required>
                                    </div>
                                    <div>
                                        <label for="time" class="block text-sm font-medium text-gray-700">Schedule Time:</label>
                                        <input type="time" name="time" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Time" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Duration:</label>
                                        <div class="flex gap-2 mt-1">
                                            <div class="w-full">
                                                <label for="hours" class="block text-sm font-medium text-gray-700">Hours*</label>
                                                <input type="number" id="hours" name="hours" min="0" max="12" placeholder="Hours" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                            </div>
                                            <div class="w-full">
                                                <label for="minutes" class="block text-sm font-medium text-gray-700">Minutes*</label>
                                                <input type="number" id="minutes" name="minutes" min="0" max="59" placeholder="Minutes" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-center space-x-4">
                                        <input type="reset" value="Reset" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 cursor-pointer">
                                        <input type="submit" value="Place this Appointment" name="shedulesubmit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>';
                    } elseif ($action == 'session-added') {
                        $titleget = $_GET['title'];
                        echo '
                    <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
                        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                            <div class="flex justify-end">
                                <a href="schedule.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                            </div>
                            <div class="mt-4 text-center">
                                <h2 class="text-2xl font-semibold text-gray-800 mb-6">' . substr($titleget, 0, 40) . ' Added Successfully!</h2>
                                <div class="flex justify-center">
                                    <a href="schedule.php" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">OK</a>
                                </div>
                            </div>
                        </div>
                    </div>';
                    } elseif ($action == 'drop') {
                        $nameget = $_GET['name'];
                        echo '
                    <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center fade-in-up-delay justify-center z-50">
                        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                            <div class="flex justify-end">
                                <a href="schedule.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                            </div>
                            <div class="mt-4 text-center">
                                <h2 class="text-2xl font-semibold text-gray-800 mb-6">Are you sure?</h2>
                                <p class="text-gray-700 mb-6">
                                    You want to delete this appointment<br>(' . substr($nameget, 0, 40) . ')
                                </p>
                                <div class="flex justify-center space-x-4">
                                    <a href="delete_appointment.php?id=' . $id . '" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none">
                                        Yes
                                    </a>
                                    <a href="schedule.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none">
                                        No
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>';
                    } elseif ($action == 'edit') {
                        $sqlmain = "SELECT schedule.scheduleid, schedule.title, schedule.docid, doctor.docname, schedule.scheduledate, schedule.scheduletime, schedule.nop, schedule.estimated_duration 
                                FROM schedule 
                                INNER JOIN doctor ON schedule.docid=doctor.docid 
                                WHERE schedule.scheduleid=$id";
                        $result = $database->query($sqlmain);
                        $row = $result->fetch_assoc();
                        $title = $row["title"];
                        $docid = $row["docid"];
                        $docname = $row["docname"];
                        $scheduledate = $row["scheduledate"];
                        $scheduletime = $row["scheduletime"];
                        $nop = $row["nop"];
                        $estimated_duration = $row["estimated_duration"];
                        // Convert duration to hours and minutes
                        $duration_parts = explode(':', $estimated_duration);
                        $hours = (int)$duration_parts[0];
                        $minutes = (int)$duration_parts[1];
                        echo '
                    <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
                        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                            <div class="flex justify-end">
                                <a href="schedule.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                            </div>
                            <div class="mt-4">
                                <h2 class="text-2xl font-semibold text-gray-800 text-center mb-6">Edit Schedule</h2>
                                <form action="edit_schedule.php" method="POST" class="space-y-6">
                                    <input type="hidden" name="scheduleid" value="' . $id . '">
                                    <div>
                                        <label for="title" class="block text-sm font-medium text-gray-700">Schedule Title:</label>
                                        <input type="text" name="title" value="' . htmlspecialchars($title) . '" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Schedule Title" required>
                                    </div>
                                    <div>
                                        <label for="docid" class="block text-sm font-medium text-gray-700">Select Dentist:</label>
                                        <select name="docid" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                            <option value="" disabled>Choose Doctor Name from the list</option>';
                        $list11 = $database->query("SELECT * FROM doctor ORDER BY docname ASC;");
                        for ($y = 0; $y < $list11->num_rows; $y++) {
                            $row00 = $list11->fetch_assoc();
                            $sn = $row00["docname"];
                            $id00 = $row00["docid"];
                            $selected = ($id00 == $docid) ? 'selected' : '';
                            echo "<option value='" . $id00 . "' $selected> Dr. " . $sn . "</option>";
                        };
                        echo '
                                        </select>
                                    </div>
                                    <div>
                                        <label for="nop" class="block text-sm font-medium text-gray-700">Appointment Numbers:</label>
                                        <input type="number" name="nop" value="' . $nop . '" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" min="0" placeholder="The final appointment number for this session depends on this number" required>
                                    </div>
                                    <div>
                                        <label for="date" class="block text-sm font-medium text-gray-700">Session Date:</label>
                                        <input type="date" name="date" value="' . $scheduledate . '" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" min="' . date('Y-m-d') . '" required>
                                    </div>
                                    <div>
                                        <label for="time" class="block text-sm font-medium text-gray-700">Schedule Time:</label>
                                        <input type="time" name="time" value="' . $scheduletime . '" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Time" required>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Duration:</label>
                                        <div class="flex gap-2 mt-1">
                                            <div class="w-full">
                                                <label for="hours" class="block text-sm font-medium text-gray-700">Hours*</label>
                                                <input type="number" id="hours" name="hours" value="' . $hours . '" min="0" max="12" placeholder="Hours" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                            </div>
                                            <div class="w-full">
                                                <label for="minutes" class="block text-sm font-medium text-gray-700">Minutes*</label>
                                                <input type="number" id="minutes" name="minutes" value="' . $minutes . '" min="0" max="59" placeholder="Minutes" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex justify-center space-x-4">
                                        <input type="reset" value="Reset" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 cursor-pointer">
                                        <input type="submit" value="Update Schedule" name="shedulesubmit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 cursor-pointer">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>';
                    } elseif ($action == 'session-edited') {
                        $titleget = $_GET['title'];
                        echo '
                    <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
                        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
                            <div class="flex justify-end">
                                <a href="schedule.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                            </div>
                            <div class="mt-4 text-center">
                                <h2 class="text-2xl font-semibold text-gray-800 mb-6">' . substr($titleget, 0, 40) . ' Updated Successfully!</h2>
                                <div class="flex justify-center">
                                    <a href="schedule.php" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">OK</a>
                                </div>
                            </div>
                        </div>
                    </div>';
                    } elseif ($action == 'view') {
                        $sqlmain = "SELECT schedule.scheduleid, schedule.title, doctor.docname, schedule.scheduledate, schedule.scheduletime, schedule.nop FROM schedule INNER JOIN doctor ON schedule.docid=doctor.docid WHERE schedule.scheduleid=$id";
                        $result = $database->query($sqlmain);
                        $row = $result->fetch_assoc();
                        $docname = $row["docname"];
                        $scheduleid = $row["scheduleid"];
                        $title = $row["title"];
                        $scheduledate = $row["scheduledate"];
                        $scheduletime = $row["scheduletime"];
                        $nop = $row["nop"];

                        $sqlmain12 = "SELECT * FROM appointment INNER JOIN patient ON patient.pid=appointment.pid INNER JOIN schedule ON schedule.scheduleid=appointment.scheduleid WHERE schedule.scheduleid=$id; ";
                        $result12 = $database->query($sqlmain12);
                        echo '
                    <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
                        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-[900px] max-h-[90vh] overflow-y-auto">
                            <div class="flex justify-end">
                                <a href="schedule.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                            </div>
                            <div class="mt-4">
                                <h2 class="text-2xl font-semibold text-gray-800 text-center mb-6">Doctor Details</h2>
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"><strong>Schedule Title</strong></label>
                                        <p class="mt-1 text-gray-900">' . $title . '</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"><strong>Doctor for this Appointment</strong></label>
                                        <p class="mt-1 text-gray-900"> ' . $docname . '</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"><strong>Appointment Date</strong></label>
                                        <p class="mt-1 text-gray-900">' . date("D, M j, Y", strtotime($scheduledate)) . '</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"><strong>Appointment Time</strong></label>
                                        <p class="mt-1 text-gray-900">' . date("h:i A", strtotime($scheduletime)) . '</p>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700"><strong>Patient Registered for this Appointment (' . $result->num_rows . "/" . $nop . ')</strong></label>
                                    </div>   
                                </div>
                                <br>
                                <div class="table-container overflow-x-auto fade-in-up-delay">
                                    <table id="doctors-table" class="min-w-[800px] w-full border-collapse fade-in-up-delay">
                                        <thead>
                                            <tr class="bg-gray-200">
                                                <th class="text-center py-2 px-4">Patient Name</th>
                                                <th class="text-center py-2 px-4">Appointment Number</th>
                                                <th class="text-center py-2 px-4">Phone Number</th>
                                            </tr>
                                        </thead>
                                        <tbody>';
                        $result = $database->query($sqlmain12);
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
                                $apponum = $row["apponum"];
                                $pid = $row["pid"];
                                $pname = $row["pname"];
                                $ptel = $row["ptel"];
                                echo '
                                        <tr>
                                            <td class="py-2 px-4">' . substr($pname, 0, 25) . '</td>
                                            <td class="text-center py-2 px-4">' . $apponum . '</td>
                                            <td class="py-2 px-4">' . substr($ptel, 0, 25) . '</td>
                                        </tr>';
                            }
                        }
                        echo ' </tbody>
                                    </table>
                                </div>
                                <div class="flex justify-center mt-6">
                                    <a href="schedule.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">Close</a>
                                </div>         
                            </div>
                        </div>
                    </div>';
                    }
                }
                ?>
         </div>
     </div>

     <script src="../js/date-time.js"></script>
     <script src="../js/active-link.js"></script>
 </body>

 </html>