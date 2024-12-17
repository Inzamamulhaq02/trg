<?php 
require 'dbconnect.php';
function getUserCount(){
    $query = "SELECT COUNT(*) AS user_count FROM users";
    global $conn;
    // Execute the query
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['user_count'];
}
function newUserscount(){
    global $conn;
    $currentYear = date('Y');
    $currentMonth = date('m');

    // SQL query to count new users who joined this month
    $query = "
        SELECT COUNT(*) AS user_count 
        FROM users 
        WHERE YEAR(registered_at) = '$currentYear' AND MONTH(registered_at) = '$currentMonth'
    ";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
        return $row['user_count']; 
}


function getPendingUsersForCurrentMonth() {
    global $conn;

    

    // SQL query to count users with pending status for the current month
    $query = "
       SELECT COUNT(DISTINCT us.user_id) AS unique_pending_users
FROM payments p
JOIN user_schemes us ON p.user_scheme_id = us.user_scheme_id
JOIN users u ON us.user_id = u.user_id
WHERE p.status = 'pending'
  AND YEAR(p.due_month) = YEAR(CURRENT_DATE)
  AND MONTH(p.due_month) = MONTH(CURRENT_DATE);

    ";

    // Execute the query
    $result = mysqli_query($conn, $query);

  
        $row = mysqli_fetch_assoc($result);
        return $row['unique_pending_users']; // Return the count
   
}
function getUserActivePlanscount($user_id){
    $query = "
    SELECT 
    COUNT(us.user_scheme_id) AS active_plans_count
FROM 
    user_schemes us
WHERE 
    us.status = 'active'
    AND us.user_id = $user_id;

    ";
    global $conn;
    // Execute the query
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
    return $row['active_plans_count'];
}
function CalculatePendingmonths($user_id){
    global $conn;

    // SQL query to count new users who joined this month
    $query = "
       SELECT 
    COUNT(p.payment_id) AS pending_months_count
FROM 
    payments p
JOIN 
    user_schemes us ON p.user_scheme_id = us.user_scheme_id
WHERE 
    p.status = 'pending'
    AND us.user_id = $user_id;

    ";
    $result = mysqli_query($conn, $query);
    $row = mysqli_fetch_assoc($result);
        return $row['pending_months_count']; 
}
function getBasicDetails($user_id) {
    global $conn;

    // SQL query to get user details
    $query = "
        SELECT 
            user_id,
            name,
            email,
            phone,
            registered_at
        FROM 
            users
        WHERE 
            user_id = ?
    ";

    // Prepare the statement
    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        die('Query preparation failed: ' . mysqli_error($conn));
    }

    // Bind parameter to the prepared statement
    mysqli_stmt_bind_param($stmt, 'i', $user_id);

    // Execute the query
    mysqli_stmt_execute($stmt);

    // Fetch the result
    $result = mysqli_stmt_get_result($stmt);

    // Fetch the user details
    $userDetails = mysqli_fetch_assoc($result);

    return $userDetails;
}

function getActivePlans($user_id) {
    global $conn;

    // Query to fetch active plans and calculate pending amount as sum of 'pending' payments
    $query = "
    SELECT 
        us.user_scheme_id,
        ss.scheme_name AS name,
        ss.plan_amount AS amount,
        us.status,
        COUNT(p.payment_id) AS months_paid,
        IFNULL(SUM(CASE WHEN p.status = 'pending' THEN p.amount ELSE 0 END), 0) AS pending_amount,
        us.joined_at,
        us.end_date
    FROM 
        user_schemes us
    JOIN 
        savings_schemes ss ON us.scheme_id = ss.scheme_id
    LEFT JOIN 
        payments p ON us.user_scheme_id = p.user_scheme_id
    WHERE 
        us.user_id = ? AND us.status = 'active'
    GROUP BY 
        us.user_scheme_id, ss.scheme_name, ss.plan_amount, us.status, us.joined_at, us.end_date
    ";

    // Prepare the statement
    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        die('Query preparation failed: ' . mysqli_error($conn));
    }

    // Bind user ID to the query
    mysqli_stmt_bind_param($stmt, 'i', $user_id);

    // Execute the query
    mysqli_stmt_execute($stmt);

    // Fetch the results
    $result = mysqli_stmt_get_result($stmt);

    $activePlans = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $activePlans[] = [
            'name' => $row['name'],
            'amount' => $row['amount'],
            'status' => $row['status'],
            'months_paid' => $row['months_paid'],
            'pending_amount' => $row['pending_amount'],
            'joined_at' => $row['joined_at'],
            'end_date' => $row['end_date'],
        ];
    }

    return $activePlans;
}


function getUserplannames($user_id) {
    global $conn;

    // SQL query to retrieve scheme_id and scheme_name for the given user
    $query = "
        SELECT 
            us.scheme_id, 
            ss.scheme_name
        FROM 
            user_schemes us
        JOIN 
            savings_schemes ss ON us.scheme_id = ss.scheme_id
        WHERE 
            us.user_id = ?
    ";

    // Prepare the SQL statement
    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        die('Query preparation failed: ' . mysqli_error($conn));
    }

    // Bind the user ID parameter
    mysqli_stmt_bind_param($stmt, 'i', $user_id);

    // Execute the query
    mysqli_stmt_execute($stmt);

    // Get the result set
    $result = mysqli_stmt_get_result($stmt);

    // Fetch the schemes into an array
    $userSchemes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $userSchemes[] = [
            'scheme_id' => $row['scheme_id'],
            'scheme_name' => $row['scheme_name']
        ];
    }

    return $userSchemes; // Return the list of schemes
}


function getPendingPayments($user_id, $plan_id) {
    global $conn;

    // SQL query to fetch pending payments for the given user and plan
    $query = "
        SELECT 
            p.payment_id, 
            p.due_month, 
            p.amount, 
            p.status
        FROM 
            payments p
        JOIN 
            user_schemes us ON p.user_scheme_id = us.user_scheme_id
        WHERE 
            us.user_id = ? 
            AND us.scheme_id = ?
            AND p.status = 'pending'
        ORDER BY 
            p.due_month ASC
    ";

    // Prepare the SQL statement
    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        die('Query preparation failed: ' . mysqli_error($conn));
    }

    // Bind the parameters (user_id and plan_id)
    mysqli_stmt_bind_param($stmt, 'ii', $user_id, $plan_id);

    // Execute the query
    mysqli_stmt_execute($stmt);

    // Get the result set
    $result = mysqli_stmt_get_result($stmt);

    // Fetch the pending payments into an array
    $pendingPayments = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $pendingPayments[] = [
            'payment_id' => $row['payment_id'],
            'due_month' => $row['due_month'],
            'amount' => $row['amount'],
            'status' => $row['status']
        ];
    }

    return $pendingPayments; // Return the list of pending payments
}

function makepayment($payment_id) {
    global $conn;

    // SQL query to update the payment status and set payment date
    $query = "
        UPDATE payments
        SET status = 'paid', 
            payment_date = CURRENT_TIMESTAMP
        WHERE payment_id = ?
    ";

    // Prepare the SQL statement
    $stmt = mysqli_prepare($conn, $query);

    if (!$stmt) {
        die('Query preparation failed: ' . mysqli_error($conn));
    }

    // Bind the parameter (payment_id)
    mysqli_stmt_bind_param($stmt, 'i', $payment_id);

    // Execute the query
    $success = mysqli_stmt_execute($stmt);

    // Check execution result
    if ($success) {
        return true; // Payment updated successfully
    } else {
        return false; // Failed to update payment
    }
}


?>