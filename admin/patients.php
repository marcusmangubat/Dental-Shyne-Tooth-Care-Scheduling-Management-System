    <?php
    session_start();
    if (isset($_SESSION["user"])) {
        if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'a') {
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
        <title>Patients</title>
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

            .table-container {
                max-width: 100%;
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
        <div class="cotainer">
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
                        <a href="schedule.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200">
                            <i class="fas fa-clock"></i> Schedule
                        </a>
                    </li>
                    <!-- Accounts Dropdown -->
                    <li class="relative">
                        <button id="accounts-toggle" class="flex items-center gap-2 px-3 py-2 rounded-lg w-full text-blue-600 hover:bg-gray-200">
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
                                <a href="patients.php" class="flex items-center gap-2 px-3 py-2 rounded-lg hover:bg-gray-200 active text-blue-600 font-semibold">
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

            <div class="main-content flex-1 p-6">
                <div class="header">
                    <h1 class="text-2xl font-bold text-gray-900">Patients</h1>
                    <form action="" method="POST">
                        <div class="search-bar"
                            style="display: flex; align-items: center; background: #fff; border: 1px solid #ccc; border-radius: 8px; padding: 5px; width: 300px; margin-right: 20px;">
                            <input type="search" name="search" list="patients" placeholder="Search patients..."
                                class="search-input" style="border: none; outline: none; padding: 5px; flex: 1;">
                            <?php
                            echo '<datalist id="patients"';
                            $list11 = $database->query("SELECT pname, pemail FROM patient");

                            for ($y = 0; $y < $list11->num_rows; $y++) {
                                $row00 = $list11->fetch_assoc();
                                $d = $row00["pname"];
                                $c = $row00["pemail"];
                                echo "<option value= '$d'></br>";
                                echo "<option value= '$c'></br>";
                            };

                            echo '</datalist>';
                            ?>
                            <button type="submit" value="search" class="search-btn"
                                style="background: none; border: none; cursor: pointer;">
                                <img src="../icons/search.png" alt="Search Icon" style="width: 16px; height: 16px;">
                            </button>
                        </div>
                    </form>
                    <?php
                    if ($_POST) {
                        $keyword = $_POST["search"];
                        $sqlmain = "SELECT * FROM  patient WHERE pemail= '$keyword' OR pname = '$keyword' OR pname LIKE '$keyword%' OR pname LIKE '%$keyword' OR pname LIKE '%$keyword%'";
                    } else {
                        $sqlmain = "SELECT * FROM patient ORDER BY pid DESC";
                    }

                    ?>
                    <div class="user"></div>
                    <div class="datetime" id="datetime"></div>
                </div>

                <div class="content-header">
                    <div>
                        <p>All Patients <?php echo $list11->num_rows; ?></p>
                    </div>

                </div>

                <div class="table-container overflow-x-auto fade-in-up-delay">
                    <table id="doctors-table" class="min-w-[800px] w-full border-collapse fade-in-up-delay">
                        <thead>
                            <tr class="bg-gray-200">
                                <th class="text-center py-2 px-4">Name</th>
                                <th class="text-center py-2 px-4">Phone</th>
                                <th class="text-center py-2 px-4">Email</th>
                                <th class="text-center py-2 px-4">Date of Birth</th>
                                <th style="text-align:center;">Events</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = $database->query($sqlmain);
                            if ($result->num_rows == 0) {
                                echo '
                             <tr>
                            <td colspan="7" style="text-align:center;">
                                <img src="../photo/nodata.png" alt="No data" style="width:25%; display:block; margin:0 auto;" class="nodata-img">
                                <br>
                                <p class="heading-main12 nodata-img"
                                    style="font-size: 20px; color: rgb(49, 49, 49); font-family: Arial, sans-serif; font-weight: bold; text-transform: uppercase; letter-spacing: 1px; line-height: 1.5; text-align:center;">
                                    We couldnt find anything related
                                </p>
                            </td>
                        </tr>
                            
                            ';
                            } else {
                                for ($x = 0; $x < $result->num_rows; $x++) {
                                    $row = $result->fetch_assoc();
                                    $pid = $row["pid"];
                                    $name = $row["pname"];
                                    $email = $row["pemail"];
                                    $dob = $row["pdob"];
                                    $tel = $row["ptel"];
                                    echo '<tr class="hover:bg-gray-50">
                                <td class=" py-2 px-4">' . substr($name, 0, 35) . '</td>
                                <td class=" py-2 px-4">' . substr($tel, 0, 10) . '</td>
                                <td class=" py-2 px-4">' . substr($email, 0, 30) . '</td>
                                <td class=" py-2 px-4">' . date("F d, Y", strtotime($dob)) . '</td>
                                 <td class="text-center py-2 px-4">
                                <div class="action-buttons action-buttons flex justify-center space-x-2">
                                    <a href="?action=view&id=' . $pid . '">
                                       <button class="btn btn-view flex items-center px-3 py-1 rounded bg-blue-50 hover:bg-blue-100 transition-colors" title="View">
        <i class="fa-solid fa-eye" style="font-size: 16px; color: #3b82f6;"></i>
    </button>
                                    </a>
                                </div>
                            </td>        
                            </tr>   
                                ';
                                }
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php
            if ($_GET) {
                $id = $_GET["id"];
                $action = $_GET["action"];
                $sqlmain = "SELECT * FROM patient WHERE pid='$id'";
                $result = $database->query($sqlmain);
                $row = $result->fetch_assoc();
                $name = $row["pname"];
                $email = $row["pemail"];
                $dob = $row["pdob"];
                $tele = $row["ptel"];
                $address = $row["paddress"];
                echo '
                <div id="popup1" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 fade-in-up-delay">
                    <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                        <div class="flex justify-end">
                            <a href="patients.php" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</a>
                        </div>
                        <div class="mt-4">
                            <h2 class="text-2xl font-semibold text-gray-800 text-center mb-6">Patient Details</h2>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><strong>Name</strong></label>
                                    <p class="mt-1 text-gray-900">' . $name . '</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><strong>Email</strong></label>
                                    <p class="mt-1 text-gray-900">' . $email . '</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><strong>Phone Number</strong></label>
                                    <p class="mt-1 text-gray-900">' . $tele . '</p>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700"><strong>Address</strong></label>
                                    <p class="mt-1 text-gray-900">' . $address . '</p>
                                </div>
                                  <div>
                                    <label class="block text-sm font-medium text-gray-700"><strong>Date of Birth</strong></label>
                                    <p class="mt-1 text-gray-900">' . date("F d, Y", strtotime($dob)) . '</p>
                                </div>
                            </div>
                            <div class="flex justify-center mt-6">
                                <a href="patients.php" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500">Close</a>
                            </div>         
                        </div>
                    </div>
                </div>';
            }

            ?>

        </div>


        <script src="../js/date-time.js"></script>
        <script src="../js/active-link.js"></script>
    </body>

    </html>