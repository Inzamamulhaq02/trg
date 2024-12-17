<?php
// include 'db_connection.php'; // Database connection
require 'utils.php';
if (isset($_GET['user_id'])) {
    $user_id = intval($_GET['user_id']); // Sanitize input
extract(getBasicDetails($user_id));
echo "basic details<br>";
echo $user_id."<br>";
echo $name."<br>";
echo $email."<br>";
echo $phone."<br>";
echo $registered_at."<br>";

//  Example user ID

// Call the getActivePlans function
$activePlans = getActivePlans($user_id);


// Display the results
if (!empty($activePlans)) {
    echo "<h2>Active Plans </h2>";
    echo "<table border='1' cellspacing='0' cellpadding='5'>";
    echo "<tr>
            <th>Plan Name</th>
            <th>Amount</th>
            <th>Status</th>
            <th>Months Paid</th>
            <th>Pending Amount</th>
            <th>Joined At</th>
            <th>End Date</th>
          </tr>";
    
    foreach ($activePlans as $plan) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($plan['name']) . "</td>";
        echo "<td>" . number_format($plan['amount'], 2) . "</td>";
        echo "<td>" . htmlspecialchars($plan['status']) . "</td>";
        echo "<td>" . htmlspecialchars($plan['months_paid']) . "</td>";
        echo "<td>" . number_format($plan['pending_amount'], 2) . "</td>";
        echo "<td>" . htmlspecialchars($plan['joined_at']) . "</td>";
        echo "<td>" . htmlspecialchars($plan['end_date']) . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "<p>No active plans found for User ID: {$user_id}.</p>";
}

echo "<h1>payments</h1>";
// Replace with the actual user ID
$selected_plan_id = isset($_POST['plan_id']) ? $_POST['plan_id'] : null;
$pendingPayments = [];

// Fetch plans for the user
$plans = getUserplannames($user_id);


// If a plan is selected, fetch the pending payments
if ($selected_plan_id) {
    $pendingPayments = getPendingPayments($user_id, $selected_plan_id);
}


 
} else {
    echo "No user ID provided.";
}

// Handle "Pay" button click
if (isset($_POST['pay_payment_id'])) {
    $payment_id = $_POST['pay_payment_id'];
    if (makepayment($payment_id)) {
        echo "<script>alert('Payment successful!');</script>";
    } else {
        echo "<script>alert('Payment failed. Please try again.');</script>";
    }
}
?>




<!DOCTYPE html>
<html>
<head>
    <title>Pending Payments</title>
</head>
<body>
    <h2>Select a Plan to View Pending Payments</h2>

    <!-- Plan Dropdown Form -->
    <form method="POST" action="">
        <label for="plan_id">Select Plan:</label>
        <select name="plan_id" id="plan_id" required>
            <option value="">-- Select a Plan --</option>
            <?php foreach ($plans as $plan): ?>
                <option value="<?php echo $plan['scheme_id']; ?>" 
                    <?php echo ($selected_plan_id == $plan['scheme_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($plan['scheme_name']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">View Pending Payments</button>
    </form>

    <!-- Display Pending Payments -->
    <?php if ($selected_plan_id): ?>
        <h3>Pending Payments for Selected Plan</h3>
        <?php if (!empty($pendingPayments)): ?>
            <table border="1" cellpadding="5" cellspacing="0">
                <tr>
                    <th>Payment ID</th>
                    <th>Due Month</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
                <?php foreach ($pendingPayments as $payment): ?>
                    <tr>
                        <td><?php echo $payment['payment_id']; ?></td>
                        <td><?php echo $payment['due_month']; ?></td>
                        <td><?php echo $payment['amount']; ?></td>
                        <td><?php echo ucfirst($payment['status']); ?></td>
                        <td>
                            <!-- Pay Button Form -->
                            <form method="POST" action="" style="display:inline;">
                                <input type="hidden" name="pay_payment_id" value="<?php echo $payment['payment_id']; ?>">
                                <button type="submit" onclick="return confirm('Are you sure you want to pay this payment?');">Pay</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        <?php else: ?>
            <p>No pending payments found for this plan.</p>
        <?php endif; ?>
    <?php endif; ?>
</body>
</html>
