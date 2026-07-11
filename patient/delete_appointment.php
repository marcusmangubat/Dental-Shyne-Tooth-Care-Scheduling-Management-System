
<?php
session_start();
if (isset($_SESSION["user"])) {
    if (($_SESSION["user"]) == "" || $_SESSION['usertype'] != 'p') {
        header("location: ../login.php");
    } else {
        $useremail = $_SESSION["user"];
    }
} else {
    header("location: ../login.php");
}

include "../config.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../phpmailer/src/Exception.php';
require __DIR__ . '/../phpmailer/src/PHPMailer.php';
require __DIR__ . '/../phpmailer/src/SMTP.php';

if ($_GET && isset($_GET["id"])) {
    $appoid = $_GET["id"];

    // fetch appointment details, including doctor and patient info
    $sql = "SELECT appointment.appoid, schedule.scheduledate, schedule.scheduletime, 
                   doctor.docname, doctor.docemail, patient.pname, patient.pemail 
            FROM appointment 
            INNER JOIN schedule ON appointment.scheduleid = schedule.scheduleid 
            INNER JOIN doctor ON schedule.docid = doctor.docid 
            INNER JOIN patient ON appointment.pid = patient.pid 
            WHERE appointment.appoid = '$appoid'";

    $result = $database->query($sql);

    if ($result->num_rows > 0) {
        $appointment = $result->fetch_assoc();

        // prepare email details
        $doctor_email = $appointment['docemail'];
        $doctor_name = $appointment['docname'];
        $patient_name = $appointment['pname'];
        $scheduled_date = date("F j, Y", strtotime($appointment['scheduledate']));
        $scheduled_time = date("g:i A", strtotime($appointment['scheduletime']));

        // delete the appointment
        $delete_sql = "DELETE FROM appointment WHERE appoid = '$appoid'";
        $database->query($delete_sql);

        // set up PHPMailer
        $mail = new PHPMailer(true);
        try {
            // server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'mgmangubat@ccc.edu.ph';
            $mail->Password = 'oenv xscw mokz rohw';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // recipients
            $mail->setFrom('mgmangubat@ccc.edu.ph', 'Dental Shyne Clinic');
            $mail->addAddress($doctor_email, $doctor_name);
            $mail->addReplyTo($appointment['pemail'], $patient_name);

            // content
            $mail->isHTML(true);
            $mail->Subject = 'Appointment Cancellation Notification';
            $mail->Body = "
                <h3>Appointment Cancellation</h3>
                <p>Dear Dr. $doctor_name,</p>
                <p>An appointment with the following details has been canceled:</p>
                <ul>
                    <li><strong>Patient:</strong> $patient_name</li>
                    <li><strong>Date:</strong> $scheduled_date</li>
                    <li><strong>Time:</strong> $scheduled_time</li>
                </ul>
                <p>Please contact the patient or check the system for further details.</p>
                <p>Best regards,<br>Dental Shyne Clinic</p>
            ";
            $mail->AltBody = "Appointment Cancellation\n\nDear Dr. $doctor_name,\n\nAn appointment with the following details has been canceled:\n- Patient: $patient_name\n- Date: $scheduled_date\n- Time: $scheduled_time\n\nPlease contact the patient or check the system for further details.\n\nBest regards,\nDental Shyne Clinic";

            $mail->send();
        } catch (Exception $e) {
            // log the error or handle it as needed
            error_log("PHPMailer Error: " . $mail->ErrorInfo);
        }
    }

    // redirect to appointment page with a cancellation success message
    header("location: appointment.php?action=canceled");
} else {
    header("location: appointment.php");
}
?>