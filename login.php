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
    
    // VULNERABLE QUERY (Allows SQL Injection)
    $sql = "SELECT * FROM users WHERE username = '$user' AND password = '$pass'";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $_SESSION['user'] = $user;
        echo "Login successful!";
    } else {
        echo "Invalid credentials!";
    }
}

//This login system is susceptible to SQL injection due to the direct embedding of user inputs into the SQL query.
//Make this a secure login


?>

<!DOCTYPE html>
<html>
<head>
    <title>Vulnerable Login</title>
</head>
<body>
    <form method="POST" action="">
        <label>Username:</label>
        <input type="text" name="username"><br>
        <label>Password:</label>
        <input type="password" name="password"><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>