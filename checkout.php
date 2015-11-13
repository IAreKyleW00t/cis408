<?php
    /* Load config.php */
    require_once 'config.php';
    
    /* Start PHP Session */
    session_start();
    session_regenerate_id(true); // Security precaution
    
    /* Check if the user is logged in */
    if (!isset($_SESSION['user'])) {
        header('Location: ./login.php');
        exit();
    }
        
    /* Open SQL Connection */
    $sql = new PDO(SQL_TYPE . ':host=' . SQL_HOST . ';dbname=' . SQL_DB . ';charset=utf8', SQL_USER, SQL_PASSWD);
    $sql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sql->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $sql->setAttribute(PDO::ATTR_PERSISTENT, false);

    /* Query the `products` database */
    $query = $sql->prepare('SELECT * FROM products');
    $query->execute();
    
    $products = $query->fetchAll();
    
    /* Check if we sent a POST request */
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $uuid = uniqid();
        $items = $_SESSION['items'];
        $unique_items = array_unique($_SESSION['items']);
        
        foreach ($unique_items as $i) {
            $q = count(array_keys($items, $i));
            $total = $q * $products[$i - 1]['price'];
            
            $query = $sql->prepare('INSERT INTO order_history (id, order_number, account, item, quantity, price) VALUES (?,?,?,?,?,?)');
            $query->execute(array(NULL, $uuid, $_SESSION['user']['id'], $i, $q, $total));
        }
        
        unset($_SESSION['items']);
    }
    
    if (!isset($_SESSION['items'])) {
        $_SESSION['items'] = array();
    }
    
    $items = $_SESSION['items'];
    $unique_items = array_unique($_SESSION['items']);
    $price = 0.00;
    
    foreach ($items as $i) {
        $price += $products[$i - 1]['price'];
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
        <title>CSUOhio Shopping :: Checkout</title>
        
        <!-- apple-touch-icon.png 152x152 -->
        <link rel="apple-touch-icon" href="apple-touch-icon.png">

        <!-- Bootstrap CSS -->
        <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" type="text/css">
        
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
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                    <h1>Checkout</h1>
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <?php if (!empty($_SESSION['items'])) : ?>
                            <table class="table table-stripped table-hover">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Price</th>
                                        <th>Quantity</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                        foreach ($unique_items as $i) {
                                            $p = $products[$i - 1];
                                            echo '<tr id="' . $p['id'] . '">';
                                            echo '<td>' . $p['name'] . '</td>';
                                            echo '<td>$' . $p['price'] . '</td>';
                                            echo '<td class="text-center">' . count(array_keys($items, $i)) . '</td>';
                                            echo '<td><button id="del-btn" class="btn btn-material-red" value="' . $p['id'] . '">Remove</button></td>';
                                            echo '</tr>';
                                        }
                                    ?>
                                </tbody>
                            </table>
                            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                                <button id="checkout-btn" class="btn btn-lg btn-material-blue">Checkout</button>
                            </div>
                            <?php else : ?>
                            <h3 class="text-center">Cart empty</h3>
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($_SESSION['items'])) : ?>
                        <div class="panel-footer text-right"><b>Total:</b> $<?php echo $price; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    
        <!-- jQuery -->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js" type="text/javascript"></script>
        
        <!-- Bootstrap JS -->
        <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js" type="text/javascript"></script>
        
        <!-- Material JS -->
        <script src="js/vendor/material.min.js" type="text/javascript"></script>
        <script src="js/vendor/ripples.min.js" type="text/javascript"></script>

        <!-- main.js -->
        <script src="js/main.js" type="text/javascript"></script>
    </body>
</html>
