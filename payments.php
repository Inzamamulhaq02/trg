<?php
// Database connection
require 'dbconnect.php';

// Fetch user schemes linked with users and savings schemes
function fetch_user_schemes($conn) {
    $schemes = [];
    $sql = "
        SELECT users.id, users.name AS us, saving.scheme_name, saving.plan_amount 
        FROM user_schemes us
        JOIN users u ON us.user_id = u.user_id
        JOIN savings_schemes ss ON us.scheme_id = ss.scheme_id
        WHERE us.status = 'active'
    ";
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $schemes[] = $row;
            echo $row;
        }
    }
    return $schemes;
}

// Handle form submission to create payment records
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_scheme_id = $_POST['user_scheme_id'];
    $due_month = $_POST['due_month'];
    $amount = $_POST['amount'];
    $status = $_POST['status'];

    $sql = "SELECT * FROM users";
    $result = $conn->query($sql);
    // Insert the payment record
    $stmt = $conn->prepare("INSERT INTO payments (user_scheme_id, due_month, amount, status, payment_date) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("isds", $user_scheme_id, $due_month, $amount, $status);

    if ($stmt->execute()) {
        echo "<p style='color:green;'>Payment record created successfully!</p>";
    } else {
        echo "<p style='color:red;'>Error: " . $stmt->error . "</p>";
    }
    $stmt->close();
}

// Fetch active user schemes for the dropdown
$user_schemes = fetch_user_schemes($conn);



?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Payment Record</title>
</head>
<body>
    <h1>Create Payment Record</h1>

    <form method="POST" action="">
        <label for="user_scheme_id">User Scheme:</label>
        <select name="user_scheme_id" required>
            <option value="">Select User and Scheme</option>
           
            <?php foreach ($user_schemes as $scheme): ?>
                <option value="<?php echo $result['id']; ?>">
                    <?php echo "User: " . $result['name']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <br><br>

        <label for="due_month">Due Month:</label>
        <input type="date" name="due_month" required>
        <br><br>

        <label for="amount">Amount:</label>
        <input type="number" step="0.01" name="amount" required>
        <br><br>

        <label for="status">Status:</label>
        <select name="status" required>
            <option value="paid">Paid</option>
            <option value="pending">Pending</option>
        </select>
        <br><br>

        <button type="submit">Create Payment</button>
    </form>
</body>
</html>

<?php $conn->close(); ?>
