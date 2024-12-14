<?php 

session_start();

// Check if admin is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}
// Database connection settings
require 'dbconnect.php';
// include 'sidebar.php';
require 'addschemes.php';
require 'fetch_plans.php';
require 'deleteschemes.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
         body {
    margin: 0;
    font-family: Arial, sans-serif;
    background-color: #20232a;
    color: #ffffff;
    display: flex;
    height: 100vh; /* Ensures full screen height */
}

.sidebar {
    width: 250px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    background-color: #282c34;
    padding-top: 20px;
    z-index: 1000; /* Ensure sidebar is always on top */
}

.sidebar a {
    display: block;
    color: white;
    padding: 10px 20px;
    text-decoration: none;
}

.sidebar a:hover {
    background-color: #61dafb;
    color: black;
}

.container {
    flex-grow: 1; /* Allows the container to take the rest of the screen space */
    margin-left: 250px; /* Pushes content to the right of the sidebar */
    padding: 20px;
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    overflow-y: auto; /* Ensures content doesn't overflow */
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

table, th, td {
    border: 1px solid #ddd;
}

th, td {
    padding: 10px;
    text-align: left;
    color: black;
}

th {
    background: #007bff;
    color: white;
}

h1 {
    color: black;
}

.message {
    color: green;
    margin-bottom: 20px;
}

.error {
    color: red;
    margin-bottom: 20px;
}

.form-container {
    margin: 20px 0;
}

.form-container input {
    padding: 10px;
    margin: 5px 0;
    width: calc(100% - 20px);
}

.form-container button {
    padding: 10px 20px;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

.form-container button:hover {
    background: #0056b3;
}

    </style>

</head>
<body>



<div class="container">
    <?php include 'sidebar.php';?>


    <div class="mt-4">
            <h2>Recent Actions</h2>
            <div class="card p-3">

        
   
        <div class="form-container">
            <div>
                <h1>add schemes</h1>
                <form action="" method="POST">
                <input type="text" name="scheme_name" placeholder="name">
                <input type="number" name="duration" placeholder="duration">
                <input type="number" name="plan_amount" placeholder="amount">
                
                <button type="submit" name="add_plans">Add schemes</button>
                </form>
            </div>
        </div>

        

        <!-- User List -->
        <h2>Existing Users</h2>
        <form method="POST" action="">
            <table>
                <tr>
                    <th>Select</th>
                    <th>Scheme name</th>
                    <th>Duration</th>
                    <th>Plan Amount</th>
                    
                </tr>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="selected_schemes[]" value="<?php echo $row['scheme_id']; ?>">
                            </td>
                            <td><?php echo $row['scheme_name']; ?></td>
                            <td><?php echo $row['duration']; ?></td>
                            <td><?php echo $row['plan_amount']; ?></td>

                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No users found.</td>
                    </tr>
                <?php endif; ?>
            </table>
            <button type="submit" name="delete_selected" 
                    onclick="return confirm('Are you sure you want to delete the selected users?')">Delete Selected</button>
        </form>

        <!-- Success message -->
        <?php if (isset($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <!-- Error message -->
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
    </div>

    </div>
        
  
</body>
</html>
