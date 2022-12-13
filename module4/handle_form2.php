<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>Your Feedback</title>
</head>

<body>
    <?php // Script 3.3 handle_form.php 

ini_set('display_errors', 1);
error_reporting(E_ALL);

// This page receives the data from feedback.html.
// It will receive: title, name, email, response, comments, and submit in $_POST.

// Create shorthand versions of the variables:
$title = $_POST['title'];
$name = $_POST['name'];
$favcolour = $_POST['favcolour'];
$favfood = $_POST['favfood'];
$response = $_POST['response'];
$comments = $_POST['comments'];

// Print the received data:
print "<p>Thank you, $title $name, for your comments.</p>
<p>You stated that your favourite colour is $favcolour and your favourite food is $favfood.</p>
<p>You stated that you found this example to be '$response' and added:<br>$comments</p>";

?>
</body>

</html>