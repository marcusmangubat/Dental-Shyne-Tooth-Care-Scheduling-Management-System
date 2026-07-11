<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign up</title>
    <link rel="stylesheet" href="css/signup.css">
    <link rel="icon" type="image/png" href="./photo/logo_cropped.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
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

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <?php
    session_start();

    $_SESSION['user'] = "";
    $_SESSION['usertype'] = "";
    $_SESSION['personal'] = []; // Initialize personal session data

    // Set new timezone
    date_default_timezone_set('Asia/Manila');
    $date = date('Y-m-d');
    $_SESSION["date"] = $date;

    if ($_POST) {
        $_SESSION["personal"] = array(
            'fname' => $_POST['fname'],
            'lname' => $_POST['lname'],
            'address' => $_POST['address'],
            'dob' => $_POST['dob'],
            'gender' => $_POST['gender']
        );

        header("Location: create_account.php");
        exit();
    }
    ?>
    <div class="container bg-white p-8 rounded-lg shadow-lg max-w-md w-full fade-in-up hover:shadow-xl transition-shadow duration-300">
        <img src="photo/form-logo.png" alt="Logo" class="mx-auto h-16 mb-6">
        <hr class="border-gray-300 mb-6">
        <div class="text-center fade-in-up-delay">
            <h1 class="text-2xl font-bold text-gray-800 mb-2 fade-in-up-delay">Let's Get Started</h1>
            <p class="text-gray-600">Add Your Personal Details to Continue</p>
        </div>

        <form action="" method="POST" class="mt-6 space-y-4 fade-in-up-delay">
            <div class="transition-transform duration-200 hover:scale-105">
                <div class="flex space-x-4">
                    <div class="w-full">
                        <label class="block text-sm font-medium text-gray-700">First Name:</label>
                        <input type="text" name="fname" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" placeholder="First Name" required>
                    </div>
                    <div class="w-full">
                        <label class="block text-sm font-medium text-gray-700">Last Name:</label>
                        <input type="text" name="lname" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" placeholder="Last Name" required>
                    </div>
                </div>
            </div>

            <div class="transition-transform duration-200 hover:scale-105">
                <label class="block text-sm font-medium text-gray-700">Address:</label>
                <input type="text" name="address" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" placeholder="Address" required>
            </div>

            <div class="transition-transform duration-200">
                <label class="block text-sm font-medium text-gray-700">Gender:</label>
                <select name="gender" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" required>
                    <option value="" disabled selected>Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                </select>
            </div>

            <div class="transition-transform duration-200 hover:scale-105">
                <label class="block text-sm font-medium text-gray-700">Date of Birth:</label>
                <input type="date" name="dob" id="dob" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" required>
            </div>
            <script>
                const today = new Date().toISOString().split("T")[0];
                document.getElementById("dob").setAttribute("max", today);
            </script>

            <div class="flex space-x-4">
                <button type="reset" class="w-full bg-gray-200 text-gray-700 py-2 rounded-md hover:bg-gray-300 transition-colors transition-all duration-200">Reset</button>
                <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition-colors transition-all duration-200">Next</button>
            </div>
        </form>

        <div class="mt-6 text-center fade-in-up-delay">
            <p class="text-sm text-gray-500">
                Already have an account? <a href="login.php" class="text-blue-600 hover:underline">Login</a>
            </p>
        </div>
    </div>
</body>

</html>