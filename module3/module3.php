<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <title>Module 3 exercise</title>

    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=3">

    <meta name="author" content="Jasmin Lumme">
    <meta name="description" content="About workdays and salary">
    <meta name="keywords" content="work, salary, envy">

    <link rel="stylesheet" type="text/css" href="styles/mystyles.css">

</head>

<body>

    <main>

        <h1>About workdays and salary</h1>

        <p><?php

            $d1 = "Mondays";
            $d2 = "Tuesdays";
            $d3 = "Thursdays";
            $salary = 10000;

            print "I work on $d1, $d2 and $d3, and earn " . number_format($salary, 0, ".", ",") . " a <i><b>week</b></i>!<br><br>";
            print "My friend works only on $d3 but earns a lot more.";

        ?></p>

    </main>

</body>

</html>