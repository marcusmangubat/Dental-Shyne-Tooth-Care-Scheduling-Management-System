<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../phpmailer/src/Exception.php';
require __DIR__ . '/../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../phpmailer/src/SMTP.php';

// Check if user is logged in and has doctor privileges
if (!isset($_SESSION["user"]) || empty($_SESSION["user"]) || $_SESSION['usertype'] != 'd') {
    header("location: ../login.php");
    exit;
}

if (isset($_GET["id"])) {
    include "../config.php";

    $appoid = $_GET["id"];

    // Fetch appointment, patient, schedule, and doctor details
    $sql = "SELECT 
                p.pname, 
                p.pemail, 
                d.docname, 
                d.docemail, 
                s.title, 
                s.scheduletime, 
                a.appodate
            FROM appointment a
            JOIN patient p ON a.pid = p.pid
            JOIN schedule s ON a.scheduleid = s.scheduleid
            JOIN doctor d ON s.docid = d.docid
            WHERE a.appoid = ?";

    $stmt = $database->prepare($sql);
    $stmt->bind_param("i", $appoid);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $patient_name = $row['pname'];
        $patient_email = $row['pemail'];
        $doctor_email = $row['docemail'];
        $doctor_name = $row['docname'];
        $appointment_date = date("D, M j, Y", strtotime($row['appodate']));
        $scheduletime = date("h:i A", strtotime($row['scheduletime']));
        $scheduletitle = $row['title'];

        // Delete the appointment
        $delete_sql = "DELETE FROM appointment WHERE appoid = ?";
        $delete_stmt = $database->prepare($delete_sql);
        $delete_stmt->bind_param("i", $appoid);

        if ($delete_stmt->execute()) {
            // Initialize PHPMailer
            $mail = new PHPMailer(true);
            try {
                // SMTP Configuration
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'mgmangubat@ccc.edu.ph';
                $mail->Password = 'oenv xscw mokz rohw';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Email settings
                $mail->setFrom('clinic@example.com', 'Dental Shyne Clinic');
                $mail->addAddress($patient_email, $patient_name);
                $mail->addReplyTo($doctor_email, 'Dr. ' . $doctor_name);

                $mail->isHTML(true);
                $mail->Subject = 'Appointment Cancellation Notice';
                $mail->Body = "
                    <h2>Appointment Cancelled</h2>
                    <p>Dear {$patient_name},</p>
                    <p>Your appointment with Dr. {$doctor_name} has been cancelled:</p>
                    <ul>
                        <li><strong>Title:</strong> {$scheduletitle}</li>
                        <li><strong>Date:</strong> {$appointment_date}</li>
                        <li><strong>Time:</strong> {$scheduletime}</li>
                    </ul>
                    <p>Please check for other doctors schedule.</p>
                    <p>Best regards,<br>Dental Shyne Clinic</p>
                ";
                $mail->AltBody = "Dear {$patient_name},\n\nYour appointment '{$scheduletitle}' with Dr. {$doctor_name} on {$appointment_date} at {$scheduletime} has been cancelled. Please contact us to reschedule.\n\nBest regards,\nDental Shyne Clinic";

                $mail->send();
            } catch (Exception $e) {
                error_log("Email failed for appointment ID {$appoid}: {$mail->ErrorInfo}");
            }
        } else {
            error_log("Failed to delete appointment ID: {$appoid}");
        }
    } else {
        error_log("No appointment found for ID: {$appoid}");
    }

    header("location: appointment.php");
    exit;
}
