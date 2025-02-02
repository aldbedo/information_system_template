<!-- DB CONNECTION USING PDO -->
<?php
$servername = "your_server_name";
$username = "your_username";
$password = "your_password";
$dataabse = "your_database";

try {
    // create new PDO object
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

    // set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // close conneection, display error message
    die("Connection failed: " . $e->getMessage());
}
?>
