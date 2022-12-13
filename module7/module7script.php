<?php
/*
/	Hi! I hope you don't mind that I took some artistic liberties with this assignment!
/	I wanted to experiment a little with foreach loops and how to utilize them in the
/	code. I admit the practices might be "too much" for the simplicity of this form,
/	but this is how I would do when there would be a lot of variables and a lot of 
/	$_POST content entering the page. Also I tried to take security into account
/	as well as I could :) 	- Jasmin
*/


// error management
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
	die("Error: Wrong method");
}

// function to strip html tags, convert html chars to entities, and trim unwanted whitespace:
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

// initialize "wanted" variable names
$wanted_variables = array("first_name", "fav_days", "fav_seasons");

// Get the values from the $_POST array & assign them to variables dynamically
foreach ($_POST as $key => $value) {
	// process only "wanted" variables to prevent accidentally overwriting any existing variables & to spare memory
	if (in_array($key, $wanted_variables)) {
		// sanitize EVERY input (even checkboxes), manipulating DOM / input values is easy via developer tools!
		$$key = sanitize($value);
	}
}

// check that critical input values exist
foreach ($wanted_variables as $name) {
	if (!isset($$name) || empty($$name)) {
		// allow missing fav_days or fav_seasons to match the assignment's goal :)
		if ($name != "fav_days" && $name != "fav_seasons") {
			// try submitting whitespace for the first name to execute this code:
			die("Error: A critical variable is empty.");
		}
	}
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Jasmin Lumme">
    <meta name="description" content="simple PHP form">
    <meta name="keywords" content="PHP, form, example">

    <title>Post results</title>

    <link type="text/css" rel="stylesheet" href="styles/module7styles.css">
</head>

<?php
// a little extra creativity :) 
print(empty($fav_days) && empty($fav_seasons) ? "<body class=\"boring\">\n" : "<body>\n");
?>
<main>
    <h1><?php
		// conditional title
		print(empty($fav_days) && empty($fav_seasons) ? "&#x1F480; Boring... &#x1F480;" : ((empty($fav_days) || empty($fav_seasons)) ? "&#x1F914; Not quite there yet... &#x1F914;" : "&#x1F389; Results &#x1F389;"));
		?></h1>

    <?php

	// error messages:
	if (empty($fav_days) && empty($fav_seasons)) {
		print "<p class=\"error\">Well, aren't you a little ray of sunshine..... You really don't like any days of the week or seasons?<br>
					You have to choose something, $first_name. If you don't have a favorite, maybe you should try picking the least horrible option...?</p>\n";
	} else if (empty($fav_days) || empty($fav_seasons)) {
		print "<p class=\"error\">You have to choose at least one ";
		print(empty($fav_days) ? "day of the week" : "season");
		print ", $first_name.</p>\n";

		// if everything is okay:
	} else {
		print "<p>Welcome to the site, <b>$first_name</b>.</p>\n        <p>";
		print "As your favorite ";
		// plural or singular handling
		print(count($fav_days) > 1 ? "days" : "day");
		print " of the week, you chose ";

		// loop day(s):
		foreach ($fav_days as $day) {
			// choose separator for not-first elements:
			if ($day != reset($fav_days) && $day != end($fav_days)) {
				print ", ";
			} else if ($day != reset($fav_days)) {
				print " and ";
			}
			// element output
			print "<i>$day</i>";
		}

		print ".</p>\n";

		print "        <p>As your favorite ";
		// plural or singular handling
		print(count($fav_seasons) > 1 ? "seasons" : "season");
		print ", you chose ";

		// loop season(s):
		foreach ($fav_seasons as $season) {
			// choose separator for not-first elements:
			if ($season != reset($fav_seasons) && $season != end($fav_seasons)) {
				print ", ";
			} else if ($season != reset($fav_seasons)) {
				print " and ";
			}
			// element output
			print "<i>$season</i>";
		}

		print ".</p>\n";
	}

	// print "back to form" -button if something was not right:
	if (empty($fav_days) || empty($fav_seasons)) {
		print "        <a href=\"module7form.html\" class=\"button\">Try again</a>\n";
	}

	?>
</main>
</body>

</html>