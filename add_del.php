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

if (isset($_POST['add_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $password = $_POST['password'];
    $conf_password = $_POST['conf_pass'];

    // Step 1: Check if the phone number is unique
    $sql = "SELECT id FROM users WHERE phone = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $phone);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // If phone number exists, set error message
        $error = "Phone number already exists. Please try a different number.";
    } else {
        // Step 2: Validate password only if phone number is unique
        if ($password !== $conf_password) {
            $error = "Password does not match!";
        } else {
            // Step 3: Insert the user into the database
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql = "INSERT INTO users (name, email, password, phone) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $name, $email, $password, $phone);
            if ($stmt->execute()) {
                $message = "User added successfully!";
            } else {
                $error = "Error: " . $stmt->error;
            }
        }
    }
    $stmt->close();
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
                $del_msg = "Selected users deleted successfully!";
            } 
            else {
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
        
        header('Location: '.$_SERVER['PHP_SELF'].'?success=true');
        exit();
    }
   
    
}
if (isset($_GET['success'])) {
    $message="User successfully assigned to the scheme!";
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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    
</head>
<body>
<?php include 'sidebar.php';?>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark">
            <div class="container-fluid">
                <span class="navbar-brand">Welcome, Admin</span>
                <div class="d-flex">
                    <a href="#" class="me-3">View Site</a>
                    <a href="#" class="me-3">Change Password</a>
                    <a href="logout.php" class="btn btn-danger btn-sm">Log Out</a>
                </div>
            </div>
        </nav>

        <!-- Dashboard Content -->
        <div class="mt-4">
            <h2>Manage Users</h2>
            <div class="card p-3">
                <div class="form-container">
                    <h2>Add User</h2>
                    <form method="POST" action="">
                        <input type="text" name="name" placeholder="Name" required>
                        <input type="email" name="email" placeholder="Email" required>
                        <input type="text" name="phone" placeholder="Phone No" pattern="\d{10}" title="Phone number must be 10 digits" required>
                        <input type="text" name="password" placeholder="Password" required>
                        <input type="text" name="conf_pass" placeholder="Confirm Password" required>
                         <!-- Success message -->
    <?php if (isset($message)): ?>
            <p class="message"><?php echo $message; ?></p>
        <?php endif; ?>

        <!-- Error message -->
        <?php if (isset($error)): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
                        <button type="submit" name="add_user">Add User</button>
                    </form>
                </div>
            </div>
           
     

            <h2>Existing Users</h2>
        <form method="POST" action="">
            <table>
                <tr>
                    <th>Select</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Actions</th>
                </tr>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr popovertarget="myheader" >
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
                            </t>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No users found.</td>
                    </tr>
                <?php endif; ?>
            </table>

            <?php if ($result && $result->num_rows > 0): ?>
            <button type="submit" name="delete_selected" 
                class="delete_btn"   onclick="return confirm('Are you sure you want to delete the selected users?')">Delete Selected</button>
            <?php endif; ?>

            </form>


            
        </div>


   

    </div>
    <dialog id="popover" popover >

    <button onclick="document.getElementById('popover').close()">Close</button>
</dialog>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
     document.querySelectorAll('table tbody tr').forEach(row => {
        row.addEventListener('click', () => {
            const details = row.dataset.details;
            const popover = document.getElementById('popover');
            // const content = document.getElementById('popover-content');

            // Update popover content
            // content.textContent = details;

            // Show the popover
            popover.showPopover();
        });
    });
</script>
</body>



<style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #20232a;
            color: #ffffff;
        }
        .sidebar {
            width: 250px;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #282c34;
            padding-top: 20px;
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
        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
        .navbar {
            background-color: #61dafb;
            color: white;
        }
        .navbar a {
            color: white;
        }
        .card {
            background-color: #282c34;
            color: white;
        }
        .card a {
            color: #61dafb;
            text-decoration: none;
        }
        .card a:hover {
            text-decoration: underline;
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
            margin-top: 10px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .delete_btn
        {
            padding: 10px 20px;
            background: red;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .delete_btn:hover{
            background: rgb(213, 68, 68);
        }

        .form-container button:hover {
            background: #0056b3;
        }

        .message {
            color: green;
            margin-bottom: 20px;
        }
        .error {
            color: red;
            margin-bottom: 20px;
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
        dialog{
            width: 60%;
            height: 70%;

        }

    </style>
</html>

