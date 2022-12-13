<?php
// error management
ini_set('display_errors', 1);
error_reporting(E_ALL);

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

include 'header.html'; ?>

        <h1>Module 8 form example</h1>

        <p>Please complete this form to access the site:</p>

        <form method="POST">

            <fieldset>
                <legend>Name</legend>

                <div class="input_container">
                    <label for="first_name">First name:</label>
                    <input id="first_name" type="text" name="first_name" size="20" value="<?php
                                                                                            if (isset($_POST['first_name'])) {
                                                                                                echo sanitize($_POST['first_name']);
                                                                                            }
                                                                                            ?>">
                </div>
                <?php if (isset($_POST['first_name']) && $_POST['first_name'] == "") { ?>
                <div class="error">Please enter your first name</div>
                <?php } ?>

                <div class="input_container">
                    <label for="last_name">Last name:</label>
                    <input id="last_name" type="text" name="last_name" size="20" value="<?php
                                                                                        if (isset($_POST['last_name'])) {
                                                                                            echo sanitize($_POST['last_name']);
                                                                                        }
                                                                                        ?>">
                </div>
                <?php if (isset($_POST['last_name']) && $_POST['last_name'] == "") { ?>
                <div class="error">Please enter your last name</div>
                <?php } ?>

            </fieldset>

            <input type="submit" name="submit" value="Submit">
        </form>

        <?php if (isset($_POST['first_name']) && isset($_POST['last_name']) && $_POST['first_name'] != "" && $_POST['last_name'] != "") { ?>

        <p>Welcome to the site, <?php echo sanitize($_POST['first_name']) . " " . sanitize($_POST['last_name']); ?>!</p>

        <?php } ?>

<?php include 'footer.html'; ?>