<?php
session_start();

if (isset($_SESSION["user"])) {
    if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'a') {
        header("location: ../login.php");
    }
} else {
    header("location: ../login.php");
}


if ($_POST) {
    //database connection
    include "../config.php";
    $title = $_POST["title"];
    $docid = $_POST["docid"];
    $nop = $_POST["nop"];
    $date = $_POST["date"];
    $time = $_POST["time"];

    // Retrieve hours and minutes from the form
    $hours = isset($_POST["hours"]) ? (int)$_POST["hours"] : 0;
    $minutes = isset($_POST["minutes"]) ? (int)$_POST["minutes"] : 0;

    // Format the estimated duration into a TIME string (HH:MM:SS)
    // Assuming seconds are always 00 for estimated duration
    $estimated_duration = sprintf('%02d:%02d:00', $hours, $minutes);

    // SQL query to insert data, including estimated_duration
    // Make sure your 'schedule' table has a column named 'estimated_duration' with TIME datatype
    $sql = "INSERT INTO schedule (docid, title, scheduledate, scheduletime, nop, estimated_duration) VALUES ($docid,'$title','$date','$time',$nop, '$estimated_duration');";

    $result = $database->query($sql);

    if ($result) {
        header("location: schedule.php?action=session-added&title=$title");
    } else {
        // Handle error, e.g., display an error message or log it
        error_log("Error inserting schedule: " . $database->error);
        // You might want to redirect to an error page or show a user-friendly message
        header("location: schedule.php?action=error&message=failed_to_add_schedule");
    }
}
