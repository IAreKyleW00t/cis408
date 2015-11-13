<?php
    /* Start the PHP Session */
    session_start();
    session_regenerate_id(true); // Security precaution
    
    /* Unset all $_SESSION variables */
    $_SESSION = array();
    
    /* Delete all session cookies */
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
    }
    
    /* Destory the PHP Session */
    session_destroy();
    header('Location: ./login.php'); // Return to login.php
?>
