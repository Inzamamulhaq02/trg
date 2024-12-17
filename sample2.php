<?php
require 'utils.php';

// Replace with the payment_id to mark as paid
$payment_id = 1;

// Call the function to mark the payment as paid
if (makepayment($payment_id)) {
    echo "Payment marked as paid successfully!";
} else {
    echo "Failed to mark the payment as paid.";
}

?>