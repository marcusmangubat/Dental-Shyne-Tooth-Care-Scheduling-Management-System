<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Front Desk - Dental Clinic</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="../photo/logo_cropped.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
                    <span class="text-gray-700 hidden sm:block" id="userName">Welcome, Front Desk</span>
                    <button onclick="logout()" class="text-red-600 hover:text-red-800">
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
                            <!-- Patients will be loaded here -->
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
                    <button onclick="confirmBooking()" id="confirmBookingBtn" class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 disabled:cursor-not-allowed" disabled>
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
                            <label class="block text-sm font-medium text-gray-700 mb-1">Password *</label>
                            <input type="password" name="ppassword" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Phone *</label>
                            <input type="tel" name="ptel" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Date of Birth *</label>
                            <input type="date" name="pdob" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Address *</label>
                        <textarea name="paddress" required rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
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

    <script>
        // Global variables
        let selectedPatientId = null;
        let selectedScheduleId = null;

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
            // Hide all tab contents
            document.querySelectorAll('.tab-content').forEach(content => {
                content.classList.add('hidden');
            });

            // Remove active class from all tabs
            document.querySelectorAll('.tab-button').forEach(button => {
                button.classList.remove('active', 'border-blue-600', 'text-blue-600');
                button.classList.add('border-transparent', 'text-gray-500');
            });

            // Show selected tab content
            document.getElementById('content-' + tabName).classList.remove('hidden');

            // Add active class to selected tab
            const activeTab = document.getElementById('tab-' + tabName);
            activeTab.classList.add('active', 'border-blue-600', 'text-blue-600');
            activeTab.classList.remove('border-transparent', 'text-gray-500');

            // Load data for the tab
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
                                <button onclick="cancelAppointment(${apt.appoid})" class="bg-red-100 text-red-600 px-4 py-2 rounded-lg hover:bg-red-200 whitespace-nowrap">
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
            if (!confirm('Are you sure you want to cancel this appointment?')) return;

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
                    alert('Appointment cancelled successfully');
                    loadAppointments();
                    loadDashboardStats();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error cancelling appointment:', error);
                alert('Error cancelling appointment');
            }
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
                                <button onclick="viewPatient(${patient.pid})" class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fas fa-eye"></i>
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
            alert('Patient details view - ID: ' + pid);
            // Implement patient details modal if needed
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
                    alert('Patient added successfully');
                    closeAddPatientModal();
                    loadPatients();
                    loadDashboardStats();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error adding patient:', error);
                alert('Error adding patient');
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
                                <div>
                                    <p class="font-medium text-gray-800">${schedule.title}</p>
                                    <p class="text-sm text-gray-600">Dr. ${schedule.docname}</p>
                                    <p class="text-xs text-gray-500">${schedule.scheduledate} at ${schedule.scheduletime}</p>
                                </div>
                                <span class="px-2 py-1 rounded text-xs font-semibold ${schedule.available > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'}">
                                    ${schedule.available}/${schedule.nop}
                                </span>
                            </div>
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
            selectedScheduleId = scheduleid;
            loadSchedulesForBooking();
            checkBookingReady();
        }

        // Check if booking is ready
        function checkBookingReady() {
            const btn = document.getElementById('confirmBookingBtn');
            if (selectedPatientId && selectedScheduleId) {
                btn.disabled = false;
            } else {
                btn.disabled = true;
            }
        }

        // Confirm booking
        async function confirmBooking() {
            if (!selectedPatientId || !selectedScheduleId) {
                alert('Please select both patient and schedule');
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
                    alert(`Appointment booked successfully! Appointment Number: ${result.apponum}`);
                    selectedPatientId = null;
                    selectedScheduleId = null;
                    document.getElementById('selectedPatientInfo').classList.add('hidden');
                    document.getElementById('bookingPatientSearch').value = '';
                    loadSchedulesForBooking();
                    loadDashboardStats();
                    checkBookingReady();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                console.error('Error booking appointment:', error);
                alert('Error booking appointment');
            }
        }

        // Logout
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = 'login.php';
            }
        }
    </script>
</body>

</html>