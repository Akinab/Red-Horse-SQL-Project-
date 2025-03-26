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
    
    // ❌ VULNERABLE QUERY (Allows SQL Injection, including UNION-based attacks)
    // The input values are directly embedded in the SQL query without validation or sanitization.
    // An attacker can manipulate the query using ' UNION SELECT ... -- to extract data from other tables.
    $sql = "SELECT * FROM users WHERE username = '$user' AND password = '$pass'";
    $result = $conn->query($sql);

    // UNION-based SQL Injection works here because:
    // - If the number of selected columns matches, an attacker can retrieve additional data.
    // - Example payload: ' UNION SELECT 1,2,3,4 FROM users -- 
    // - This injects additional columns and returns unintended database content.

    while($row=mysqli_fetch_assoc($result)){
        echo "<pre>";
        var_dump($row);
        echo "</pre>";
    }
    echo "<br>";

    if ($result->num_rows > 0) {
        $_SESSION['user'] = $user;
        echo "Login successful!";
    } else {
        echo "Invalid credentials!";
    }
}

// This login system is susceptible to SQL injection due to direct user input in the query.
// To prevent this, use prepared statements with parameterized queries.

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
