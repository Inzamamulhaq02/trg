<?php
// Start session
session_start();

// Check if admin is logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Database connection settings
require 'dbconnect.php';

require 'dbconnect.php';


// Handle form submission to add user
if (isset($_POST['add_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $conf_password = $_POST['conf_pass'];

    if ($password !== $conf_password) {
        $error = "Password does not match!";
    } else {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssss", $name, $email, $password, $phone);
        if ($stmt->execute()) {
            $message = "User added successfully!";
        } else {
            $message = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
}

// Handle form submission to delete selected users
if (isset($_POST['delete_selected'])) {
    if (!empty($_POST['selected_users'])) {
        // Get selected user IDs
        $selected_users = $_POST['selected_users'];
        
        // Check if selected_users is an array and contains values
        if (is_array($selected_users) && count($selected_users) > 0) {
            $placeholders = implode(',', array_fill(0, count($selected_users), '?'));

            // Prepare the delete query
            $sql = "DELETE FROM users WHERE user_id IN ($placeholders)";
            $stmt = $conn->prepare($sql);

            if ($stmt === false) {
                // Handle error with preparing the statement
                die("Error preparing query: " . $conn->error);
            }

            // Bind the parameters dynamically
            $stmt->bind_param(str_repeat('i', count($selected_users)), ...$selected_users);

            if ($stmt->execute()) {
                $error = "Selected users deleted successfully!";
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();

            // Refresh the page to show updated user list
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $error = "No valid users selected for deletion.";
        }
    } else {
        $error = "No users selected for deletion.";
    }




}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['assign_to_scheme'])  && isset($_POST['scheme_id'])) {
       
        $scheme_id = $_POST['scheme_id'];
        $user_id=$_POST['user_id'];
        // foreach ($selected_users as $user_id) {
            $stmt = $conn->prepare("INSERT INTO user_schemes (user_id, scheme_id) VALUES (?, ?)");
            $stmt->bind_param("ii", $user_id, $scheme_id);
            $stmt->execute();
            $stmt->close();
        // }
        echo "<p>Selected users have been successfully assigned to the scheme.</p>";
    }
}
// Fetch all users
$sql = "SELECT * FROM users";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
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
        }
        th {
            background: #007bff;
            color: white;
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
        <h1>Manage Users</h1>

        <div class="form-container">
            <h2>Add User</h2>
            <form method="POST" action="">
                <input type="text" name="name" placeholder="Name" required>
                <input type="email" name="email" placeholder="Email" required>
                <input type="text" name="phone" placeholder="Phone No" required>
                <input type="text" name="password" placeholder="Password" required>
                <input type="text" name="conf_pass" placeholder="Confirm Password" required>

                <button type="submit" name="add_user">Add User</button>
            </form>
        </div>

        

        <!-- User List -->
        <h2>Existing Users</h2>
        <form method="POST" action="">
            <table>
                <tr>
                    <th>Select</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                </tr>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="selected_users[]" value="<?php echo $row['user_id']; ?>">
                            </td>

                            <td><?php echo $row['name']; ?></td>
                            <td><?php echo $row['email']; ?></td>
                            <td><?php echo $row['phone']; ?></td>
                            <td>
                               
                     <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="user_id" value="<?php echo $row['user_id']; ?>">
                        <select name="scheme_id" required>
                            <option value="">Select a Scheme</option>
                            <?php 
                            // Fetch schemes from the database
                            $schemes_result = $conn->query("SELECT scheme_id, scheme_name FROM savings_schemes");
                            if ($schemes_result && $schemes_result->num_rows > 0) {
                                while ($scheme = $schemes_result->fetch_assoc()) {
                                   
                                    echo '<option value="'.$scheme['scheme_id'].'">'.$scheme['scheme_name'].'</option>';
                                }
                            }
                            ?>
                        </select>
                        <button type="submit" name="assign_to_scheme" 
                                onclick="return confirm('Are you sure you want to assign this user to the selected scheme?')">Assign</button>
                    </form>
                </td>
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
</body>
</html>

<?php
$conn->close();
?>


<!-- <script>
    document.querySelector('form',(e)=>{
        e.preventDefault();
    })
</script> -->