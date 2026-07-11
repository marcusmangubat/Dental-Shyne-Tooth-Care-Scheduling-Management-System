 <?php
    session_start();
    $_SESSION['user'] = "";
    $_SESSION['usertype'] = "";

    // Set new timezone
    date_default_timezone_set('Asia/Manila');
    $date = date('Y-m-d');
    $_SESSION["date"] = $date;

    include 'config.php';

    if ($_POST) {
        $email = $_POST['useremail'];
        $password = $_POST['userpassword'];

        $error = '<label for="promter" class="form-label"></label>';

        // Use prepared statement for email verification
        $stmt = $database->prepare("SELECT * FROM webuser WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $utype = $result->fetch_assoc()['usertype'];
            if ($utype == 'p') {
                // Check patient credentials
                $stmt = $database->prepare("SELECT * FROM patient WHERE pemail = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $checker = $stmt->get_result();
                if ($checker->num_rows == 1) {
                    $row = $checker->fetch_assoc();
                    if (password_verify($password, $row['ppassword'])) {
                        // Patient dashboard
                        $_SESSION['user'] = $email;
                        $_SESSION['usertype'] = 'p';
                        header('location: patient/index.php');
                        exit();
                    } else {
                        $error = '<label for="promter" class="form-label" style="color: rgb(255, 62, 62); text-align:center;">Wrong Credentials: Invalid email or password</label>';
                    }
                }
            } else if ($utype == 'a') {
                // Check admin credentials
                $stmt = $database->prepare("SELECT * FROM admin WHERE aemail = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $checker = $stmt->get_result();
                if ($checker->num_rows == 1) {
                    $row = $checker->fetch_assoc();
                    if (password_verify($password, $row['apassword'])) {
                        // Admin dashboard
                        $_SESSION['user'] = $email;
                        $_SESSION['usertype'] = 'a';
                        header('location: admin/index.php');
                        exit();
                    } else {
                        $error = '<label for="promter" class="form-label" style="color: rgb(255, 62, 62); text-align:center;">Wrong Credentials: Invalid email or password</label>';
                    }
                }
            } else if ($utype == 'd') {
                // Check doctor credentials
                $stmt = $database->prepare("SELECT * FROM doctor WHERE docemail = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $checker = $stmt->get_result();
                if ($checker->num_rows == 1) {
                    $row = $checker->fetch_assoc();
                    if (password_verify($password, $row['docpassword'])) {
                        // Doctor dashboard
                        $_SESSION['user'] = $email;
                        $_SESSION['usertype'] = 'd';
                        header('location: doctor/index.php');
                        exit();
                    } else {
                        $error = '<label for="promter" class="form-label" style="color: rgb(255, 62, 62);">Wrong Credentials: Invalid email or password</label>';
                    }
                }
            } else if ($utype == 'f') {
                // Check frontdesk credentials
                $stmt = $database->prepare("SELECT * FROM frontdesk WHERE fdemail = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $checker = $stmt->get_result();
                if ($checker->num_rows == 1) {
                    $row = $checker->fetch_assoc();
                    if (password_verify($password, $row['fdpassword'])) {
                        // Frontdesk dashboard
                        $_SESSION['user'] = $email;
                        $_SESSION['usertype'] = 'f';
                        header('location: tesingfrontdesk/index.php');
                        exit();
                    } else {
                        $error = '<label for="promter" class="form-label" style="color: rgb(255, 62, 62);">Wrong Credentials: Invalid email or password</label>';
                    }
                }
            }
            $stmt->close();
        } else {
            $error = '<label for="promter" class="form-label" style="color: rgb(255, 62, 62);">We can\'t find an account for this email</label>';
        }
    } else {
        $error = '<label for="promter" class="form-label">  </label>';
    }
    ?>

 <!DOCTYPE html>
 <html lang="en">

 <head>
     <meta charset="UTF-8">
     <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <title>Log in</title>
     <link rel="icon" type="image/png" href="./photo/logo_cropped.png">
     <script src="https://cdn.tailwindcss.com"></script>
     <script src="./css/tailwind.js"></script>
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
     <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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

     <div class="container bg-white p-8 rounded-lg shadow-lg max-w-md w-full fade-in-up hover:shadow-xl transition-shadow duration-300">
         <img src="photo/form-logo.png" alt="Login Image" class="mx-auto h-16 mb-6">
         <hr class="border-gray-300 mb-6">
         <div class="text-center fade-in-up-delay">
             <h1 class="text-2xl font-bold text-gray-800 mb-2">Welcome!</h1>
             <p class="text-gray-600">Login with your details to continue</p>
         </div>

         <form action="" method="POST" class="mt-6 space-y-4 fade-in-up-delay">
             <div class="transition-transform duration-200 hover:scale-105">
                 <label for="useremail" class="block text-sm font-medium text-gray-700">Email:</label>
                 <input type="email" name="useremail" id="useremail" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500  transition-all duration-200" placeholder="Email Address" required>
             </div>

             <div class="transform transition-transform duration-200 hover:scale-105">
                 <label for="userpassword" class="block text-sm font-medium text-gray-700">Password:</label>
                 <input type="password" name="userpassword" id="userpassword" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500  transition-all duration-200" placeholder="Password" required>
                 <span id="togglePassword"
                     class="absolute right-3 top-8 text-gray-500 cursor-pointer">
                     <i class="fa-solid fa-eye-slash"></i>
                 </span>
             </div>
             <script>
                 const togglePassword = document.getElementById('togglePassword');
                 const password = document.getElementById('userpassword');

                 togglePassword.addEventListener('click', () => {
                     const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                     password.setAttribute('type', type);

                     // Toggle eye icon
                     togglePassword.innerHTML = type === 'password' ?
                         '<i class="fa-solid fa-eye-slash"></i>' :
                         '<i class="fa-solid fa-eye"></i>';
                 });
             </script>

             <p class="text-red-500 text-sm text-center transition-opacity duration-300"><?php echo $error ?></p>

             <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition-colors transition-all duration-200">Login</button>
         </form>

         <div class="mt-6 text-center space-y-2 fade-in-up-delay">
             <hr class="border-gray-300">
             <p class="text-sm text-gray-500">
                 Forgot password? <a href="reset-password.php" class="text-blue-600 hover:underline transition-colors duration-200">Reset</a>
             </p>
             <p class="text-sm text-gray-500">
                 Don't have an account? <a href="signup.php" class="text-blue-600 hover:underline transition-colors duration-200">Sign Up</a>
             </p>
         </div>
     </div>
 </body>

 </html>