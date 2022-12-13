<?php include 'header.html';
ini_set('display_errors', 1);
error_reporting(E_ALL);


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

$allowed_tables = array(
    "employees" => array(
        "abbr" => "e",
        "name" => "Employees",
        "fields" => array(
            "e.employeeNumber" => "Employee number",
            "e.jobTitle" => "Job Title",
            "e.firstName" => "First name",
            "e.lastName" => "Last name",
            "e.email" => "Email",
            "e.officeCode" => "Office code",
            "o.city" => "City",
            "o.country" => "Country",
            "o.state" => "State"
        )
    ),
    "offices" => array(
        "abbr" => "o",
        "name" => "Offices",
        "fields" => array(
            "o.officeCode" => "Office code",
            "o.city" => "City",
            "o.country" => "Country",
            "o.state" => "State",
            "o.postalCode" => "Postal code",
            "o.addressLine1" => "Address",
            "o.phone" => "Phone"
        )
    ),
);

if (!empty($_GET['table']) && key_exists($_GET['table'], $allowed_tables)) {
    $table = sanitize($_GET['table']);
} else {
    header('location: ?table=employees');
}

$search_key = $search_value = "";

if (!empty($_POST['search_key']) && key_exists($_POST['search_key'], $allowed_tables[$table]['fields'])) {
    $search_key = sanitize($_POST['search_key']);
}

if (!empty($_POST['search_value'])) {
    $search_value = "%" . sanitize($_POST['search_value']) . "%";
}

if (!empty($_POST['Submit']) && $search_key == "") {
    die("No attribute provided");
}
if (!empty($_POST['Submit']) && $search_value == "") {
    die("No keyword provided");
}
require_once '../dbconn.php'; ?>


<h1>Search from company database</h1>

<div class="checkbox_container">
    <a class="button<?php echo (!empty($_GET['table']) && $_GET['table'] != 'employees' ? " not-active" : ""); ?>"
        href="?table=employees">Search from employees</a>
    <a class="button<?php echo (!empty($_GET['table']) && $_GET['table'] != 'offices' ? " not-active" : ""); ?>"
        href="?table=offices">Search from offices</a>
</div>

<form method="POST">
    <div class="input_container">
        <label for="attr">Attribute</label>
        <select id="attr" name="search_key" required>
            <option value="" selected disabled>Select one...</option>
            <?php foreach ($allowed_tables[$table]['fields'] as $dbname => $text) {
                echo "<option value=\"$dbname\"";
                if (!empty($_POST['search_key']) && $_POST['search_key'] == $dbname) {
                    echo " selected";
                }
                echo ">" . $text . "</option>";
            }
            ?>
        </select>
    </div>
    <div class="input_container">
        <label for="keyword">Search keyword</label>
        <input id="keyword" type="text" name="search_value" placeholder="type in keyword"
            value="<?php echo (!empty($_POST['search_value']) ? sanitize($_POST['search_value']) : ""); ?>" required>
    </div>
    <input type="submit" value="Search">
</form>

<?php

if (!empty($_POST['keyword'])) {
    $keyword = mysqli_real_escape_string($conn, sanitize($_POST['keyword']));
} else {
    $keyword = null;
}

// if db connection is established
if ($conn) {
    mysqli_set_charset($conn, "utf8");

    if (!empty($search_key) && !empty($search_value)) {

        $query = "";
        if ($table == "employees") {
            $query = "SELECT e.*, b.firstName as supFirst, b.lastName as supLast, o.*
            FROM employees e 
            LEFT JOIN employees b ON b.employeeNumber = e.reportsTo 
            JOIN offices o ON e.officeCode = o.officeCode
            WHERE $search_key LIKE ?
            ORDER BY e.officeCode, e.employeeNumber";
        } else if ($table == "offices") {
            $query = "SELECT o.* 
            FROM offices o
            WHERE $search_key LIKE ?
            ORDER BY o.officeCode";
        } else {
            die("Invalid table name");
        }

        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "s", $search_value);
        mysqli_stmt_execute($stmt);
        $results = mysqli_stmt_get_result($stmt);

        echo "Found ";
        echo mysqli_num_rows($results) > 0 ? "<b>" . mysqli_num_rows($results) . "</b>" : "no";
        echo " matches from $table.<br>\n";

        $first = true;

        if ($table == "employees") {
            while ($row = mysqli_fetch_assoc($results)) {

                if ($first) {
                    print "<div class=\"scrollable\">\n    <table>\n";
                    print "        <tr><th>#</th><th>Name</th><th>Title</th><th>Email</th><th>Office</th><th>Supervisor</th></tr>\n";
                }
                print "        <tr>";
                print "<td>" . $row['employeeNumber'] . "</td>";
                print "<td>" . $row['lastName'] . ", " . $row['firstName'] . "</td>";
                print "<td>" . $row['jobTitle'] . "</td>";
                print "<td>" . $row['email'] . "</td>";
                print "<td>" . $row['city'] . "</td>";
                if ($row['supLast'] != null) {
                    print "<td>" . $row['supLast'] . ", " . $row['supFirst'] . "</td>";
                } else {
                    print "<td><i>none</i></td>";
                }

                print "</tr>\n";

                $first = false;
            }
        } else if ($table == "offices") {
            while ($row = mysqli_fetch_array($results)) {

                if ($first) {
                    print "<div class=\"scrollable\">\n    <table>\n";
                    print "        <tr><th>#</th><th>City</th><th>Phone</th><th>Address</th><th>State</th><th>Country</th><th>Postal code</th><th>Territory</th></tr>\n";
                }
                print "        <tr>";
                print "<td>" . $row['officeCode'] . "</td>";
                print "<td>" . $row['city'] . "</td>";
                print "<td>" . $row['phone'] . "</td>";
                print "<td>" . $row['addressLine1'] . " " . $row['addressLine2'] . "</td>";
                print "<td>" . ($row['state'] != "" ? $row['state'] : "<i>N/A</i>") . "</td>";
                print "<td>" . $row['country'] . "</td>";
                print "<td>" . $row['postalCode'] . "</td>";
                print "<td>" . $row['territory'] . "</td>";

                print "</tr>\n";

                $first = false;
            }
        } else {
            die("Invalid table name");
        }
        if (mysqli_num_rows($results) > 0) {
            print "    </table>\n</div>\n\n";
        }
    }

    mysqli_close($conn); // Close the connection.

} else {
    print "<p class=\"error\">Could not connect to the database:<br>" . mysqli_connect_error() . ".</p>";
}

include 'footer.html'; ?>