<?php
require_once('../dbconn.php');
/*
1 = school
2 = sport
3 = deadlines
4 = travel
5 = friends

    INSERT INTO `php2022jasmin_categories` (`category_id`, `hex`, `title`) VALUES
    (3, '#EFA9FF', 'Travel');

    INSERT INTO `php2022jasmin_events` (`event_id`, `title`, `start`, `end`, `wholeday`, `category_id`) VALUES
    (3, 'Trip to Pori', '2022-11-25 00:00:00', '2022-11-27 23:00:00', 1, 4);

    DELETE FROM `php2022jasmin_events` WHERE `event_id` = 4;
*/
$query = "
INSERT INTO `php2022jasmin_categories` (`category_id`, `hex`, `title`) VALUES
(5, '#FFD7A9', 'Friends');
INSERT INTO `php2022jasmin_events` (`event_id`, `title`, `start`, `end`, `wholeday`, `category_id`) VALUES
    (2, 'Dressage competition', '2022-12-17 10:00:00', '2022-12-17 18:00:00', 0, 2), (4, 'New Year\'s celebration', '2022-12-31 18:00:00', '2022-12-31 23:00:00', 0, 5);
    ";

if (mysqli_multi_query($conn, $query)) {
    echo $query . "<br>";
    echo '<h1>OK!</h1>';
    echo mysqli_affected_rows($conn);
} else {
    echo '<p style="color: red;">Something went wrong: ' . mysqli_error($conn) . '</p>';
}

mysqli_close($conn);