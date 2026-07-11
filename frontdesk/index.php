<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
// Check if user is logged in and has the correct user type
if (!isset($_SESSION['user']) || empty($_SESSION['user']) || !isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'f') {
    header("Location: ../login.php");
    exit();
}
$useremail = $_SESSION['user'];
include '../config.php';
try {
    // Prepare and execute the database query
    $stmt = $database->prepare("SELECT * FROM frontdesk WHERE fdemail = ?");
    if (!$stmt) {
        die("Prepare failed: " . $database->error);
    }

    $stmt->bind_param("s", $useremail);
    $stmt->execute();
    $userrow = $stmt->get_result();

    // Check if a record was found
    if ($userrow->num_rows === 0) {
        header("Location: ../login.php");
        exit();
    }

    // Fetch user data
    $userfetch = $userrow->fetch_assoc();
    $userid = $userfetch['fdid'];
    $username = $userfetch['fdname'];

    $stmt->close();
} catch (Exception $e) {
    // Handle database errors (log securely in production)
    die("Database error: " . $e->getMessage());
}

// Close the connection (optional, depending on your app's needs)
$database->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Front Desk - Dental Shyne</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="../photo/logo_cropped.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .fade-in-up-delay {
            animation: fadeInUp 0.3s ease-out;
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
    </style>
</head>


<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <i class="fas fa-tooth text-blue-600 text-2xl mr-3"></i>
                    <span class="text-xl font-bold text-gray-800">Dental Shyne</span>
                    <span class="ml-4 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">Front Desk</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-gray-700 hidden sm:block" id="userName">Welcome, <?php echo explode(" ", trim($username))[0]; ?></span>
                    <button onclick="showConfirmPopup('confirmPopup', 'Are you sure?', 'You want to logout?', () => window.location.href='../logout.php')" class="text-red-600 hover:text-red-800">
                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Dashboard Stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Patients</p>
                        <p class="text-3xl font-bold text-gray-800" id="totalPatients">0</p>
                    </div>
                    <div class="bg-blue-100 rounded-full p-3">
                        <i class="fas fa-users text-blue-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Today's Appointments</p>
                        <p class="text-3xl font-bold text-gray-800" id="todayAppointments">0</p>
                    </div>
                    <div class="bg-green-100 rounded-full p-3">
                        <i class="fas fa-calendar-check text-green-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Total Doctors</p>
                        <p class="text-3xl font-bold text-gray-800" id="totalDoctors">0</p>
                    </div>
                    <div class="bg-purple-100 rounded-full p-3">
                        <i class="fas fa-user-md text-purple-600 text-2xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-500 text-sm">Pending Appointments</p>
                        <p class="text-3xl font-bold text-gray-800" id="pendingAppointments">0</p>
                    </div>
                    <div class="bg-yellow-100 rounded-full p-3">
                        <i class="fas fa-clock text-yellow-600 text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex flex-wrap -mb-px">
                    <button onclick="switchTab('appointments')" id="tab-appointments" class="tab-button active px-4 sm:px-6 py-4 text-sm font-medium border-b-2 border-blue-600 text-blue-600">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        <span class="hidden sm:inline">Today's </span>Appointments
                    </button>
                    <button onclick="switchTab('patients')" id="tab-patients" class="tab-button px-4 sm:px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        <i class="fas fa-users mr-2"></i>Patients
                    </button>
                    <button onclick="switchTab('booking')" id="tab-booking" class="tab-button px-4 sm:px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        <i class="fas fa-plus-circle mr-2"></i>
                        <span class="hidden sm:inline">Book </span>Appointment
                    </button>
                    <button onclick="switchTab('schedules')" id="tab-schedules" class="tab-button px-4 sm:px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300">
                        <i class="fas fa-clock mr-2"></i>Schedules
                    </button>
                </nav>
            </div>
        </div>

        <!-- Tab Contents -->

        <!-- Today's Appointments Tab -->
        <div id="content-appointments" class="tab-content">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <h2 class="text-2xl font-bold text-gray-800">Today's Appointments</h2>
                    <input type="date" id="appointmentDate" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="loadAppointments()">
                </div>
                <div id="appointmentsList" class="space-y-4">
                    <!-- Appointments will be loaded here -->
                </div>
            </div>
        </div>

        <!-- Patients Tab -->
        <div id="content-patients" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <h2 class="text-2xl font-bold text-gray-800">Patient Management</h2>
                    <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                        <input type="text" id="patientSearch" placeholder="Search patients..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent flex-1 sm:flex-none" onkeyup="searchPatients()">
                        <button onclick="showAddPatientModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 whitespace-nowrap">
                            <i class="fas fa-plus mr-2"></i>Add Patient
                        </button>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">DOB</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="patientsTable" class="bg-white divide-y divide-gray-200">
                            <!-- Patients will be loaded here by JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Book Appointment Tab -->
        <div id="content-booking" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">Book New Appointment</h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Patient Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Patient</label>
                        <input type="text" id="bookingPatientSearch" placeholder="Search patient by name or phone..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent mb-2" onkeyup="searchPatientsForBooking()">
                        <div id="patientSearchResults" class="border border-gray-200 rounded-lg max-h-64 overflow-y-auto">
                            <!-- Patient search results will appear here -->
                        </div>
                        <div id="selectedPatientInfo" class="mt-4 p-4 bg-blue-50 rounded-lg hidden">
                            <p class="font-medium text-gray-800">Selected Patient:</p>
                            <p class="text-gray-600" id="selectedPatientName"></p>
                        </div>
                    </div>

                    <!-- Schedule Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Schedule</label>
                        <input type="date" id="bookingScheduleDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent mb-2" onchange="loadSchedulesForBooking()">
                        <div id="schedulesList" class="space-y-2 max-h-96 overflow-y-auto">
                            <!-- Schedules will be loaded here -->
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button onclick="showConfirmPopup('bookingPopup', 'Confirm Booking', 'Are you sure you want to book this appointment?', confirmBookingAction)" id="confirmBookingBtn" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>
                        <i class="fas fa-check mr-2"></i>Confirm Booking
                    </button>
                </div>
            </div>
        </div>

        <!-- Schedules Tab -->
        <div id="content-schedules" class="tab-content hidden">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <h2 class="text-2xl font-bold text-gray-800">Doctor Schedules</h2>
                    <input type="date" id="scheduleDate" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" onchange="loadSchedules()">
                </div>
                <div id="schedulesDisplay" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Schedules will be loaded here -->
                </div>
            </div>
        </div>
    </div>

    <!-- Add Patient Modal -->
    <div id="addPatientModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Add New Patient</h3>
                    <button onclick="closeAddPatientModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                <form id="addPatientForm" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                            <input type="text" name="pname" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" name="pemail" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                            <input type="tel" name="ptel" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth *</label>
                            <input type="date" name="pdob" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gender *</label>
                            <select name="pgender" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address *</label>
                        <textarea name="paddress" required rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>

                    <!-- Password Field with Toggle -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                        <div class="relative">
                            <input type="password" id="ppassword" name="ppassword" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-10">
                            <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500" onclick="togglePasswordVisibility('ppassword', this)">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    <!-- Confirm Password Field with Toggle -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password *</label>
                        <div class="relative">
                            <input type="password" id="pconfirm_password" name="pconfirm_password" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent pr-10">
                            <button type="button" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500" onclick="togglePasswordVisibility('pconfirm_password', this)">
                                <i class="fas fa-eye-slash"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button" onclick="closeAddPatientModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-save mr-2"></i>Save Patient
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Patient Modal -->
    <div id="viewPatientModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Patient Details</h3>
                    <button onclick="closeViewPatientModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                <div class="space-y-4 text-gray-700">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Full Name:</label>
                        <p id="viewPName" class="text-gray-900 font-semibold"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Email:</label>
                        <p id="viewPEmail"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Phone:</label>
                        <p id="viewPTel"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Date of Birth:</label>
                        <p id="viewPDob"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Gender:</label>
                        <p id="viewPGender"></p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Address:</label>
                        <p id="viewPAddress"></p>
                    </div>
                </div>
                <div class="flex justify-end pt-6">
                    <button onclick="closeViewPatientModal()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Patient Modal -->
    <div id="editPatientModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-gray-800">Edit Patient Details</h3>
                    <button onclick="closeEditPatientModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-2xl"></i>
                    </button>
                </div>
                <form id="editPatientForm" class="space-y-4">
                    <input type="hidden" name="pid" id="editPid">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                            <input type="text" name="pname" id="editPName" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                            <input type="email" name="pemail" id="editPEmail" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                            <input type="tel" name="ptel" id="editPTel" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth *</label>
                            <input type="date" name="pdob" id="editPDob" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Gender *</label>
                            <select name="pgender" id="editPGender" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Select Gender</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address *</label>
                        <textarea name="paddress" id="editPAddress" required rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button" onclick="closeEditPatientModal()" class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            <i class="fas fa-save mr-2"></i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <script>
        // Global variable to store current patient ID for edit/view
        let currentPatientId = null;

        // ... (existing JS functions) ...

        // --- View Patient Functions ---
        async function showViewPatientModal(pid) {
            currentPatientId = pid;
            try {
                const response = await fetch(`get-patient-details.php?pid=${pid}`);
                const result = await response.json();

                if (result.success && result.data) {
                    const patient = result.data;
                    document.getElementById('viewPName').textContent = patient.pname;
                    document.getElementById('viewPEmail').textContent = patient.pemail;
                    document.getElementById('viewPTel').textContent = patient.ptel;
                    document.getElementById('viewPDob').textContent = patient.pdob;
                    document.getElementById('viewPGender').textContent = patient.pgender || 'N/A'; // Handle if gender is null
                    document.getElementById('viewPAddress').textContent = patient.paddress;

                    document.getElementById('viewPatientModal').classList.remove('hidden');
                    document.getElementById('viewPatientModal').classList.add('flex');
                } else {
                    showMessagePopup('messagePopup', 'Error', 'Could not load patient details: ' + result.message);
                }
            } catch (error) {
                console.error('Error loading patient details:', error);
                showMessagePopup('messagePopup', 'Error', 'Failed to load patient details.');
            }
        }

        function closeViewPatientModal() {
            document.getElementById('viewPatientModal').classList.add('hidden');
            document.getElementById('viewPatientModal').classList.remove('flex');
            currentPatientId = null;
        }

        // --- Edit Patient Functions ---
        async function showEditPatientModal(pid) {
            currentPatientId = pid;
            try {
                const response = await fetch(`get-patient-details.php?pid=${pid}`);
                const result = await response.json();

                if (result.success && result.data) {
                    const patient = result.data;
                    document.getElementById('editPid').value = patient.pid;
                    document.getElementById('editPName').value = patient.pname;
                    document.getElementById('editPEmail').value = patient.pemail;
                    document.getElementById('editPTel').value = patient.ptel;
                    document.getElementById('editPDob').value = patient.pdob;
                    document.getElementById('editPGender').value = patient.pgender; // Set selected option
                    document.getElementById('editPAddress').value = patient.paddress;

                    document.getElementById('editPatientModal').classList.remove('hidden');
                    document.getElementById('editPatientModal').classList.add('flex');
                } else {
                    showMessagePopup('messagePopup', 'Error', 'Could not load patient details for editing: ' + result.message);
                }
            } catch (error) {
                console.error('Error loading patient details for editing:', error);
                showMessagePopup('messagePopup', 'Error', 'Failed to load patient details for editing.');
            }
        }

        function closeEditPatientModal() {
            document.getElementById('editPatientModal').classList.add('hidden');
            document.getElementById('editPatientModal').classList.remove('flex');
            document.getElementById('editPatientForm').reset();
            currentPatientId = null;
        }

        // Handle Edit Patient Form Submission
        document.getElementById('editPatientForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('update-patient.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showMessagePopup('messagePopup', 'Success', 'Patient updated successfully');
                    closeEditPatientModal();
                    loadPatients(); // Reload patients list to reflect changes
                } else {
                    showMessagePopup('messagePopup', 'Error', 'Error updating patient: ' + result.message);
                }
            } catch (error) {
                console.error('Error updating patient:', error);
                showMessagePopup('messagePopup', 'Error', 'Failed to update patient.');
            }
        });

        // Update the loadPatients function's patient mapping to include new buttons:
        async function loadPatients(search = '') {
            try {
                const response = await fetch(`get-patients.php?search=${encodeURIComponent(search)}`);
                const result = await response.json();

                const tbody = document.getElementById('patientsTable');

                if (result.success && result.data.length > 0) {
                    tbody.innerHTML = result.data.map(patient => `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">${patient.pname}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                                <div class="text-sm text-gray-500">${patient.pemail}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">${patient.ptel}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                                <div class="text-sm text-gray-500">${patient.pdob}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button onclick="showViewPatientModal(${patient.pid})" class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button onclick="showEditPatientModal(${patient.pid})" class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No patients found</td></tr>';
                }
            } catch (error) {
                console.error('Error loading patients:', error);
            }
        }
    </script>
    <script>
        function togglePasswordVisibility(inputId, btn) {
            var input = document.getElementById(inputId);
            var icon = btn.querySelector("i");
            if (input.type === "password") {
                input.type = "text";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            } else {
                input.type = "password";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            }
        }
    </script>

    <!-- Confirmation Popups -->
    <div id="confirmPopup" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 fade-in-up-delay">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <div class="flex justify-end">
                <button onclick="hidePopup('confirmPopup')" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</button>
            </div>
            <div class="mt-4 text-center">
                <h2 id="confirmPopupTitle" class="text-2xl font-semibold text-gray-800 mb-6">Are you sure?</h2>
                <p id="confirmPopupMessage" class="text-gray-700 mb-6"></p>
                <div class="flex justify-center space-x-4">
                    <button id="confirmPopupYes" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none">Yes</button>
                    <button onclick="hidePopup('confirmPopup')" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none">No</button>
                </div>
            </div>
        </div>
    </div>

    <div id="messagePopup" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 fade-in-up-delay">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md">
            <div class="flex justify-end">
                <button onclick="hidePopup('messagePopup')" class="text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</button>
            </div>
            <div class="mt-4 text-center">
                <h2 id="messagePopupTitle" class="text-2xl font-semibold text-gray-800 mb-6"></h2>
                <p id="messagePopupMessage" class="text-gray-700 mb-6"></p>
                <div class="flex justify-center space-x-4">
                    <button onclick="hidePopup('messagePopup')" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none">OK</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Global variables
        let selectedPatientId = null;
        let selectedScheduleId = null;
        let currentAppoid = null;

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            loadDashboardStats();
            document.getElementById('appointmentDate').valueAsDate = new Date();
            document.getElementById('bookingScheduleDate').valueAsDate = new Date();
            document.getElementById('scheduleDate').valueAsDate = new Date();
            loadAppointments();
        });

        // Tab switching
        function switchTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active', 'border-blue-600', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500');
            });
            document.getElementById('content-' + tabName).classList.remove('hidden');
            const activeTab = document.getElementById('tab-' + tabName);
            activeTab.classList.add('active', 'border-blue-600', 'text-blue-600');
            activeTab.classList.remove('border-transparent', 'text-gray-500');

            if (tabName === 'patients') {
                loadPatients();
            } else if (tabName === 'schedules') {
                loadSchedules();
            } else if (tabName === 'booking') {
                loadSchedulesForBooking();
            }
        }

        // Load dashboard stats
        async function loadDashboardStats() {
            try {
                const response = await fetch('get-dashboard-stats.php');
                const result = await response.json();

                if (result.success) {
                    document.getElementById('totalPatients').textContent = result.data.totalPatients;
                    document.getElementById('todayAppointments').textContent = result.data.todayAppointments;
                    document.getElementById('totalDoctors').textContent = result.data.totalDoctors;
                    document.getElementById('pendingAppointments').textContent = result.data.pendingAppointments;
                }
            } catch (error) {
                console.error('Error loading dashboard stats:', error);
            }
        }

        // Load appointments
        async function loadAppointments() {
            const date = document.getElementById('appointmentDate').value;
            try {
                const response = await fetch(`get-appointments.php?date=${date}`);
                const result = await response.json();

                const container = document.getElementById('appointmentsList');

                if (result.success && result.data.length > 0) {
                    container.innerHTML = result.data.map(apt => `
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                            #${apt.apponum}
                                        </span>
                                        <span class="text-gray-600 text-sm">${apt.scheduletime}</span>
                                    </div>
                                    <h4 class="font-semibold text-gray-800 text-lg">${apt.pname}</h4>
                                    <p class="text-gray-600 text-sm"><i class="fas fa-phone mr-2"></i>${apt.ptel}</p>
                                    <p class="text-gray-600 text-sm"><i class="fas fa-envelope mr-2"></i>${apt.pemail}</p>
                                    <div class="mt-2">
                                        <span class="text-gray-700 font-medium">Dr. ${apt.docname}</span>
                                        <span class="text-gray-500 text-sm"> - ${apt.specialty || 'General'}</span>
                                    </div>
                                </div>
                                <button onclick="showConfirmPopup('confirmPopup', 'Are you sure?', 'You want to cancel this appointment (${apt.pname.substring(0, 40)})', () => cancelAppointment(${apt.appoid}))" class="bg-red-100 text-red-600 px-4 py-2 rounded-lg hover:bg-red-200 whitespace-nowrap">
                                    <i class="fas fa-times mr-2"></i>Cancel
                                </button>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<p class="text-gray-500 text-center py-8">No appointments for this date</p>';
                }
            } catch (error) {
                console.error('Error loading appointments:', error);
            }
        }
        // Cancel appointment
        async function cancelAppointment(appoid) {
            try {
                const response = await fetch('delete-appointment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        appoid
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showMessagePopup('messagePopup', 'Success', 'Appointment cancelled successfully');
                    loadAppointments();
                    loadDashboardStats();
                } else {
                    showMessagePopup('messagePopup', 'Error', 'Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error cancelling appointment:', error);
                showMessagePopup('messagePopup', 'Error', 'Error cancelling appointment');
            }
            hidePopup('confirmPopup');
        }

        // Load patients
        async function loadPatients(search = '') {
            try {
                const response = await fetch(`get-patients.php?search=${encodeURIComponent(search)}`);
                const result = await response.json();

                const tbody = document.getElementById('patientsTable');

                if (result.success && result.data.length > 0) {
                    tbody.innerHTML = result.data.map(patient => `
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">${patient.pname}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap hidden md:table-cell">
                                <div class="text-sm text-gray-500">${patient.pemail}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-500">${patient.ptel}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap hidden lg:table-cell">
                                <div class="text-sm text-gray-500">${patient.pdob}</div>
                            </td>
                              <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <button onclick="showViewPatientModal(${patient.pid})" class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-eye"></i> View
                                </button>
                                <button onclick="showEditPatientModal(${patient.pid})" class="text-green-600 hover:text-green-900">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </td>
                        </tr>
                    `).join('');
                } else {
                    tbody.innerHTML = '<tr><td colspan="5" class="px-6 py-4 text-center text-gray-500">No patients found</td></tr>';
                }
            } catch (error) {
                console.error('Error loading patients:', error);
            }
        }
        // Search patients
        function searchPatients() {
            const search = document.getElementById('patientSearch').value;
            loadPatients(search);
        }
        // View patient details
        function viewPatient(pid) {
            showMessagePopup('messagePopup', 'Patient Details', 'Patient details view - ID: ' + pid);
        }
        // Show add patient modal
        function showAddPatientModal() {
            document.getElementById('addPatientModal').classList.remove('hidden');
            document.getElementById('addPatientModal').classList.add('flex');
        }
        // Close add patient modal
        function closeAddPatientModal() {
            document.getElementById('addPatientModal').classList.add('hidden');
            document.getElementById('addPatientModal').classList.remove('flex');
            document.getElementById('addPatientForm').reset();
        }
        // Add patient form submission
        document.getElementById('addPatientForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());

            try {
                const response = await fetch('add-patient.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showMessagePopup('messagePopup', 'Success', 'Patient added successfully');
                    closeAddPatientModal();
                    loadPatients();
                    loadDashboardStats();
                } else {
                    showMessagePopup('messagePopup', 'Error', 'Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error adding patient:', error);
                showMessagePopup('messagePopup', 'Error', 'Error adding patient');
            }
        });
        // Load schedules
        async function loadSchedules() {
            const date = document.getElementById('scheduleDate').value;
            try {
                const response = await fetch(`get-schedules.php?date=${date}`);
                const result = await response.json();

                const container = document.getElementById('schedulesDisplay');

                if (result.success && result.data.length > 0) {
                    container.innerHTML = result.data.map(schedule => `
                        <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="font-semibold text-gray-800 text-lg">${schedule.title}</h4>
                                    <p class="text-gray-600">Dr. ${schedule.docname}</p>
                                    <p class="text-sm text-gray-500">${schedule.specialty || 'General'}</p>
                                </div>
                                <span class="px-3 py-1 rounded-full text-sm font-semibold ${schedule.available > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                    ${schedule.available}/${schedule.nop} Available
                                </span>
                            </div>
                            <div class="flex items-center text-gray-600 text-sm">
                                <i class="fas fa-calendar mr-2"></i>
                                <span>${schedule.scheduledate}</span>
                                <i class="fas fa-clock ml-4 mr-2"></i>
                                <span>${schedule.scheduletime}</span>
                            </div>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<p class="text-gray-500 text-center py-8 col-span-2">No schedules available</p>';
                }
            } catch (error) {
                console.error('Error loading schedules:', error);
            }
        }
        // Search patients for booking
        async function searchPatientsForBooking() {
            const search = document.getElementById('bookingPatientSearch').value;

            if (search.length < 2) {
                document.getElementById('patientSearchResults').innerHTML = '<p class="p-4 text-gray-500 text-sm">Type at least 2 characters to search</p>';
                return;
            }

            try {
                const response = await fetch(`get-patients.php?search=${encodeURIComponent(search)}`);
                const result = await response.json();

                const container = document.getElementById('patientSearchResults');

                if (result.success && result.data.length > 0) {
                    container.innerHTML = result.data.map(patient => `
                        <div onclick="selectPatient(${patient.pid}, '${patient.pname}')" class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                            <p class="font-medium text-gray-800">${patient.pname}</p>
                            <p class="text-sm text-gray-600">${patient.ptel} - ${patient.pemail}</p>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<p class="p-4 text-gray-500 text-sm">No patients found</p>';
                }
            } catch (error) {
                console.error('Error searching patients:', error);
            }
        }
        // Select patient for booking
        function selectPatient(pid, pname) {
            selectedPatientId = pid;
            document.getElementById('selectedPatientInfo').classList.remove('hidden');
            document.getElementById('selectedPatientName').textContent = pname;
            document.getElementById('patientSearchResults').innerHTML = '';
            document.getElementById('bookingPatientSearch').value = '';
            checkBookingReady();
        }
        // Load schedules for booking
        async function loadSchedulesForBooking() {
            const date = document.getElementById('bookingScheduleDate').value;
            try {
                const response = await fetch(`get-schedules.php?date=${date}`);
                const result = await response.json();

                const container = document.getElementById('schedulesList');

                if (result.success && result.data.length > 0) {
                    container.innerHTML = result.data.map(schedule => `
                        <div onclick="selectSchedule(${schedule.scheduleid})" class="border border-gray-200 rounded-lg p-3 cursor-pointer hover:border-blue-500 hover:bg-blue-50 transition-all ${selectedScheduleId === schedule.scheduleid ? 'border-blue-500 bg-blue-50' : ''} ${schedule.available === 0 ? 'opacity-50 cursor-not-allowed' : ''}">
                            <div class="flex justify-between items-start">
                                <p class="font-medium text-gray-800">${schedule.title}</p>
                                <p class="text-sm text-gray-600">Dr. ${schedule.docname}</p>
                                <p class="text-xs text-gray-500">${schedule.scheduledate} at ${schedule.scheduletime}</p>
                            </div>
                            <span class="px-2 py-1 rounded text-xs font-semibold ${schedule.available > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                ${schedule.available}/${schedule.nop}
                            </span>
                        </div>
                    `).join('');
                } else {
                    container.innerHTML = '<p class="text-gray-500 text-sm py-4">No schedules available for this date</p>';
                }
            } catch (error) {
                console.error('Error loading schedules:', error);
            }
        }

        // Select schedule for booking
        function selectSchedule(scheduleid) {
            // Fetch the latest schedule data to verify availability
            const date = document.getElementById('bookingScheduleDate').value;
            fetch(`get-schedules.php?date=${date}`)
                .then(response => response.json())
                .then(result => {
                    const schedule = result.data.find(s => s.scheduleid === scheduleid);
                    if (schedule && schedule.available > 0) {
                        selectedScheduleId = scheduleid;
                        checkBookingReady();
                        loadSchedulesForBooking(); // Refresh UI to reflect selection
                    } else {
                        showMessagePopup('messagePopup', 'Error', 'This schedule is full or unavailable');
                    }
                })
                .catch(error => {
                    console.error('Error checking schedule availability:', error);
                    showMessagePopup('messagePopup', 'Error', 'Failed to verify schedule availability');
                });
        }
        // Check if booking is ready
        function checkBookingReady() {
            const btn = document.getElementById('confirmBookingBtn');
            if (selectedPatientId && selectedScheduleId) {
                // Verify schedule availability
                fetch(`get-schedules.php?scheduleid=${selectedScheduleId}`)
                    .then(response => response.json())
                    .then(result => {
                        if (result.success && result.data && result.data.available > 0) {
                            btn.disabled = false;
                        } else {
                            btn.disabled = true;
                            showMessagePopup('messagePopup', 'Warning', 'Selected schedule is no longer available');
                        }
                    })
                    .catch(error => {
                        console.error('Error verifying schedule:', error);
                        btn.disabled = true;
                    });
            } else {
                btn.disabled = true;
            }
        }
        // Confirm booking
        async function confirmBookingAction() {
            if (!selectedPatientId || !selectedScheduleId) {
                showMessagePopup('messagePopup', 'Error', 'Please select both patient and schedule');
                return;
            }
            try {
                const response = await fetch('book-appointment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        pid: selectedPatientId,
                        scheduleid: selectedScheduleId
                    })
                });
                const result = await response.json();
                if (result.success) {
                    showMessagePopup('messagePopup', 'Success', `Appointment booked successfully! Appointment Number: ${result.apponum}`);
                    selectedPatientId = null;
                    selectedScheduleId = null;
                    document.getElementById('selectedPatientInfo').classList.add('hidden');
                    document.getElementById('bookingPatientSearch').value = '';
                    loadSchedulesForBooking();
                    loadDashboardStats();
                    checkBookingReady();
                } else {
                    showMessagePopup('messagePopup', 'Error', result.message || 'Failed to book appointment');
                }
            } catch (error) {
                console.error('Error booking appointment:', error);
                showMessagePopup('messagePopup', 'Error', 'Failed to book appointment due to a network or server error');
            }
            hidePopup('confirmPopup');
        }
        // Show confirmation popup
        function showConfirmPopup(popupId, title, message, confirmAction) {
            const popup = document.getElementById(popupId);
            document.getElementById('confirmPopupTitle').textContent = title;
            document.getElementById('confirmPopupMessage').textContent = message;
            document.getElementById('confirmPopupYes').onclick = confirmAction;
            popup.classList.remove('hidden');
            popup.classList.add('flex');
        }
        // Show message popup
        function showMessagePopup(popupId, title, message) {
            const popup = document.getElementById(popupId);
            document.getElementById('messagePopupTitle').textContent = title;
            document.getElementById('messagePopupMessage').textContent = message;
            popup.classList.remove('hidden');
            popup.classList.add('flex');
        }
        // Hide popup
        function hidePopup(popupId) {
            const popup = document.getElementById(popupId);
            popup.classList.add('hidden');
            popup.classList.remove('flex');
        }
        // Logout
        function logout() {
            showConfirmPopup('confirmPopup', 'Are you sure?', 'You want to logout?', () => {
                window.location.href = '../logout.php';
            });
        }
    </script>
</body>

</html>