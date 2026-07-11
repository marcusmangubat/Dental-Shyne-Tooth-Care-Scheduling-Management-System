<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/signup.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" type="image/png" href="./photo/logo_cropped.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
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
    include 'config.php';
    require 'phpmailer/src/PHPMailer.php'; // Adjust path to your PHPMailer installation
    require 'phpmailer/src/SMTP.php';
    require 'phpmailer/src/Exception.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    $_SESSION['user'] = "";
    $_SESSION['usertype'] = "";
    $error = "";
    $showAccountForm = true;
    $showCodeForm = false;

    // Set new timezone
    date_default_timezone_set('Asia/Manila');
    $date = date('Y-m-d');
    $_SESSION["date"] = $date;

    // Function to generate a random verification code
    function generateVerificationCode($length = 6)
    {
        return substr(str_shuffle('0123456789'), 0, $length);
    }

    // Ensure database connection uses MySQLi
    if (!$database instanceof mysqli) {
        die("Database connection error");
    }

    // Check if personal details exist
    if (!isset($_SESSION['personal']) || empty($_SESSION['personal'])) {
        header("Location: signup.php");
        exit();
    }

    // Initialize rate-limiting session variables
    if (!isset($_SESSION['email_attempts'])) {
        $_SESSION['email_attempts'] = 0;
        $_SESSION['email_attempts_time'] = time();
    }

    // Initialize code attempt counter
    if (!isset($_SESSION['code_attempts'])) {
        $_SESSION['code_attempts'] = 0;
    }

    // Handle account creation form submission
    if (isset($_POST['submit_account'])) {
        $fname = $_SESSION['personal']['fname'];
        $lname = $_SESSION['personal']['lname'];
        $name = $fname . " " . $lname;
        $address = $_SESSION['personal']['address'];
        $gender = $_SESSION['personal']['gender'];
        $dob = $_SESSION['personal']['dob'];
        $email = $_POST['newemail'];
        $tele = $_POST['tele'];
        $newpassword = $_POST['newpassword'];
        $cpassword = $_POST['cpassword'];

        // Password validation
        $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

        if (!preg_match($passwordRegex, $newpassword)) {
            $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password must be at least 8 characters long, contain 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character (@$!%*?&).</label>';
            $showAccountForm = true;
        } elseif ($newpassword !== $cpassword) {
            $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Password Confirmation Error! Reconfirm Password</label>';
            $showAccountForm = true;
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Invalid email format. Please enter a valid email address.</label>';
            $showAccountForm = true;
        } else {
            // Check if email exists in database
            $stmt = $database->prepare("SELECT * FROM webuser WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Already have an account for this Email address.</label>';
                $showAccountForm = true;
            } else {
                // Check rate limit (3 emails per 15 minutes)
                $currentTime = time();
                $rateLimitWindow = 15 * 60; // 15 minutes in seconds
                $maxAttempts = 3;

                if ($currentTime - $_SESSION['email_attempts_time'] > $rateLimitWindow) {
                    // Reset attempts if time window has expired
                    $_SESSION['email_attempts'] = 0;
                    $_SESSION['email_attempts_time'] = $currentTime;
                }

                if ($_SESSION['email_attempts'] >= $maxAttempts) {
                    $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Too many email attempts. Please try again later.</label>';
                    $showAccountForm = true;
                } else {
                    // Increment email attempt counter
                    $_SESSION['email_attempts']++;
                    // Reset code attempt counter for new verification code
                    $_SESSION['code_attempts'] = 0;

                    // Generate and store verification code with timestamp
                    $verificationCode = generateVerificationCode();
                    $_SESSION['verification_code'] = $verificationCode;
                    $_SESSION['code_timestamp'] = $currentTime;
                    $_SESSION['signup_data'] = [
                        'email' => $email,
                        'tele' => $tele,
                        'newpassword' => $newpassword,
                        'fname' => $fname,
                        'lname' => $lname,
                        'address' => $address,
                        'dob' => $dob,
                        'gender' => $gender
                    ];

                    // Send verification email
                    $mail = new PHPMailer(true);
                    try {
                        // Server settings
                        $mail->isSMTP();
                        $mail->Host = 'smtp.gmail.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = 'mgmangubat@ccc.edu.ph';
                        $mail->Password = 'oenv xscw mokz rohw';
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                        $mail->Port = 587;

                        // Recipients
                        $mail->setFrom('no-reply@yourdomain.com', 'Dental Shyne Tooth Care');
                        $mail->addAddress($email);

                        // Content
                        $mail->isHTML(true);
                        $mail->Subject = 'Dental Shyne Tooth Care - Account Verification Code';
                        $mail->Body = "
                            <p>Dear User,</p>
                            <p>We received a request to create an account. Please use the verification code below to proceed:</p>
                            <h2 style='color: #007bff;'>$verificationCode</h2>
                            <p>If you did not request to create an account, please ignore this email or contact our support team immediately.</p>
                            <br>
                            <p>Thank you,<br>
                            <strong>Dental Shyne Tooth Care</strong></p>
                        ";
                        $mail->AltBody = "
Dear Patient,

We received a request to create an account. Please use the verification code below to proceed:

Verification Code: $verificationCode

If you did not request to create an account, please ignore this email or contact our clinic immediately.

Thank you,
Dental Shyne Tooth Care
";

                        $mail->send();
                        $showAccountForm = false;
                        $showCodeForm = true;
                    } catch (Exception $e) {
                        $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Invalid email address or unable to send verification email: ' . htmlspecialchars($mail->ErrorInfo) . '</label>';
                        $showAccountForm = true;
                        $showCodeForm = false;
                    }
                }
            }
            $stmt->close();
        }
    }

    // Handle verification code submission
    if (isset($_POST['verify_code'])) {
        $enteredCode = $_POST['verification_code'];
        $currentTime = time();
        $codeExpiration = 10 * 60; // 10 minutes in seconds
        $maxCodeAttempts = 5;

        // Check if code has expired
        if (!isset($_SESSION['code_timestamp']) || ($currentTime - $_SESSION['code_timestamp'] > $codeExpiration)) {
            $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Verification code has expired. Please request a new one.</label>';
            $showAccountForm = true;
            $showCodeForm = false;
            unset($_SESSION['verification_code']);
            unset($_SESSION['code_timestamp']);
            unset($_SESSION['code_attempts']);
        } elseif ($enteredCode === $_SESSION['verification_code']) {
            $signupData = $_SESSION['signup_data'];
            $email = $signupData['email'];
            $tele = $signupData['tele'];
            $newpassword = $signupData['newpassword'];
            $fname = $signupData['fname'];
            $lname = $signupData['lname'];
            $name = $fname . " " . $lname;
            $address = $signupData['address'];
            $dob = $signupData['dob'];
            $gender = $signupData['gender'];

            // Hash password
            $hashed_password = password_hash($newpassword, PASSWORD_DEFAULT);

            // Insert into patient table
            $stmt = $database->prepare("INSERT INTO patient (pemail, pname, ppassword, paddress, pdob, ptel, pgender) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssss", $email, $name, $hashed_password, $address, $dob, $tele, $gender);
            $stmt->execute();
            $stmt->close();

            // Insert into webuser table
            $stmt = $database->prepare("INSERT INTO webuser (email, usertype) VALUES (?, 'p')");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->close();

            // Clear session data
            unset($_SESSION['verification_code']);
            unset($_SESSION['code_timestamp']);
            unset($_SESSION['code_attempts']);
            unset($_SESSION['signup_data']);
            unset($_SESSION['personal']);
            unset($_SESSION['email_attempts']);
            unset($_SESSION['email_attempts_time']);

            $_SESSION['user'] = $email;
            $_SESSION['usertype'] = 'p';
            $_SESSION['username'] = $fname;
            header('Location: patient/index.php');
            exit();
        } else {
            // Increment code attempt counter
            $_SESSION['code_attempts']++;

            if ($_SESSION['code_attempts'] >= $maxCodeAttempts) {
                $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Too many invalid verification code attempts. Please request a new code.</label>';
                $showAccountForm = true;
                $showCodeForm = false;
                unset($_SESSION['verification_code']);
                unset($_SESSION['code_timestamp']);
                unset($_SESSION['code_attempts']);
            } else {
                $error = '<label for="promter" class="form-label" style="color:rgb(255, 62, 62);text-align:center;">Invalid verification code</label>';
                $showAccountForm = false;
                $showCodeForm = true;
            }
        }
    }
    ?>

    <div class="container bg-white p-8 rounded-lg shadow-lg max-w-md w-full fade-in-up hover:shadow-xl transition-shadow duration-300">
        <img src="photo/form-logo.png" alt="Login Image" class="mx-auto h-16 mb-6">
        <hr class="border-gray-300 mb-6">
        <div class="text-center fade-in-up-delay">
            <h1 class="text-2xl font-bold text-gray-800 mb-2 fade-in-up-delay">Let's Get Started</h1>
            <p class="text-gray-600">Create User Account</p>
        </div>

        <?php if ($showAccountForm) { ?>
            <!-- Account Creation Form -->
            <form action="" method="POST" class="mt-6 space-y-4 fade-in-up-delay">
                <div class="transition-transform duration-200 hover:scale-105">
                    <label for="newemail" class="block text-sm font-medium text-gray-700">Email:</label>
                    <input type="email" name="newemail" id="newemail" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" placeholder="Email Address" required>
                </div>

                <div class="transition-transform duration-200 hover:scale-105">
                    <label for="tele" class="block text-sm font-medium text-gray-700">Mobile Number:</label>
                    <input type="tel" name="tele" id="tele" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" placeholder="ex: 09123456789" pattern="^09\d{9}$" required>
                </div>

                <div class="transition-transform duration-200 hover:scale-105">
                    <label for="newpassword" class="block text-sm font-medium text-gray-700">Create Password:</label>
                    <div class="relative">
                        <input type="password" name="newpassword" id="newpassword" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" placeholder="New Password" required oninput="validatePasswordDynamic()">
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hidden" onclick="togglePasswordVisibility('newpassword', this)"> <i class="fa-solid fa-eye-slash"></i></button>
                    </div>
                    <p id="passwordError" class="text-red-500 text-sm text-center mt-1"></p>
                </div>

                <div class="transition-transform duration-200 hover:scale-105">
                    <label for="cpassword" class="block text-sm font-medium text-gray-700">Confirm Password:</label>
                    <div class="relative">
                        <input type="password" name="cpassword" id="cpassword" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" placeholder="Confirm Password" required disabled oninput="toggleConfirmPasswordIcon()">
                        <button type="button" id="toggleCPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hidden" onclick="togglePasswordVisibility('cpassword', this)"><i class="fa-solid fa-eye-slash"></i></button>
                    </div>
                </div>
                <!-- Privacy Policy Checkbox -->
                <div class="transition-transform duration-200 hover:scale-105">
                    <label class="flex items-center text-sm font-medium text-gray-700">
                        <input type="checkbox" name="privacy_policy" id="privacy_policy" class="mr-2 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded" required>
                        I agree to the<a href="privacy-policy.html" target="_self" class="text-blue-600 hover:underline"> Privacy Policy</a>
                    </label>
                </div>

                <div class="transition-transform duration-200 hover:scale-105">
                    <p class="text-red-500 text-sm"><?php echo $error ?></p>
                </div>

                <div class="flex space-x-4">
                    <button type="reset" class="w-full bg-gray-200 text-gray-700 py-2 rounded-md hover:bg-gray-300 transition-colors transition-all duration-200">Reset</button>
                    <button type="submit" name="submit_account" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition-colors transition-all duration-200">Sign Up</button>
                </div>
            </form>
        <?php } ?>

        <?php if ($showCodeForm) { ?>
            <!-- Verification Code Form -->
            <form action="" method="POST" class="mt-6 space-y-4 fade-in-up-delay">
                <div class="transition-transform duration-200 hover:scale-105">
                    <label for="verification_code" class="block text-sm font-medium text-gray-700">Verification Code:</label>
                    <input type="text" name="verification_code" id="verification_code" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" placeholder="Enter the code sent to your email" required>
                </div>

                <p class="text-red-500 text-sm"><?php echo $error ?></p>

                <button type="submit" name="verify_code" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition-colors transition-all duration-200">Verify Code</button>
            </form>
        <?php } ?>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                Already have an account? <a href="login.php" class="text-blue-600 hover:underline">Login</a>
            </p>
        </div>
    </div>

    <script>
        function validatePasswordDynamic() {
            var password = document.getElementById("newpassword").value;
            var confirmPasswordInput = document.getElementById("cpassword");
            var passwordError = document.getElementById("passwordError");
            var togglePasswordBtn = document.getElementById("togglePassword");
            var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

            if (password.length > 0) {
                togglePasswordBtn.classList.remove("hidden");
                if (!passwordRegex.test(password)) {
                    passwordError.textContent = "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.";
                    confirmPasswordInput.disabled = true;
                } else {
                    passwordError.textContent = "";
                    confirmPasswordInput.disabled = false;
                }
            } else {
                togglePasswordBtn.classList.add("hidden");
                passwordError.textContent = "";
                confirmPasswordInput.disabled = true;
            }
        }

        function toggleConfirmPasswordIcon() {
            var confirmPassword = document.getElementById("cpassword").value;
            var toggleCPasswordBtn = document.getElementById("toggleCPassword");
            if (confirmPassword.length > 0) {
                toggleCPasswordBtn.classList.remove("hidden");
            } else {
                toggleCPasswordBtn.classList.add("hidden");
            }
        }

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
</body>

</html>