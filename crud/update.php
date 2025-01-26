<?php
    session_start();

    // helper function 
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

    // initialize variables
    $nameErr = $emailErr = $birthdayErr = $civilStatusErr = $mobileErr = $addressErr = "";
    $succ_msg = $err_mgs ="";
    $errFlag = false;

    if ($_SERVER["REQUEST_METHOD"] === "POST") {

        // validate inputs
        // id
        if (empty($_POST["id"])) {
            $errFlag = true;
        } else {
            $id = test_input($_POST['id']);
        }

        // name
        if (empty($_POST["name"])) {
            $nameErr = "*Required";
            $errFlag = true;
        } else {
            $name = test_input($_POST['name']);

            if (!preg_match("/^[\p{L} '-]+$/u", $name)) {
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

        // proceed to update item if there are no errors
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

                // prepare query to INSERT
                $query = "UPDATE employee SET name = :name, email = :email, birthday = :birthday, civil_status = :civil_status, mobile = :mobile, address = :address, date_updated = NOW() WHERE id = :id";

                // execute query
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->bindParam(':birthday', $birthday, PDO::PARAM_STR);
                $stmt->bindParam(':civil_status', $civilStatus, PDO::PARAM_STR);
                $stmt->bindParam(':mobile', $mobile, PDO::PARAM_STR);
                $stmt->bindParam(':address', $address, PDO::PARAM_STR);
                $stmt->execute();   

                // success message
                $succ_msg = "Data Updated Successfully!";
                $_SESSION['form_messages'] = ['success' => $succ_msg];
                unset($_SESSION['form_data']);
            } catch (PDOException $e) {
                // error message
                $err_mgs = "Error updating record: " . $e->getMessage();
            }
        } else {
            $_SESSION['form_data'] = $_POST;
            $_SESSION['form_errors'] = [
                'nameErr' => $nameErr,
                'emailErr' => $emailErr,
                'birthdayErr' => $birthdayErr,
                'civilStatusErr' => $civilStatusErr,
                'mobileErr' => $mobileErr,
                'addressErr' => $addressErr
            ];
            $_SESSION['form_messages'] = [
                'success' => $succ_msg,
                'error' => $err_msg];
        }
        header("Location: read.php");
        exit;
    }
?>

<?php //echo htmlspecialchars($_SERVER['PHP_SELF']) ?>