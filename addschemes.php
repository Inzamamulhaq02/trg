<?php 


if (isset($_POST['add_plans'])) {
    $scheme_id=$_POST['scheme_id'];
    $scheme_name=$_POST['scheme_name'];
    $duration=$_POST['duration'];
    $amount=$_POST['plan_amount'];
 $start_date=date('d-m-y');
 
 
 
   
  
         $sql = "INSERT INTO savings_schemes (scheme_id, Scheme_name, duration, plan_amount,created_at) VALUES (?, ?, ?, ?,?)";
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