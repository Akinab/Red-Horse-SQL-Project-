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

$remarks = "";  // Store remarks to display below the form

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    
    // Intentionally vulnerable query with time-based SQL injection simulation
    $sql = "SELECT * FROM users WHERE username = '$user' AND password = '$pass' OR IF(1=1, SLEEP(5), 0) -- ";

    // Measure execution time
    $start_time = microtime(true);
    $result = $conn->query($sql);
    $end_time = microtime(true);

    $execution_time = $end_time - $start_time;

    if ($result && $result->num_rows > 0) {
        $_SESSION['user'] = $user;
        $remarks = "<div class='success'>✅ Login successful!</div>";
    } else {
        $remarks = "<div class='error'>❌ Invalid credentials!</div>";
    }

    // Display execution time to show the delay
    $remarks .= "<div class='info'>⏱️ Execution time: " . number_format($execution_time, 2) . " seconds</div>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vulnerable Login</title>
    <style>
        /* Overall Page Styling */
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, maroon, yellow);
            color: #333;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;  /* Display form and remarks in column layout */
        }

        .container {
            background: #fff;
            width: 400px;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        h2 {
            color: maroon;
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-weight: bold;
            margin-bottom: 8px;
            text-align: left;
        }

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 12px;
            background: maroon;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: 0.3s;
        }

        button:hover {
            background: darkred;
        }

        /* Remarks section below the form */
        .remarks-container {
            background: #fff;
            width: 400px;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            margin-top: 20px;
            text-align: center;
        }

        .success {
            color: green;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .error {
            color: red;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .info {
            color: #555;
            font-weight: bold;
        }

        /* Responsive Design */
        @media (max-width: 480px) {
            .container, .remarks-container {
                width: 90%;
            }
        }
    </style>
</head>
<body>

<!-- Centered Login Form -->
<div class="container">
    <h2>Login Form</h2>
    <form method="POST" action="">
        <label>Username:</label>
        <input type="text" name="username" placeholder="Enter username" required>
        
        <label>Password:</label>
        <input type="password" name="password" placeholder="Enter password" required>
        
        <button type="submit">Login</button>
    </form>
</div>

<!-- Remarks Section Below the Form -->
<?php if ($remarks): ?>
    <div class="remarks-container">
        <?= $remarks ?>
    </div>
<?php endif; ?>

</body>
</html>
