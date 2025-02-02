<!-- PHP SCRIPT -->
<?php

    // helper function to prepare inputs
    function test_input($data, $type = 'string') {
        $data = trim($data);
        $data = stripslashes($data);
    $data = htmlspecialchars($data);

    if ($type === 'int') {
        if (is_numeric($data)) {
            return(int)$data;
        } else {
            return 0;
        }
    }

    return $data;
    }

    $name = $email = $birthday = $civilStatus = $mobile = $address = "";

    $nameErr = $emailErr = $birthdayErr = $civilStatusErr = $mobileErr = $addressErr = "";

    $succ_msg = $err_mgs ="";
    $errFlag = false;

    // validate and sanitize inputs
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        
        // name
        if (empty($_POST["name"])) {
            $nameErr = "*Required";
            $errFlag = true;
        } else {
            $name = test_input($_POST['name']);

            if (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
                $nameErr = "Only letters and white space allowed.";
                $errFlag = true;
            }
        }

        // email
        if (empty($_POST["email"])) {
            $emailErr = "*Required";
            $errFlag = true;
        } else {
            $email = test_input($_POST['email']);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emailErr = "Invalid email format.";
                $errFlag = true;
            }
        }
        
        // birthday
        if (empty($_POST["birthday"])) {
            $birthdayErr = "*Required";
            $errFlag = true;
        } else {
            $birthday = test_input($_POST['birthday']);
        }

        // civil status
        if (empty($_POST['civil-status'])) {
            $civilStatusErr = "*Required";
            $errFlag = true;
        } else {
            $civilStatus = test_input($_POST['civil-status']);
        }

        // mobile number
        if (empty($_POST['mobile'])) {
            $mobileErr = "*Required";
            $errFlag = true;
        } else {
            $mobile = test_input($_POST['mobile']);
    
            if (!preg_match("/^(\+?[0-9]{13}|[0-9]{11})$/", $mobile)) {
                $mobileErr = "Invalid mobile number format";
                $errFlag = true;
            }
        }

        // address
        if (empty($_POST["address"])) {
            $addressErr = "*Required";
            $errFlag = true;
        } else {
            $address = test_input($_POST['address']);

            if (!preg_match("/^[a-zA-Z0-9\s,.'-\/]*$/", $address)) {
                $addressErr = "Invalid address format.";
                $errFlag = true;
            }
        }

        // insert data into database
        if (!$errFlag) {

            // create db connection
            $servername = "localhost";
            $username = "root";
            $password = "";
            $database = "data_monitoring";

            try {
                // create new PDO object
                $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);

                // set the PDO error mode to exception
                $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                // close conneection, display error message
                die("Connection failed: " . $e->getMessage());
            }

            // prepare query to INSERT
            $query = "INSERT INTO employee (name, email, birthday, civil_status, mobile, address) VALUES (:name, :email, :birthday, :civil_status, :mobile, :address)";

            // execute query
            try {
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':birthday', $birthday, PDO::PARAM_STR);
                $stmt->bindParam(':civil_status', $civilStatus, PDO::PARAM_STR);
                $stmt->bindParam(':mobile', $mobile, PDO::PARAM_STR);
                $stmt->bindParam(':address', $address, PDO::PARAM_STR);
                $stmt->execute();

                // success message
                $succ_msg = "Data Submitted Successfully";
            } catch (PDOException $e) {

                // error message
                $err_mgs = $e->getMessage();
            }
        }
    }
?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create</title>

    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>
<body>
    <h1>Create Data</h1>

    <?php
        if (!empty($succ_msg)) {
            echo '<div class="alert alert-success">' . htmlspecialchars($succ_msg) . "</div>";
        }

        if (!empty($err_msg)) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($err_msg) . "</div>";
        }
    ?>

    <!-- FORM (POST) -->
    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">

        <!-- NAME (TEXT) -->
        <div class="text-danger"> <?= $nameErr ?></div>
        <label for="name">Name</label>
        <input class="form-control mb-3" type="text" id="name" name="name" value="<?php htmlspecialchars($name) ?>">

        <!-- EMAIL (EMAIL) -->
        <div class="text-danger"> <?= $emailErr ?></div>
        <label for="email">Email</label>
        <input class="form-control mb-3" type="text" id="email" name="email" value="<?php htmlspecialchars($email) ?>">

        <!-- BIRTHDAY (DATE) -->
        <div class="text-danger"> <?= $birthdayErr ?></div>
        <label for="birthday">Birthday</label>
        <input class="form-control mb-3" type="date" id="birthday" name="birthday" value="<?php htmlspecialchars($birthday) ?>">

        <!-- CIVIL STATUS (SELECT) -->
        <div class="text-danger"> <?= $civilStatusErr ?></div>
        <label for="civil-status">Civil Status</label>
        <select class="form-control mb-3" id="civil-status" name="civil-status">
            <option value="">Select Civil Status</option>
            <option value="Single" <?php ($civilStatus == "Single") ? 'selected' : '' ?>>Single</option>
            <option value="Married" <?= ($civilStatus == "Married") ? 'selected' : '' ?>>Married</option>
            <option value="Divorced" <?= ($civilStatus == "Divorced") ? 'selected' : '' ?>>Divorced</option>
        </select>

        <!-- MOBILE NUMBER (TEXT) -->
        <div class="text-danger"> <?= $mobileErr ?></div>
        <label for="mobile">Mobile Number</label>
        <input class="form-control mb-3" type="text" id="mobile" name="mobile" value="<?php htmlspecialchars($mobile) ?>">

        <!-- ADDRESS (TEXT) -->
        <div class="text-danger"> <?= $addressErr ?></div>
        <label for="address">Address</label>
        <input class="form-control mb-3" type="text" id="address" name="address" value="<?php htmlspecialchars($address) ?>">

        <!-- BUTTON (SUBMIT) -->
        <button class="" type="submit" id="submit" name="submit">
            Submit
        </button>
    </form>

    <script>
        // Populate the form fields with existing data 
        document.getElementById("name").value = "<?php echo htmlspecialchars($name); ?>";
        document.getElementById("email").value = "<?php echo htmlspecialchars($email); ?>";
        document.getElementById("birthday").value = "<?php echo htmlspecialchars($birthday); ?>";
        document.getElementById("civil-status").value = "<?php echo htmlspecialchars($civilStatus); ?>";
        document.getElementById("mobile").value = "<?php echo htmlspecialchars($mobile); ?>";
        document.getElementById("address").value = "<?php echo htmlspecialchars($address); ?>";
    </script>
</body>
</html>