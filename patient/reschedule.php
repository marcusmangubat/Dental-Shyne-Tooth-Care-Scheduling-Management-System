<?php
session_start();
if (!isset($_SESSION["user"]) || empty($_SESSION["user"]) || $_SESSION['usertype'] != 'p') {
    header("location: ../login.php");
    exit();
}
include "../config.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && (isset($_POST['reschedule']) || isset($_POST['change_time']))) {
    $appoid = $_POST['appoid'];
    $useremail = $_SESSION["user"];
    $is_change_time = isset($_POST['change_time']);

    try {
        // Fetch patient ID
        $stmt = $database->prepare("SELECT pid FROM patient WHERE pemail = ?");
        $stmt->bind_param("s", $useremail);
        $stmt->execute();
        $result = $stmt->get_result();
        $userfetch = $result->fetch_assoc();
        $pid = $userfetch['pid'];
        $stmt->close();

        // Start transaction
        $database->begin_transaction();

        if ($is_change_time) {
            // Change time slot within the same schedule
            $scheduleid = $_POST['scheduleid'];
            $new_apponum = $_POST['new_apponum'];

            // Validate schedule
            $stmt = $database->prepare("SELECT scheduledate, nop FROM schedule WHERE scheduleid = ?");
            $stmt->bind_param("i", $scheduleid);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                $database->rollback();
                header("location: appointment.php?action=error&message=Invalid schedule");
                exit();
            }
            $schedule = $result->fetch_assoc();
            $appodate = $schedule['scheduledate'];
            $max_capacity = $schedule['nop'];
            $stmt->close();

            // Check if the slot is already booked
            $stmt = $database->prepare("SELECT COUNT(*) as count FROM appointment WHERE scheduleid = ? AND apponum = ?");
            $stmt->bind_param("ii", $scheduleid, $new_apponum);
            $stmt->execute();
            $result = $stmt->get_result();
            $slot_booked = $result->fetch_assoc()['count'] > 0;
            if ($slot_booked) {
                $database->rollback();
                header("location: appointment.php?action=session-full");
                exit();
            }
            $stmt->close();

            // Check for conflicts (same date, different schedule)
            $stmt = $database->prepare("SELECT COUNT(*) as count FROM appointment a
                                       JOIN schedule s ON a.scheduleid = s.scheduleid
                                       WHERE a.pid = ? AND s.scheduledate = ? AND a.appoid != ?");
            $stmt->bind_param("isi", $pid, $appodate, $appoid);
            $stmt->execute();
            $result = $stmt->get_result();
            $conflict = $result->fetch_assoc()['count'];
            if ($conflict > 0) {
                $database->rollback();
                header("location: appointment.php?action=already-booked");
                exit();
            }
            $stmt->close();

            // Update appointment with new apponum
            $stmt = $database->prepare("UPDATE appointment SET apponum = ? WHERE appoid = ? AND pid = ?");
            $stmt->bind_param("iii", $new_apponum, $appoid, $pid);
            $stmt->execute();
            $stmt->close();
        } else {
            // Full reschedule (different date/doctor)
            $old_scheduleid = $_POST['old_scheduleid'];

            // Parse the combined value from the radio button
            $new_schedule_info = explode(':', $_POST['new_schedule_info']);
            $new_scheduleid = intval($new_schedule_info[0]);
            $new_apponum = intval($new_schedule_info[1]);
            // Now both $new_scheduleid and $new_apponum are correctly extracted

            // Validate new schedule
            $max_capacity = 10; // Define max capacity
            $stmt = $database->prepare("SELECT nop, scheduledate FROM schedule WHERE scheduleid = ?");
            $stmt->bind_param("i", $new_scheduleid);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($result->num_rows == 0) {
                $database->rollback();
                header("location: appointment.php?action=error&message=Invalid schedule");
                exit();
            }
            $schedule = $result->fetch_assoc();
            // The `nop` in schedule table stores the total number of patient slots available
            // not the current count of booked patients. We need to check if the new_apponum
            // is within the available slots (1 to nop) and if that specific slot is not already booked.

            $available_slots_in_schedule = $schedule['nop']; // Total number of slots for this schedule
            $new_appodate = $schedule['scheduledate'];
            $stmt->close();

            // Check if the selected apponum is within the valid range for the new schedule
            if ($new_apponum <= 0 || $new_apponum > $available_slots_in_schedule) {
                $database->rollback();
                header("location: appointment.php?action=error&message=Invalid appointment number selected for schedule.");
                exit();
            }

            // Check if the specific slot (scheduleid + apponum) is already booked
            $stmt = $database->prepare("SELECT COUNT(*) as count FROM appointment WHERE scheduleid = ? AND apponum = ?");
            $stmt->bind_param("ii", $new_scheduleid, $new_apponum);
            $stmt->execute();
            $result = $stmt->get_result();
            $slot_booked = $result->fetch_assoc()['count'] > 0;
            if ($slot_booked) {
                $database->rollback();
                header("location: appointment.php?action=session-full"); // Use session-full for specific slot booking
                exit();
            }
            $stmt->close();

            // Check for conflicts (patient already has another appointment on the *new_appodate* but on a *different scheduleid*)
            // This is important to prevent double booking on the same day for different schedule IDs
            $stmt = $database->prepare("SELECT COUNT(*) as count FROM appointment a
                                       JOIN schedule s ON a.scheduleid = s.scheduleid
                                       WHERE a.pid = ? AND s.scheduledate = ? AND a.appoid != ?"); // check if appoid is *not* the current one being rescheduled
            $stmt->bind_param("isi", $pid, $new_appodate, $appoid); // Pass $appoid to exclude the current appointment
            $stmt->execute();
            $result = $stmt->get_result();
            $conflict = $result->fetch_assoc()['count'];
            if ($conflict > 0) {
                $database->rollback();
                header("location: appointment.php?action=already-booked");
                exit();
            }
            $stmt->close();

            // Update appointment
            // Remove the `appodate` from being updated by `new_appodate` here.
            // The `appodate` column in your `appointment` table usually means the date the appointment was *booked*.
            // The `scheduledate` of the schedule (which is what `$new_appodate` is) already dictates the actual appointment date.
            // If `appodate` is truly intended to be the *scheduled* date, then it should be updated.
            // Based on your initial schema and common practice, `appodate` is often the booking date, `scheduledate` is the actual date.
            // Let's assume `appodate` is the booking date and should reflect the current date of reschedule.
            $current_booking_date = date('Y-m-d'); // Date of the reschedule
            $stmt = $database->prepare("UPDATE appointment SET scheduleid = ?, appodate = ?, apponum = ? WHERE appoid = ? AND pid = ?");
            $stmt->bind_param("isiii", $new_scheduleid, $current_booking_date, $new_apponum, $appoid, $pid);
            $stmt->execute();
            $stmt->close();

            // IMPORTANT: Your `schedule.nop` seems to represent the *total number of slots available for that schedule* (e.g., 10 patients)
            // NOT the count of currently booked patients.
            // So, do NOT increment/decrement `nop` in the `schedule` table.
            // If `nop` *does* represent the current number of booked patients, then your `schedule` table structure is problematic
            // because a schedule should have a fixed `max_capacity` (e.g., 10 slots) and then you count actual bookings.

            // Assuming 'nop' in `schedule` means "number of patients *this schedule can accommodate*",
            // then you should *not* change `nop`. If it means "number of *currently booked* patients",
            // then you need to manage it.
            // For now, I'm removing the `nop` updates based on the assumption that `nop` is the max capacity.
            // If `nop` IS meant to be a live counter, then it should be renamed to something like `booked_slots_count` and initialized to 0.

            // Original code to decrement/increment nop - REMOVED based on assumption
            // $stmt = $database->prepare("UPDATE schedule SET nop = nop - 1 WHERE scheduleid = ?");
            // $stmt->bind_param("i", $old_scheduleid);
            // $stmt->execute();
            // $stmt->close();

            // $stmt = $database->prepare("UPDATE schedule SET nop = nop + 1 WHERE scheduleid = ?");
            // $stmt->bind_param("i", $new_scheduleid);
            // $stmt->execute();
            // $stmt->close();
        }

        // Commit transaction
        $database->commit();

        header("location: appointment.php?action=reschedule-success");
    } catch (Exception $e) {
        $database->rollback();
        header("location: appointment.php?action=error&message=" . urlencode($e->getMessage()));
    }
} else {
    header("location: appointment.php");
}
