<?php 


if (isset($_POST['add_plans'])) {
    $scheme_id=$_POST['scheme_id'];
    $scheme_name=$_POST['scheme_name'];
    $duration=$_POST['total_months'];
    $amount=$_POST['monthly_due'];
 $start_date=date('d-m-y');
 
 
 
   
  
         $sql = "INSERT INTO savings_schemes (scheme_id, name, total_months, current_due_month,start_date) VALUES (?, ?, ?, ?,?)";
         $stmt = $conn->prepare($sql);
         $stmt->bind_param("sssss", $scheme_id,$scheme_name,$duration,$amount,$start_date);
         if ($stmt->execute()) {
             $message = "plan added successfully!";
         } else {
             $message = "Error: " . $stmt->error;
         }
         $stmt->close();
     
 }
?>