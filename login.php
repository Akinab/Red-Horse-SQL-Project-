<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "testdb";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // Intentionally vulnerable query with SQLi
    $sql = "SELECT * FROM users WHERE username = '$user' AND password = '$pass'";

    // Display the SQL query
    echo "<br>Query: " . $sql;

    // Measure execution time
    $start_time = microtime(true);  // Start timer
    $result = $conn->query($sql);
    $end_time = microtime(true);    // End timer

    $execution_time = $end_time - $start_time;

    // Simulate SQLi effect: if the sleep occurs, login is successful
    if ($execution_time >= 5 || ($result && $result->num_rows > 0)) {
        $_SESSION['user'] = $user;
        echo "<br>✅ Login successful!";
    } else {
        echo "<br>❌ Invalid credentials!";
    }

    // Display execution time
    echo "<br>Execution time: " . $execution_time . " seconds";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Vulnerable Login</title>
</head>
<body>
    <form method="POST" action="">
        <label>Username:</label>
        <input type="text" name="username" required><br>
        <label>Password:</label>
        <input type="password" name="password" required><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
