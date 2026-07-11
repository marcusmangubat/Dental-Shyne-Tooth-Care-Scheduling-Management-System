```php
<?php
session_start();
if (isset($_SESSION["user"])) {
    if (($_SESSION["user"]) == "" or $_SESSION['usertype'] != 'a') {
        header("location: ../login.php");
    }
} else {
    header("location: ../login.php");
}

include "../config.php";

if ($_POST) {
    $scheduleid = $_POST['scheduleid'];
    $title = $_POST['title'];
    $docid = $_POST['docid'];
    $nop = $_POST['nop'];
    $scheduledate = $_POST['date'];
    $scheduletime = $_POST['time'];
    $hours = $_POST['hours'];
    $minutes = $_POST['minutes'];

    // Format duration as TIME (HH:MM:SS)
    $estimated_duration = sprintf("%02d:%02d:00", $hours, $minutes);

    // Update schedule in the database
    $sql = "UPDATE schedule SET title='$title', docid='$docid', scheduledate='$scheduledate', scheduletime='$scheduletime', nop='$nop', estimated_duration='$estimated_duration' WHERE scheduleid='$scheduleid'";
    $result = $database->query($sql);

    if ($result) {
        header("location: schedule.php?action=session-edited&title=" . urlencode($title));
    } else {
        echo "Error updating schedule: " . $database->error;
    }
} else {
    header("location: schedule.php");
}
?>
```