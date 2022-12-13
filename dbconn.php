<?php

function sanitize($input) {
    // if input is an array, loop through its elements
    if (gettype($input) == 'array') {
        $returnable_array = array();

        foreach ($input as $element) {
            $element = strip_tags($element);
            array_push($returnable_array, trim(htmlspecialchars($element, ENT_COMPAT, 'UTF-8')));
        }

        return $returnable_array;
    }

    // if input is not an array, treat it as a string
    $stripped = strip_tags($input);
    return trim(htmlspecialchars($stripped, ENT_COMPAT, 'UTF-8'));
}

// removed actual credentials
$host = "not-secure";
$username = "not-secure";
$password = "not-secure";
$db = "not-secure";

$conn = @mysqli_connect($host, $username, $password, $db) or die('Could not connect to MySQL');

mysqli_set_charset($conn, 'utf8');