<?php include 'header.html';

require_once '../dbconn.php'; ?>


<h1>Employees</h1>

<?php

// if db connection is established
if ($conn) {

    $results = mysqli_query($conn, 'SELECT a.*, b.firstName as supFirst, b.lastName as supLast FROM employees a LEFT JOIN employees b ON b.employeeNumber = a.reportsTo ORDER BY a.officeCode, a.employeeNumber');

    $currentOffice = false;


    while ($row = mysqli_fetch_array($results)) {
        if (!$currentOffice || $currentOffice !== $row['officeCode']) {
            if ($currentOffice) {
                print "    </table>\n</div>\n\n";
            }
            print "<div class=\"faketablerow\">Office " . $row['officeCode'] . "</div>\n";
            print "<div class=\"scrollable\">\n    <table>\n";
            print "        <tr><th>#</th><th>Name</th><th>Title</th><th>Email</th><th>Supervisor</th></tr>\n";
        }
        print "        <tr>";
        print "<td>" . $row['employeeNumber'] . "</td>";
        print "<td>" . $row['lastName'] . ", " . $row['firstName'] . "</td>";
        print "<td>" . $row['jobTitle'] . "</td>";
        print "<td>" . $row['email'] . "</td>";
        if ($row['supLast'] != null) {
            print "<td>" . $row['supLast'] . ", " . $row['supFirst'] . "</td>";
        } else {
            print "<td><i>none</i></td>";
        }

        print "</tr>\n";

        $currentOffice = $row['officeCode'];
    }

    print "    </table>\n</div>\n\n";

    mysqli_close($conn); // Close the connection.

} else {
    print "<p class=\"error\">Could not connect to the database:<br>" . mysqli_connect_error() . ".</p>";
}

include 'footer.html'; ?>