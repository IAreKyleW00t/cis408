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
            
            $item = $_POST['item'];
            $key = array_search($item, $_SESSION['items']);
            unset($_SESSION['items'][$key]);
        }
    } else {
        echo 'Invalid POST request.';
    }
?>
