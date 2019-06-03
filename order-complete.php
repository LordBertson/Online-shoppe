<!DOCTYPE html>
<html lang="en">
<head>
    <?php require './repetitive/head.html'?>
    <title>KuchINÉ - Shopping Cart</title>
</head>
<body>
<?php require './repetitive/style.html'?>
    <style>
    .container{
                height: 100vh;
                width: 83.5vw;
            }
    th,td{
        padding-right:10px;
        padding-left:5px;
        border-bottom: 1px #c0c0c0 solid;
    }
    table{
        border: 2px solid #333333;
        width: 70vw
    }
    button{
        width: 70vw;
    }
    td{
        color:#333333;
        background-color: white;
    }
    th{
        color:#111111;
        background-color: grey;
    }

    
    h1,h2{
        padding-top:20px;
        text-align: center;
    }
    .form-control{
        width:70px;
    }
    input{
        height:20px;
        outline: 1px #c0c0c0 solid;
        margin-left: 6vw;
    }
    </style>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if(!isset($_SESSION['email'])) {
    header("Location: ./login.php");
}

require './repetitive/dbconnect.php';
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
$dsn = "mysql:host=$server;dbname=$database;charset=$charset";
try {
     $link = new PDO($dsn, $user, $pass);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
} 
    if (!$link) {
        printf($messErr_connectionDatabaseFailed);
        printf("<br />");
    }else{
        $userID = $_SESSION["id"];
        $orderID = $_GET['order'];
        $stmt= $link->prepare('SELECT * FROM Orders WHERE orderID=?');
        $stmt->execute([$orderID]);
        $order = $stmt-> fetch();

        if($order['userID'] != $userID){
            header("Location: ./shop.php");
            exit;
        }

        $stmt= $link->prepare('SELECT * FROM OrderedItems WHERE orderID=?');
        $stmt->execute([$orderID]);
        $items = $stmt-> fetchAll();

        $stmt= $link->prepare('SELECT email FROM Users WHERE userID=?');
        $stmt->execute([$userID]);
        $user = $stmt-> fetch();
        $email = $user['email'];
    }
?>
    <?php require './repetitive/header.php'?>
    <main id="container" class="container">
        <h1 align='center'>Order</h1>
        <table align='center'>
        <tr>
            <th>Name</th>
            <th>Quantity</th>
            <th>Per Piece</th>
            <th>Price</th>
        </tr>
        <?php
        $total = 0;
        foreach($items as $item){
            $itemID = $item['itemID'];
            $stmt= $link->prepare('SELECT name FROM Items WHERE itemID=?');
            $stmt->execute([$itemID]);
            $name = $stmt-> fetch();
        $rowTotal = $item['priceOrd']*$item['quantityOrd'];
        $total = $total + $rowTotal;

        ?>
                        <tr>
                            <td><?php echo $name['name']?></td>
                            <td><?php echo $item['quantityOrd']?></td>
                            <td>$<?php echo $item['priceOrd']?></td>
                            <td>$<?php echo $rowTotal?></td>
                        </tr>
        <?php 
        } ?>
        <tr>
            <th>Total:</th>
            <th></th>
            <th></th>
            <th style='font-size:15px'>$<?php echo $total?></th>
        </tr>
        </table>
        <br>
        <table align='center'>
            <tr>
                <th>Recipient</th>
                <th></th>
            </tr>
            <tr>
                <td>Name:</td>
                <td><?php echo $order['name'] ?></td>
            </tr>
            <tr>
                <td>Address:</td>
                <td><?php echo $order['address'] ?></td>
            </tr>
            <tr>
                <td>Email:</td>
                <td><?php echo $email ?></td>
            </tr>
            <tr>
                <td>Date and time placed:</td>
                <td><?php echo $order['datePlaced'] ?></td>
            </tr>
            <tr>
                <td>Payment method:</td>
                <td><?php echo $order['paymentMethod'] ?></td>
            </tr>
            <tr>
                <td>Delivery method:</td>
                <td><?php echo $order['deliveryOption'] ?></td>
            </tr>
        </table>
    </main>
    <footer></footer>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.0/js/bootstrap.min.js"></script>
    <script>
    window.onscroll = function() {myFunction()};

    var navbar = document.getElementById("navbar");
    var container = document.getElementById("container");
    var sticky = navbar.offsetTop;

    function myFunction() {
    if (window.pageYOffset >= sticky) {
        navbar.classList.add("sticky");
        container.style.paddingTop = "38px";
        
    } else {
        navbar.classList.remove("sticky");
        container.style.paddingTop = "0px";
    }
    }
    </script>
</body>
</html>
