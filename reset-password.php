<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" type="image/png" href="./photo/logo_cropped.png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
    require 'phpmailer/src/PHPMailer.php';
    require 'phpmailer/src/SMTP.php';
    require 'phpmailer/src/Exception.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    // Ensure database connection uses MySQLi
    if (!$database instanceof mysqli) {
        die("Database connection error");
    }

    $error = '<label for="promter" class="form-label"> &nbsp;</label>';
    $success = '';
    $showEmailForm = true;
    $showCodeForm = false;
    $showPasswordForm = false;
    $userEmail = '';
    $userType = '';

    // Function to generate a random verification code
    function generateVerificationCode($length = 6)
    {
        return substr(str_shuffle('0123456789'), 0, $length);
    }

    // Initialize code attempt counter
    if (!isset($_SESSION['code_attempts'])) {
        $_SESSION['code_attempts'] = 0;
    }

    // Email verification
    if (isset($_POST['verify_email'])) {
        $email = $_POST['useremail'];
        $userEmail = $email;

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = '<label for="promter" class="form-label" style="color: rgb(255, 62, 62); text-align:center;">Invalid email format. Please enter a valid email address.</label>';
            $showEmailForm = true;
        } else {
            $stmt = $database->prepare("SELECT * FROM webuser WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows == 1) {
                $userType = $result->fetch_assoc()['usertype'];

                // Generate and store verification code with timestamp
                $verificationCode = generateVerificationCode();
                $_SESSION['verification_code'] = $verificationCode;
                $_SESSION['reset_email'] = $email;
                $_SESSION['user_type'] = $userType;
                $_SESSION['code_timestamp'] = time();
                $_SESSION['code_attempts'] = 0; // Reset code attempts for new code

                // Send verification email using PHPMailer
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
                    $mail->setFrom('no-reply@dentalshyne.com', 'Dental Shyne Tooth Care');
                    $mail->addAddress($email);

                    // Content
                    $mail->isHTML(true);
                    $mail->Subject = 'Password Reset Verification Code';
                    $mail->Body = "Your verification code is: <strong>$verificationCode</strong><br><br>This code will expire in 10 minutes. Please enter it to proceed with your password reset.<br><br>\n\nBest regards,<br>\nDental Shyne Tooth Care";
                    $mail->AltBody = "Your verification code is: $verificationCode\n\nThis code will expire in 10 minutes. Please enter it to proceed with your password reset.\n\nBest regards,\nDental Shyne Tooth Care";

                    $mail->send();
                    $showEmailForm = false;
                    $showCodeForm = true;
                } catch (Exception $e) {
                    $error = '<label for="promter" class="form-label" style="color: rgb(255, 62, 62); text-align:center;">Failed to send verification email: ' . htmlspecialchars($mail->ErrorInfo) . '</label>';
                    $showEmailForm = true;
                }
            } else {
                $error = '<label for="promter" class="form-label" style="color: rgb(255, 62, 62); text-align:center;">No account found with this email address</label>';
                $showEmailForm = true;
            }
            $stmt->close();
        }
    }

    // Code verification
    if (isset($_POST['verify_code'])) {
        $enteredCode = $_POST['verification_code'];
        $userEmail = $_SESSION['reset_email'];
        $userType = $_SESSION['user_type'];
        $maxCodeAttempts = 5;

        // Check if code has expired (10 minutes)
        if (!isset($_SESSION['code_timestamp']) || (time() - $_SESSION['code_timestamp'] > 600)) {
            $error = '<label for="promter" class="form-label" style="color: rgb(255, 62, 62); text-align:center;">Verification code has expired. Please request a new one.</label>';
            $showEmailForm = true;
            $showCodeForm = false;
            unset($_SESSION['verification_code']);
            unset($_SESSION['code_timestamp']);
            unset($_SESSION['code_attempts']);
        } elseif ($enteredCode === $_SESSION['verification_code']) {
            $showEmailForm = false;
            $showCodeForm = false;
            $showPasswordForm = true;
            $_SESSION['code_attempts'] = 0; // Reset attempts on successful verification
        } else {
            // Increment code attempt counter
            $_SESSION['code_attempts']++;

            if ($_SESSION['code_attempts'] >= $maxCodeAttempts) {
                $error = '<label for="promter" class="form-label" style="color: rgb(255, 62, 62); text-align:center;">Too many invalid verification code attempts. Please request a new code.</label>';
                $showEmailForm = true;
                $showCodeForm = false;
                unset($_SESSION['verification_code']);
                unset($_SESSION['code_timestamp']);
                unset($_SESSION['code_attempts']);
            } else {
                $error = '<label for="promter" class="form-label" style="color: rgb(255, 62, 62); text-align:center;">Incorrect verification code</label>';
                $showEmailForm = false;
                $showCodeForm = true;
            }
        }
    }

    // Password reset
    if (isset($_POST['reset_password'])) {
        $email = $_POST['useremail'];
        $userType = $_POST['usertype'];
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

        // Password validation regex: min 8 chars, 1 uppercase, 1 lowercase, 1 number, 1 special char
        $passwordRegex = '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';

        if (!preg_match($passwordRegex, $newPassword)) {
            $error = '<label for="promter" class="form-label" style="color: rgb(255, 62, 62); text-align:center;">Password must be at least 8 characters long, contain 1 uppercase letter, 1 lowercase letter, 1 number, and 1 special character (@$!%*?&).</label>';
            $showEmailForm = false;
            $showPasswordForm = true;
            $userEmail = $email;
        } elseif ($newPassword !== $confirmPassword) {
            $error = '<label for="promter" class="form-label" style="color: rgb(255, 62, 62); text-align:center;">Passwords do not match</label>';
            $showEmailForm = false;
            $showPasswordForm = true;
            $userEmail = $email;
        } else {
            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update password based on user type
            if ($userType == 'p') {
                $stmt = $database->prepare("UPDATE patient SET ppassword = ? WHERE pemail = ?");
                $stmt->bind_param("ss", $hashedPassword, $email);
                $stmt->execute();
                $stmt->close();
            } else if ($userType == 'a') {
                $stmt = $database->prepare("UPDATE admin SET apassword = ? WHERE aemail = ?");
                $stmt->bind_param("ss", $hashedPassword, $email);
                $stmt->execute();
                $stmt->close();
            } else if ($userType == 'd') {
                $stmt = $database->prepare("UPDATE doctor SET docpassword = ? WHERE docemail = ?");
                $stmt->bind_param("ss", $hashedPassword, $email);
                $stmt->execute();
                $stmt->close();
            } else if ($userType == 'f') {
                $stmt = $database->prepare("UPDATE frontdesk SET fdpassword = ? WHERE fdemail = ?");
                $stmt->bind_param("ss", $hashedPassword, $email);
                $stmt->execute();
                $stmt->close();
            }

            // Clear session data
            unset($_SESSION['verification_code']);
            unset($_SESSION['reset_email']);
            unset($_SESSION['user_type']);
            unset($_SESSION['code_timestamp']);
            unset($_SESSION['code_attempts']);

            $success = '<label for="promter" class="form-label" style="color: green; text-align:center;">Password reset successful! <a href="login.php">Login now</a></label>';
            $showEmailForm = false;
            $showCodeForm = false;
            $showPasswordForm = false;
        }
    }
    ?>

    <div class="container bg-white p-8 rounded-lg shadow-lg max-w-md w-full fade-in-up hover:shadow-xl transition-shadow duration-300">
        <img src="photo/form-logo.png" alt="Reset Password Image" class="mx-auto h-16 mb-6">
        <hr class="border-gray-300 mb-6">
        <div class="text-center fade-in-up-delay">
            <h1 class="text-2xl font-bold text-gray-800 mb-2 fade-in-up-delay">Reset Password</h1>
            <p class="text-gray-600">Enter your details to reset your password</p>
        </div>

        <?php if ($success) { ?>
            <div class="text-center my-6 text-green-600 text-sm">
                <?php echo $success; ?>
            </div>
        <?php } ?>

        <?php if ($showEmailForm) { ?>
            <!-- Step 1: Email Form -->
            <form action="" method="POST" class="mt-6 space-y-4 fade-in-up-delay">
                <div class="transition-transform duration-200 hover:scale-105">
                    <label for="useremail" class="block text-sm font-medium text-gray-700">Email:</label>
                    <input type="email" name="useremail" id="useremail" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" placeholder="Enter your email address" required>
                </div>

                <p class="text-red-500 text-sm"><?php echo $error ?></p>

                <button type="submit" name="verify_email" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition-colors transition-all duration-200">Send Verification Code</button>
            </form>
        <?php } ?>

        <?php if ($showCodeForm) { ?>
            <!-- Step 2: Verification Code Form -->
            <form action="" method="POST" class="mt-6 space-y-4 fade-in-up-delay">
                <input type="hidden" name="useremail" value="<?php echo htmlspecialchars($userEmail); ?>">
                <div class="transition-transform duration-200 hover:scale-105">
                    <label for="verification_code" class="block text-sm font-medium text-gray-700">Verification Code:</label>
                    <input type="text" name="verification_code" id="verification_code" class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200" placeholder="Enter the code sent to your email" required>
                </div>
                <p class="text-gray-500 text-sm text-center">This verification code will expire in 10 minutes.</p>
                <p class="text-red-500 text-sm"><?php echo $error ?></p>

                <button type="submit" name="verify_code" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition-colors transition-all duration-200">Verify Code</button>
            </form>
        <?php } ?>

        <?php if ($showPasswordForm) { ?>
            <!-- Step 3: Password Reset Form -->
            <form action="" method="POST" class="mt-6 space-y-4" onsubmit="return validatePassword()">
                <input type="hidden" name="useremail" value="<?php echo htmlspecialchars($userEmail); ?>">
                <input type="hidden" name="usertype" value="<?php echo htmlspecialchars($userType); ?>">

                <div class="transition-transform duration-200 hover:scale-105">
                    <label for="new_password" class="block text-sm font-medium text-gray-700">New Password:</label>
                    <div class="relative">
                        <input type="password" name="new_password" id="new_password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Enter new password" required oninput="validatePasswordDynamic()">
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hidden" onclick="togglePasswordVisibility('new_password', this)">
                            <i class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>
                    <p id="passwordError" class="text-red-500 text-sm text-center mt-1"></p>
                </div>

                <div class="transition-transform duration-200 hover:scale-105">
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700">Confirm Password:</label>
                    <div class="relative">
                        <input type="password" name="confirm_password" id="confirm_password" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Confirm new password" required disabled oninput="toggleConfirmPasswordIcon()">
                        <button type="button" id="toggleCPassword" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hidden" onclick="togglePasswordVisibility('confirm_password', this)">
                            <i class="fa-solid fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <p class="text-red-500 text-sm"><?php echo $error ?></p>

                <button type="submit" name="reset_password" class="w-full bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition-colors">Reset Password</button>
            </form>
        <?php } ?>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                Remember your password? <a href="login.php" class="text-blue-600 hover:underline">Login</a>
            </p>
        </div>
    </div>

    <script>
        function validatePasswordDynamic() {
            var password = document.getElementById("new_password").value;
            var confirmPasswordInput = document.getElementById("confirm_password");
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
            var confirmPassword = document.getElementById("confirm_password").value;
            var toggleCPasswordBtn = document.getElementById("toggleCPassword");
            if (confirmPassword.length > 0) {
                toggleCPasswordBtn.classList.remove("hidden");
            } else {
                toggleCPasswordBtn.classList.add("hidden");
            }
        }

        function validatePassword() {
            var password = document.getElementById("new_password").value;
            var confirmPassword = document.getElementById("confirm_password").value;
            var passwordError = document.getElementById("passwordError");
            var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/;

            if (!passwordRegex.test(password)) {
                passwordError.textContent = "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.";
                return false;
            }
            if (password !== confirmPassword) {
                passwordError.textContent = "Passwords do not match!";
                return false;
            }
            return true;
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