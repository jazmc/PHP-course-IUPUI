<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="author" content="Jasmin Lumme" />
    <meta name="description" content="simple PHP form" />

    <title>Forum Posting Results</title>

    <link type="text/css" rel="stylesheet" href="styles/module5styles.css" />
</head>

<body>
    <main>
        <h1>Form results</h1>
        <?php // Script 5.2 - handle_post.php
			/* This script receives five values from posting.html:
			first_name, last_name, email, posting, submit */

			ini_set('display_errors', 1);
			error_reporting(E_ALL);

			// Address error management, if you want.
			if(!isset($_POST) || count($_POST) < 1) {
				die("Error: Wrong method");
			}

			function sanitize($input) {
				$stripped = strip_tags($input);
				return htmlspecialchars($stripped, ENT_COMPAT, 'UTF-8');
			}
 
			// Get the values from the $_POST array:
			$first_name = sanitize($_POST['first_name']);
			$last_name = sanitize($_POST['last_name']);
			$email = sanitize($_POST['email']);
			$city = sanitize($_POST['city']);
			$education = sanitize($_POST['education']);

			// Create a full name variable:
			$name = $first_name . ' ' . $last_name;

			// Print a message:
			print "<div>Welcome to the site, $name of <b><i>$city</i></b>.<br><br>
			Your email of $email has been registered.<br><br>
			Your education is:<br>
 			<p>$education</p></div>";

		?>
    </main>
</body>

</html>