<?php
    /* Load config.php */
    require_once 'config.php';
    
    /* Start PHP Session */
    session_start();
    session_regenerate_id(true); // Security precaution
    
    /* Check if the user is already logged in */
    if (isset($_SESSION['user'])) {
        header('Location: ./checkout.php');
        exit();
    }
    
    /* Check if we sent a POST request */
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        /* Validate $_POST variables */
        if (!isset($_POST['username']) || !isset($_POST['password'])) {
            $_SESSION['error'] = "Invalid POST request.";
            header('Location: ./login.php');
            exit();
        }
        
        /* Open SQL Connection */
        $sql = new PDO(SQL_TYPE . ':host=' . SQL_HOST . ';dbname=' . SQL_DB . ';charset=utf8', SQL_USER, SQL_PASSWD);
        $sql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $sql->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $sql->setAttribute(PDO::ATTR_PERSISTENT, false);

        /* Query the `accounts` database */
        $query = $sql->prepare('SELECT * FROM accounts WHERE username=? AND password=?');
        $query->execute(array($_POST['username'], hash(HASH, AUTH_SALT . $_POST['password'] . AUTH_SALT)));
        
        /* Check for a valid result; 1 = valid, 0 = not found */
        if ($query->rowCount() != 1) {
            if(!isset($_COOKIE['login_attempts']))
                setcookie('login_attempts', 1, strtotime('+5 minutes'));
            else
                setcookie('login_attempts', $_COOKIE['login_attempts'] + 1, strtotime('+5 minutes'));
            
            $_SESSION['error'] = "Invalid username or password.";
            header('Location: ./login.php');
            exit();
        }
        
        /* Save the user */
        $user = $query->fetchAll(PDO::FETCH_ASSOC)[0];
        
        /* Check if user account is locked */
        if ($user['locked'] == 1) {
            $_SESSION['error'] = "Your account is locked.";
            header('Location: ./login.php');
            exit();
        }
        
        /* Save admin information into $_SESSION */
        $_SESSION['user'] = $user;
        
        /* Update last login Information */
        $query = $sql->prepare("UPDATE accounts SET last_login_time=?,last_login_ip=? WHERE id=?");
        $query->execute(array(date("Y-m-d H:i:s", time()), $_SERVER['REMOTE_ADDR'], $user['id']));

        /* Redirect to panel.php */
        header('Location: ./checkout.php');
    }
?>
<!DOCTYPE html>
<!--[if lt IE 7]><html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"><![endif]-->
<!--[if IE 7]><html class="no-js lt-ie9 lt-ie8" lang="en"><![endif]-->
<!--[if IE 8]><html class="no-js lt-ie9" lang="en"><![endif]-->
<!--[if gt IE 8]><!--><html class="no-js" lang="en"><!--<![endif]-->
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1,user-scalable=no">
        <meta name="description" content="Cleveland State University's ACM Student Chapter.">
        <meta name="theme-color" content="#F44336">
        <title>CSUOhio Shopping :: Login</title>
        
        <!-- apple-touch-icon.png 152x152 -->
        <link rel="apple-touch-icon" href="apple-touch-icon.png">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css" type="text/css">
        
        <!-- Material CSS -->
        <link rel="stylesheet" href="css/vendor/material-fullpalette.min.css" type="text/css">
        <link rel="stylesheet" href="css/vendor/roboto.min.css" type="text/css">
        <link rel="stylesheet" href="css/vendor/ripples.min.css" type="text/css">
        
        <!-- main.css -->
        <link rel="stylesheet" href="css/main.css" type="text/css">
        <style>
            body {
                padding-top: 80px;
            }
        </style>
        
        <!-- Mondernizr -->
        <script src="js/vendor/modernizr.min.js"></script>
    </head>
    <body>
        <nav class="navbar navbar-fixed-top navbar-material-blue">
            <div class="container">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-responsive-collapse">
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="products.php">CSUOhio Shopping</a>
                </div>
                <div class="navbar-collapse collapse navbar-responsive-collapse">
                    <ul class="nav navbar-nav">
                        <li><a href="products.php">Products</a></li>
                    </ul>
                    <ul class="nav navbar-nav navbar-right">
                        <li class="dropdown">
                            <a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">Welcome, <?php echo isset($_SESSION['user']['first_name']) ? $_SESSION['user']['first_name'] : 'Guest'; ?> <b class="caret"></b></a>
                            <ul class="dropdown-menu">
                                <?php if (isset($_SESSION['user'])) : ?>
                                <li><a href="checkout.php">Checkout</a></li>
                                <li><a href="history.php">Order History</a></li>
                                <li class="divider"></li>
                                <li><a id="logout" href="javascript:void(0)">Logout</a></li>
                                <?php else : ?>
                                <li><a href="login.php">Login</a></li>
                                <li><a href="register.php">Register</a></li>
                                <?php endif; ?>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        <div class="notice">
            <code>Student Project #6/7 - K.C.</code>
        </div>
        
        <div class="container">
            <div class="row">
                <div class="col-sm-12 col-md-8 col-md-offset-2">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                            <h3 class="panel-title">Login</h3>
                        </div>
                        <div class="panel-body">
                            <?php if (isset($_SESSION['error'])) : ?>
                            <div class="text-center">
                                <h4 class="text-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></h4>
                            </div>
                            <?php endif; ?>
                            <form class="form-horizontal" name="login" action="login.php" method="post" validate>
                                <fieldset>
                                    <div class="form-group">
                                        <label for="username" class="col-sm-2 control-label">Username</label>
                                        <div class="col-sm-10">
                                            <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="password" class="col-sm-2 control-label">Password</label>
                                        <div class="col-sm-10">
                                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-10 col-sm-offset-2">
                                            <button type="submit" class="btn btn-material-blue">Login</button><a class="btn btn-flat btn-material-blue" href="register.php">Register</a>
                                        </div>
                                    </div>
                                </fieldset>
                            </form>
                            <div class="col-xs-12 text-center">
                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- jQuery -->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js" type="text/javascript"></script>
        
        <!-- Bootstrap JS -->
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js" type="text/javascript"></script>
        
        <!-- Material JS -->
        <script src="js/vendor/material.min.js" type="text/javascript"></script>
        <script src="js/vendor/ripples.min.js" type="text/javascript"></script>

        <!-- main.js -->
        <script src="js/main.js" type="text/javascript"></script>
    </body>
</html>
