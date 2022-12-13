<?php include 'header.html';
ini_set('display_errors', 1);
error_reporting(E_ALL);
// my old sanitize function is now in this file also:
require_once '../dbconn.php';

// init year & month & selected event date
$year = date('Y');
$month = date('m');
$selected_date = null;
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

function returnCategories($conn) {
    if ($conn) {
        $query = "
        SELECT c.* FROM php2022jasmin_categories c
        ORDER BY c.title";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_execute($stmt);
        $results = mysqli_stmt_get_result($stmt) or die(mysqli_error($conn));

        return mysqli_fetch_all($results, MYSQLI_ASSOC);
    } else {
        die("Error with database connection");
    }
}

function createCategory($conn, $nameinput = null, $hexinput = null) {
    if ($nameinput == null || $hexinput == null) {
        return false;
    }
    $name = sanitize($nameinput);
    $hex = sanitize($hexinput);
    $query = "
        INSERT INTO php2022jasmin_categories (category_id, title, hex) VALUES (NULL, '$name', '$hex')";
    $stmt = mysqli_prepare($conn, $query) or die(mysqli_error($conn));
    $ok = $stmt->execute() or die(mysqli_error($conn));
    if ($ok) {
        // successful insert
        return mysqli_insert_id($conn);
    }
    return false;
}

function createEvent($conn, $titleinput, $startinput, $endinput, $wholeday = 0, $cat_id) {
    if ($titleinput == null || $startinput == null || $endinput == null || $wholeday == null || $cat_id == null) {
        return false;
    }
    $title = sanitize($titleinput);
    $start = date('Y-m-d H:i:s', strtotime(sanitize($startinput)));
    $end = date('Y-m-d H:i:s', strtotime(sanitize($endinput)));

    if ($start > $end) {
        die("Sorry, your event can not end before it starts!");
    }

    $wholeday = sanitize($wholeday);
    $cat_id = sanitize($cat_id);
    $query = "
        INSERT INTO php2022jasmin_events (event_id, title, start, end, wholeday, category_id) VALUES (NULL, '$title', '$start', '$end', '$wholeday', '$cat_id')";
    $stmt = mysqli_prepare($conn, $query) or die(mysqli_error($conn));
    $ok = $stmt->execute() or die(mysqli_error($conn));
    if ($ok) {
        // successful insert
        return mysqli_insert_id($conn);
    }
    return false;
}

// functions for event display in calendar
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

// event creation form action
if (!empty($_POST['submitevent'])) {
    // is creating new category required?
    if (!empty($_POST['category']) && sanitize($_POST['category']) == 'createnew') {
        $wanted_values = array("title", "start", "end", "wholeday", "category");
        $locationstring = "Location: ?mkevent=" . $selected_date . "&year=" . $year . "&month=" . $month;
        foreach ($wanted_values as $wkey) {
            if (!empty($_POST[$wkey])) {
                $locationstring .= "&" . $wkey . "=" . sanitize($_POST[$wkey]);
            }
        }
        mysqli_close($conn);
        header($locationstring);
        // is new category currently being created?
    } else if (!empty($_POST['cattitle'])) {
        // create new category
        $cat_id = createCategory($conn, $_POST['cattitle'], $_POST['hex']);

        if (!is_numeric($cat_id)) {
            die("Error in creating a new category");
        }
        // create event with last inserted id for category
        if (createEvent($conn, $_POST['title'], $_POST['start'], $_POST['end'], $_POST['wholeday'], $cat_id)) {
            mysqli_close($conn);
            header('Location: ?year=' . $year . '&month=' . $month . '&success=true');
        } else {
            mysqli_close($conn);
            header('Location: ?year=' . $year . '&month=' . $month . '&success=false');
        };
    } else {
        // create new event with existing category
        if (createEvent($conn, $_POST['title'], $_POST['start'], $_POST['end'], $_POST['wholeday'], $_POST['category_id'])) {
            mysqli_close($conn);
            header('Location: ?year=' . $year . '&month=' . $month . '&success=true');
        } else {
            mysqli_close($conn);
            header('Location: ?year=' . $year . '&month=' . $month . '&success=false');
        };
    }
}

?>
<main>

    <div class="arrows">
        <a href="<?php
                    if ($month == 1) {
                        echo "?year=" . $year - 1 . "&month=12";
                    } else {
                        echo "?year=" . $year . "&month=" . $month - 1;
                    }
                    ?>"><i class="fas fa-arrow-circle-left"></i> Previous month</a>
        <a href="<?php
                    if ($month == 12) {
                        echo "?year=" . $year + 1 . "&month=1";
                    } else {
                        echo "?year=" . $year . "&month=" . $month + 1;
                    }
                    ?>">Next month <i class="fas fa-arrow-circle-right"></i></a>
    </div>


    <?php
    // IF NEW EVENT IS BEING CREATED
    if (!empty($_GET['mkevent']) || (!empty($_GET['category']) && $_GET['category'] == 'createnew')) {
        if (empty($_GET['mkevent'])) {
            $selected_date = sanitize(date('Y-m-d', strtotime($_GET['start'])));
        } else {
            $selected_date = sanitize($_GET['mkevent']);
        }

    ?>
    <div id="curtain">
        <h2>Create event</h2>
        <form method="POST">
            <div class="input_container">
                <label for="title">Title:</label>
                <input id="title" type="text" placeholder="Event title" name="title"
                    value="<?php echo (!empty($_GET['title']) ? sanitize($_GET['title']) : "") ?>" required>
            </div>
            <div class="input_container">
                <label for="start">Start time:</label>
                <input id="start" type="datetime-local" name="start" value="<?php if (!empty($_GET['start'])) {
                                                                                    echo sanitize($_GET['start']);
                                                                                } else {
                                                                                    echo $selected_date . "T" . date('H:00');
                                                                                }
                                                                                ?>" required />
            </div>
            <div class="input_container">
                <label for="end">End time:</label>
                <input id="end" type="datetime-local" name="end" value="<?php if (!empty($_GET['end'])) {
                                                                                echo sanitize($_GET['end']);
                                                                            } else {
                                                                                echo $selected_date . "T" . date("H:00", strtotime('+1 hour'));
                                                                            }
                                                                            ?>" required />
            </div>
            <div class="input_container checkbox_container">
                <label for="wholeday">Whole day event?:</label>
                <div>
                    <input type="hidden" name="wholeday" value="0">
                    <input id="wholeday" type="checkbox" name="wholeday" value="1"
                        <?php echo (!empty($_GET['wholeday']) && $_GET['wholeday'] == 1) ? "checked" : ""; ?>>
                </div>
            </div>
            <?php if (empty($_GET['category'])) { ?>
            <div class="input_container">
                <label for="category">Category:</label>
                <select id="category" name="category" required>
                    <option value="" selected disabled>Choose category...</option>
                    <?php
                            $categories = returnCategories($conn);

                            foreach ($categories as $cat) {
                                echo "<option value=\"" . $cat['category_id'] . "\">" . $cat['title'] . "</option>\n";
                            }

                            ?>
                    <option value="createnew">CREATE NEW CATEGORY</option>
                </select>
            </div>
            <?php } else if (!empty($_GET['category']) && $_GET['category'] == 'createnew') { ?>
            <fieldset>
                <legend>Create a new event category</legend>
                <div class="input_container">
                    <label for="cattitle">Category name:</label>
                    <input id="cattitle" type="text" placeholder="My new category" name="cattitle" required>
                </div>
                <div class="input_container checkbox_container">
                    <label for="hec">Color for events:</label>
                    <input id="hex" type="color" name="hex"
                        value="<?php echo sprintf('#%06X', mt_rand(0, 0xFFFFFF)); ?>" required>
                </div>
            </fieldset>
            <?php } ?>
            <div class="input_container button_container">
                <input type="submit" name="submitevent" value="Create">
            </div>
        </form>
    </div>
    <?php } // END OF EVENT CREATION 
    ?>

    <?php
    // IF EVENT IS OPENED FOR EDIT / DELETE
    if (!empty($_GET['event'])) { ?>

    <div id="curtain">
        <div class="arrows">
            <a href="<?php echo "?year=" . $year . "&month=" . $month; ?>"><i class="fas fa-arrow-circle-left"></i>
                Back to calendar view</a>
        </div>
        <h2>Edit event</h2>
        <form action="POST">

            <div class="input_container button_container">
                <input type="submit" name="editevent" value="Save modifications">
                <input type="submit" class="danger" name="deleteevent" value="Delete event">
            </div>
        </form>
    </div>

    <?php } // END OF EVENT EDIT / DELETE 
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
                    echo "    <a href=\"?year=$year&month=$month&event=" . $event['event_id'] . "\"><div class=\"event\" title=\"" . $event['category'] . "\"";
                    if (!empty($event['hex'])) {
                        echo " style=\"border-color:" . $event['hex'] . "\"";
                    }
                    echo ">";

                    if ($event['wholeday'] != 1) {
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

                    echo "</div></a>\n";
                }

                echo "</div>\n";
            }
            ?>
        </div>
    </div>

    <?php include 'footer.html';
    mysqli_close($conn) ?>