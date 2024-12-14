<?php 


if (isset($_POST['add_plans'])) {
    $scheme_name=$_POST['scheme_name'];
    $duration=$_POST['duration'];
    $amount=$_POST['plan_amount'];
    $created_at=date('d-m-y');
 
  
         $sql = "INSERT INTO savings_schemes (scheme_name,duration,plan_amount,created_at) VALUES (?,?,?,?)";
         $stmt = $conn->prepare($sql);

         if (!$stmt) 
         {
             die("Error preparing statement: " . $conn->error);
         }

         $stmt->bind_param("siis",$scheme_name,$duration,$amount,$created_at);
         if ($stmt->execute()) {
             $message = "plan added successfully!";
         } else {
             $message = "Error: " . $stmt->error;
         }
         $stmt->close();
     
 }
?>
