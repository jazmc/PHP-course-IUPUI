<?php include 'header.html';
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once '../dbconn.php';

// init year & month
$year = date('Y');
$month = date('m');
if (!empty($_GET['year'])) {
    $year = sanitize($_GET['year']);
}
if (!empty($_GET['month'])) {
    $month = sanitize($_GET['month']);
}

$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$weekday_of_first = date('N', mktime(0, 0, 0, $month, 1, $year));

function returnEventsByDate($conn, $yyyy_mm_dd) {
    if ($conn) {
        $query = "
        SELECT e.*, c.hex, c.title as category 
        FROM php2022jasmin_events e 
        JOIN php2022jasmin_categories c ON e.category_id = c.category_id 
        WHERE DATE(e.start) = (?) 
        OR (DATE(e.start) <= (?) AND DATE(e.end) >= (?))
        OR DATE(e.end) = (?) 
        ORDER BY e.start";

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssss", $yyyy_mm_dd, $yyyy_mm_dd, $yyyy_mm_dd, $yyyy_mm_dd);
        mysqli_stmt_execute($stmt);
        $results = mysqli_stmt_get_result($stmt) or die(mysqli_error($conn));

        return mysqli_fetch_all($results, MYSQLI_ASSOC);
    } else {
        die("Error with database connection");
    }
}

function startsBeforeToday($event, $date) {
    if (date("Y-m-d", strtotime($date)) > date("Y-m-d", strtotime($event['start']))) {
        return true;
    }
    return false;
}

function startsToday($event, $date) {
    if (date("Y-m-d", strtotime($date)) == date("Y-m-d", strtotime($event['start']))) {
        return true;
    }
    return false;
}

function endsToday($event, $date) {
    if (date("Y-m-d", strtotime($date)) == date("Y-m-d", strtotime($event['end']))) {
        return true;
    }
    return false;
}

function endsAfterToday($event, $date) {
    if (date("Y-m-d", strtotime($date)) < date("Y-m-d", strtotime($event['end']))) {
        return true;
    }
    return false;
}

?>

<h1><?php echo date('F Y', mktime(0, 0, 0, $month, 1, $year)); ?></h1>

<div class="calendar">
    <div class="weekdays">
        <div>Mon</div>
        <div>Tue</div>
        <div>Wed</div>
        <div>Thu</div>
        <div>Fri</div>
        <div>Sat</div>
        <div>Sun</div>
    </div>
    <div class="dates">
        <?php
        for ($i = 1; $i <= $days_in_month; $i++) {
            $today = $year . "-" . $month . "-" . $i;
            echo "<div class=\"day";
            if (date("Y-m-j") == $year . "-" . $month . "-" . $i) {
                echo " today";
            }
            if ($i == 1) {
                echo "\" style=\"grid-column:" . $weekday_of_first;
            }
            echo "\">\n";
            echo "    <div class=\"dayhead\">";
            echo "<div class=\"datenr\">";
            echo "<span class=\"mobile\">" . date('D', strtotime($today)) . "</span> ";
            echo $i;
            echo "<span class=\"mobile\">.$month.</span>";
            echo "</div>";
            // echo whole-day events
            echo "</div>\n";

            // fetch hourly events for a certain date
            $events_array = returnEventsByDate($conn, $today);

            // loop through events
            foreach ($events_array as $event) {
                echo "    <div class=\"event\"";
                if (!empty($event['hex'])) {
                    echo " style=\"border-color:" . $event['hex'] . "\"";
                }
                echo ">";

                // if event starts today, echo starting time
                if (startsToday($event, $today)) {
                    echo "<b>" . date('H:i', strtotime($event['start'])) . "</b> ";
                    // if event starts today, echo ending time
                } else if (endsToday($event, $today)) {
                    echo "<b>" . date('H:i', strtotime($event['end'])) . "</b> ";
                    // if a multi-day event is continuing through the whole day, echo dashes
                } else {
                    echo "<b>--:--</b> ";
                }

                // event title
                echo $event['title'];

                // add starts / continues / ends for multi-day events
                if (startsBeforeToday($event, $today) && endsAfterToday($event, $today)) {
                    echo " (continues)";
                } else if (startsBeforeToday($event, $today) && endsToday($event, $today)) {
                    echo " (ends)";
                } else if (startsToday($event, $today) && endsAfterToday($event, $today)) {
                    echo " (starts)";
                }

                echo "</div>\n";
            }

            echo "</div>\n";
        }
        ?>
    </div>
</div>

<?php include 'footer.html'; ?>