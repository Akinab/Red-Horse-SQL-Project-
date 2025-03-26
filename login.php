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

$remarks = "";               // Store general remarks
$execution_time = 0;         // Store execution time
$union_results = "";         // Store UNION results

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];
    
    // Check which SQLi type is selected
    $use_union = ($_POST['use_union'] == "1");

    // --- 🚨 UNION-based SQL Injection ---
    $sql_union = "SELECT * FROM users WHERE username = '$user' AND password = '$pass'";
    
    // We validate the number of columns using ' ORDER BY 1 --  until it runs into an error
    // Using this we could modify the SQL code ' UNION SELECT NULL, NULL, NULL FROM users -- depending on the number of columns
    // By injecting this SQL code to the query ' UNION SELECT username, password FROM users -- 
    // We transform it to this code underneath to display all credentials inside the database
    // "SELECT * FROM users WHERE username = '$user' AND password = '$pass' UNION SELECT username, password FROM users -- ";

    // --- ⏱️ Time-based SQL Injection ---
    $sql_time =  "SELECT * FROM users WHERE username = '$user' AND password = '$pass' 
                 OR IF(1=1, SLEEP(5), 0) -- ";

    //  By injecting this SQL code to the query ' OR IF(1=1, SLEEP(5), 0) --
    // The system would load for 5 seconds if there is a credential in the database that would result in a true statement
    // We transform the code to  "SELECT * FROM users WHERE username = '$user' AND password = '$pass' OR IF(1=1, SLEEP(5), 0) -- ";



    // Toggle between SQL injection types
    $sql = $use_union ? $sql_union : $sql_time;

    // Measure execution time for Time-based SQLi
    $start_time = microtime(true);
    $result = $conn->query($sql);
    $end_time = microtime(true);

    $execution_time = $end_time - $start_time;

    // Display results based on the selected attack type
    if ($result && $result->num_rows > 0) {
        $_SESSION['user'] = $user;
        $remarks = "<div class='success'>✅ Login successful!</div>";

        // Display UNION-based results only when UNION SQLi is selected
        if ($use_union) {
            while ($row = mysqli_fetch_assoc($result)) {
                $union_results .= "<pre>" . print_r($row, true) . "</pre>";
            }
        }
    } else {
        $remarks = "<div class='error'>❌ Invalid credentials!</div>";
    }

    // Display execution time only for Time-based SQLi
    if (!$use_union) {
        $remarks .= "<div class='info'>⏱️ Execution time: " . number_format($execution_time, 2) . " seconds</div>";
    }
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

        input, select {
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

        pre {
            background: #f4f4f4;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            text-align: left;
            overflow: auto;
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

        <label>SQL Injection Type:</label>
        <select name="use_union">
            <option value="0">Time-Based SQLi</option>
            <option value="1">Union-Based SQLi</option>
        </select>
        
        <button type="submit">Login</button>
    </form>
</div>

<!-- Remarks Section Below the Form -->
<?php if (!empty($remarks)): ?>
    <div class="remarks-container">
        <?= $remarks ?>
    </div>
<?php endif; ?>

<!-- Display UNION-based SQLi results only when selected -->
<?php if (!empty($union_results)): ?>
    <div class="remarks-container">
        <h3>🛠️ UNION-based SQLi Results:</h3>
        <?= $union_results ?>
    </div>
<?php endif; ?>

</body>
</html>
