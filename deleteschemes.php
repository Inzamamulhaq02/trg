<?php 

if (isset($_POST['delete_selected'])) {
    if (!empty($_POST['selected_schemes'])) {
        // Get selected user IDs
        $selected_users = $_POST['selected_schemes'];
        
        // Check if selected_users is an array and contains values
        if (is_array($selected_users) && count($selected_users) > 0) {
            $placeholders = implode(',', array_fill(0, count($selected_users), '?'));

            // Prepare the delete query
            $sql = "DELETE FROM savings_schemes WHERE scheme_id IN ($placeholders)";
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

?>