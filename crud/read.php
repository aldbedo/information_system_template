<!-- PHP -->
<?php
session_start();

// check for session messages
if (isset($_SESSION['form_messages'])) {
    $successMessage = $_SESSION['form_messages']['success'] ?? '';
    $errorMessage = $_SESSION['form_messages']['error'] ?? '';
    unset($_SESSION['form_messages']);
}

// Cget data and errors
$formData = $_SESSION['form_data'] ?? [];
if (isset($_SESSION['form_errors'])) {
    $formErrors = $_SESSION['form_errors'];
    unset($_SESSION['form_errors']);
}

// create db connection
$servername = "localhost";
$username = "root";
$password = "";
$database = "data_monitoring";

try {
    // create connection
    $conn = new mysqli($servername, $username, $password, $database);

    // check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // default query to fetch all data
    $query = "SELECT * FROM employee ORDER BY id DESC";

    $id = $name = $email = "";
    $types = "";
    $params = [];

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $id = isset($_POST['id-filter']) ? trim($_POST['id-filter']) : "";
        $name = isset($_POST['name-filter']) ? trim($_POST['name-filter']) : "";
        $email = isset($_POST['email-filter']) ? trim($_POST['email-filter']) : "";

        $query = "SELECT * FROM employee WHERE 1=1";

        if (empty($id) && empty($name) && empty($email)) {
            $query = "SELECT * FROM employee ORDER BY id DESC";
        }

        if (!empty($id)) {
            $query .= " AND id = ?";
            $types .= "i";
            $params[] = $id;
        }

        if (!empty($name)) {
            $query .= " AND name LIKE ?";
            $types .= "s";
            $params[] = "%$name%";
        }
        
        if (!empty($email)) {
            $query .= " AND email LIKE ?";
            $types .= "s";
            $params[] = "%$email%";
        }

        // prepare the statement
        $stmt = $conn->prepare($query);

        if ($stmt === false) {
            throw new Exception("Error preparing statement" . $conn->error);
        }

        // bind parameters dynamically
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }

        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = $conn->query($query);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

?>

<!-- HTML -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Read</title>

    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Modal styling */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .close {
            width: 5%;
            height: 5%;
            margin-left: auto;
            font-size: 25px;
            cursor: pointer;
        }
    </style>

    <script>
        function openModal(id, name, email, birthday, civilStatus, mobile, address) {
            document.getElementById('edit-form-modal').style.display = 'block';
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-email').value = email;
            document.getElementById('edit-birthday').value = birthday;
            document.getElementById('edit-civil-status').value = civilStatus;
            document.getElementById('edit-mobile').value = mobile;
            document.getElementById('edit-address').value = address;
        }

        function closeModal() {
            document.getElementById('edit-form-modal').style.display = 'none';
        }
    </script>

</head>
<body> 
    
    <div class="container-fluid mt-4" style="max-width: 80%;">
        <?php
            if (!empty($successMessage)) {
                echo "<div class='alert alert-success'>" . htmlspecialchars($successMessage) . "</div>";
            } 

            if (!empty($errorMessage)) {
                echo "<div class='alert alert-danger'>" . htmlspecialchars($errorMessage) . "</div>";
            } 
        ?>

        <!-- filter -->
        <form class="mb-4" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST">
            <label for="id-filter">Search ID</label>
            <input type="text" id="id-filter" name="id-filter">
            <label for="name-filter">Search Name</label>
            <input type="text" id="name-filter" name="name-filter">
            <label for="email-filter">Search Email</label>
            <input type="text" id="email-filter" name="email-filter">
            <button class="btn btn-dark" type="submit">Filter</button>
        </form>

        <table class="table table-striped table-bordered"> 
            <thead class="thead-dark">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                    <th scope="col">Birthday</th>
                    <th scope="col">Civil Status</th>
                    <th scope="col">Mobile</th>
                    <th scope="col">Address</th>
                    <th scope="col">Date Created</th>
                    <th scope="col">Date Updated</th>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody id="data-table-body">
                <!-- Data will be populated here -->
                <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr><td>" . $row['id'] . "</td>";
                            echo "<td>" . $row['name'] . "</td>";
                            echo "<td>" . $row['email'] . "</td>";
                            echo "<td>" . $row['birthday'] . "</td>";
                            echo "<td>" . $row['civil_status'] . "</td>";
                            echo "<td>" . $row['mobile'] . "</td>";
                            echo "<td>" . $row['address'] . "</td>";
                            echo "<td>" . $row['date_created'] . "</td>";
                            echo "<td>" . $row['date_updated'] . "</td>";
                            echo "<td>
                                    <button class='btn btn-primary' onclick='openModal({$row['id']}, \"{$row['name']}\", \"{$row['email']}\", \"{$row['birthday']}\", \"{$row['civil_status']}\", \"{$row['mobile']}\", \"{$row['address']}\")'>Edit</button>
                                    <button class='btn btn-danger' onclick='deleteData({$row['id']})'>Delete</button>
                            </td></tr>";
                        }
                    }
                ?>
            </tbody>
        </table>

        <!-- Modal -->
        <div id="edit-form-modal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal()">&times;</span>
                <h2 class="mb-3">Edit Item</h2>
                <form action="update.php" method="POST">
                    <input type="hidden" name="id" id="edit-id">

                    <label for="edit-name">Name:</label>
                    <input type="text" id="edit-name" name="name" value="<?php echo $formData['name'] ?? ''; ?>">
                    <span class="text-danger"><?php echo $formErrors['nameErr'] ?? ''; ?></span>
                    <br><br>

                    <label for="edit-email">Email:</label>
                    <input type="email" id="edit-email" name="email" value="<?php echo $formData['email'] ?? ''; ?>">
                    <br><br>

                    <label for="edit-birthday">Birthday:</label>
                    <input type="date" id="edit-birthday" name="birthday" value="<?php echo $formData['email'] ?? ''; ?>"></input>
                    <br><br>

                    <label for="edit-civil-status">Civil Status:</label>
                    <select id="edit-civil-status" name="civil-status" required>
                        <option value="">Select Civil Status</option>
                        <option value="Single" <?php echo (isset($formData['civil-status']) && $formData['civil-status'] == 'Single') ? 'selected' : ''; ?>>Single</option>
                        <option value="Married" <?php echo (isset($formData['civil-status']) && $formData['civil-status'] == 'Mrried') ? 'selected' : ''; ?>>Married</option>
                        <option value="Divorced" <?php echo (isset($formData['civil-status']) && $formData['civil-status'] == 'Divorced') ? 'selected' : ''; ?>>Divorced</option>
                    </select>
                    <br><br>

                    <label for="edit-mobile">Mobile Number:</label>
                    <input type="text" name="mobile" id="edit-mobile" value="<?php echo $formData['mobile'] ?? ''; ?>"></input>
                    <br><br>

                    <label for="edit-address">Address:</label>
                    <input type="text" name="address" id="edit-address" value="<?php echo $formData['address'] ?? ''; ?>"></input>
                    <br><br>

                    <button class="btn btn-primary" type="submit">Save</button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        <?php if (!empty($formErrors)): ?>
            document.getElementById('edit-form-modal').style.display = 'block';
        <?php endif; ?>
    </script>
</body>
</html>