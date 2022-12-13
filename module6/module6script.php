<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="author" content="Jasmin Lumme">
    <meta name="description" content="simple PHP form">
    <meta name="keywords" content="PHP, form, example">

    <title>Registration results</title>

    <link type="text/css" rel="stylesheet" href="styles/module6styles.css">
</head>

<body>
    <main>
        <h1>Results</h1>
        <?php

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

			function age($dob){
				if(!empty($dob)){
					$birthdate = new DateTime($dob);
					$today   = new DateTime('today');
					$age = $birthdate->diff($today)->y;
					return $age;
				}else{
					return "D.O.B. invalid";
				}
			}
 
			// Get the values from the $_POST array:
			$first_name = sanitize($_POST['first_name']);
			$last_name = sanitize($_POST['last_name']);
			$dob = sanitize($_POST['dob']);
			$username = sanitize($_POST['username']);
		
			$name = $first_name . ' ' . $last_name;
			$age = age($dob);


			$everything_ok = true;

			if(strlen($username) < 6) {
				$everything_ok = false;
				print "<div class=\"error\">Sorry, your username needs to be at least 6 characters, $name!</div>";
			}

			if($age <= 16) {
				$everything_ok = false;
				print "<div class=\"error\">Sorry, you are too young, $name!</div>";
			}

			if($everything_ok) {
				print "<div>Welcome to the site, $name. You are $age years old, so you qualify.<br><br>
				Your username of <span style=\"color:#ba372a\">$username</span> has been registered.</div>";
			}

		?>
    </main>
</body>

</html>