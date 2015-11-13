<?php
    /* Start PHP Session */
    session_start();
    session_regenerate_id(true); // Security precaution
    
    /* Check if we sent a POST request */
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        /* Validate $_POST variables */
        if (isset($_POST['item'])) {
            if(!isset($_SESSION['items'])) {
                $_SESSION['items'] = array();
            }
            
            array_push($_SESSION['items'], $_POST['item']);
        }
    } else {
        echo 'Invalid POST request.';
    }
?>
