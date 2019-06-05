<!DOCTYPE html>
<html lang="en">
<?php
require "./vendor/phpmailer/phpmailer/src/PHPMailer.php";
$mail = new PHPMailer\PHPMailer\PHPMailer();
session_start();
if(!isset($_SESSION['email'])) {
    header("Location: ./login.php");
}
if($_SESSION['privilege']!=3){
    header("Location: ./shop.php");
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
        $link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $stmt = $link -> prepare('SELECT status, orderID, email, name, address, datePlaced, dateRevieved, paymentMethod, deliveryOption  FROM Orders JOIN Users WHERE Orders.userID = Users.userID ORDER BY datePlaced DESC;');
                    $stmt -> bindParam(1, $email);
                    $stmt -> execute();
                    $orders = $stmt-> fetchAll();
    }
    if(isset($_GET['accept'])){
        if($_SESSION['privilege']>1){
            $orderID = $_GET['accept'];
            $dateRevieved = date("Y-m-d H:i:s");
            $stmt = $link -> prepare('SELECT status FROM Orders WHERE orderID=?');
            $stmt -> execute([$orderID]);
            $status = $stmt-> fetch();
            if($status['status'] != "accepted" and $status['status'] != "declined"){
                $stmt = $link -> prepare('UPDATE Orders SET dateRevieved=?, status=? WHERE orderID=?');
                $stmt -> execute([$dateRevieved, 'accepted', $orderID]);
                $stmt = $link -> prepare('SELECT email FROM Orders JOIN Users WHERE Users.userID=Orders.userID AND orderID=?');
                $stmt -> execute([$orderID]);
                $user = $stmt-> fetch();
                $mail->setFrom('vybp01@eso.vse.cz', 'Peter Výboch');
                $mail->addAddress($user['email'], $name);
                $mail->Subject  = 'Order accepted :)';
                $mail->Body     = 'Your order number '.$orderID.' has been accepted.';
                if(!$mail->send()) {
                echo 'Message was not sent.';
                echo 'Mailer error: ' . $mail->ErrorInfo;
                } else {
                echo 'Message has been sent.';
                }
                header("Location: ./list_orders.php");
                exit;
            }else{
                header("Location: ./list_orders.php?status=revieved_already");
                exit;
            }
        }
    }
    if(isset($_GET['decline'])){
        if($_SESSION['privilege']>1){
            $orderID = $_GET['decline'];
            $dateRevieved = date("Y-m-d H:i:s");
            $stmt = $link -> prepare('SELECT status FROM Orders WHERE orderID=?');
            $stmt -> execute([$orderID]);
            $status = $stmt-> fetch();
            if($status['status'] != "accepted" and $status['status'] != "declined"){
                $stmt = $link -> prepare('UPDATE Orders SET dateRevieved=?, status=? WHERE orderID=?');
                $stmt -> execute([$dateRevieved, 'declined', $orderID]);
                $stmt = $link -> prepare('SELECT email FROM Orders JOIN Users WHERE Users.userID=Orders.userID AND orderID=?');
                $stmt -> execute([$orderID]);
                $user = $stmt-> fetch();
                $mail->setFrom('vybp01@eso.vse.cz', 'Peter Výboch');
                $mail->addAddress($user['email'], $name);
                $mail->Subject  = 'Order declined :(';
                $mail->Body     = 'Your order number '.$orderID.' has been declined.';
                if(!$mail->send()) {
                echo 'Message was not sent.';
                echo 'Mailer error: ' . $mail->ErrorInfo;
                } else {
                echo 'Message has been sent.';
                }
                header("Location: ./list_orders.php");
                exit;
            }else{
                header("Location: ./list_orders.php?status=revieved_already");
                exit;
            }
        }
    }
?>
<head>
    <?php require './repetitive/head.html'?>
    <?php require './repetitive/style.html'?>
    <title>KuchINÉ - Shopping Cart</title>
    <style>
        .lista{
            text-align:right;
        }
        .lista a{
            color:blue;
        }
        th,td{
            padding-right:10px;
            border-top: 2px solid #333333;
        }
        td{
            color:#333333;
            background-color: #CCCCCC;
        }
        th{
            color:#111111;
            background-color: #888888;
        }
        table{
            text-align:left;
            width: 100%;
            margin-bottom: 50px;
        }

        
        h1{
            padding-top:20px;
        }
        #image{
            margin-top:27px;
            height:400px;
            width: 400px;
            outline: 2px #c0c0c0 solid;
        }
        #edit{
            padding-bottom:20px;
            padding-right:50px;
        }
        #desc{
            padding:5px;
            height:150px;
        }
        .container{
                height: 100vh;
                width: 82.5vw;
                text-align:center;
            }
        #edit, #image{
            display:inline-block;
        }
        #url{
            margin-top:15px;
        }
        #id{
            color: black;
            font-size: 20px;
        }
    </style>
</head>
<body>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$helperBool=true;
$priceBool=true;
$quantityBool=true;
$itemName='';
$category='';
$price='';
$quantity='';
$description='';
$imgurl='';
#DB #######################



?>
    <?php require './repetitive/header.php'?>
    <main id="container" class="container">
    <?php
    if(isset($_GET['status'])){
        if($_GET['status'] == 'revieved_already'){
            echo '<font color=\'red\'>This order has already been revieved<font>';
        }
    }
    ?>
    <h1> Items </h1>
    <div class="row justify-content-center">
    <?php
        foreach($orders as $order){ 
    ?>
    <div class='lista'><a href='./list_orders.php?accept=<?php echo $order['orderID']?>'>accept</a>/<a href='./list_orders.php?decline=<?php echo $order['orderID']?>'>decline</a></div>
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Date Placed</th>
            <th>Date Revieved</th>
            <th>Payment Method</th>
            <th>Delivery Option</th>
            <th>Status</th>
        </tr>
        <tr>
            <td><?php echo $order['orderID']?></td>
            <td><?php echo $order['name']?></td>
            <td><?php echo $order['email']?></td>
            <td><?php echo $order['datePlaced']?></td>
            <td><?php echo $order['dateRevieved']?></td>
            <td><?php echo $order['paymentMethod']?></td>
            <td><?php echo $order['deliveryOption']?></td>
            <td><?php echo $order['status']?></td>
        </tr>
        <tr>
            <th>Item ID</th>
            <th colspan="2">Item Name</th>
            <th>Quantity ordered</th>
            <th>Quantity available</th>
            <th>Price Ordered</th>
            <th></th>
            <th>Total</th>
        </tr>
        <?php
            $orderID = $order['orderID'];
            $stmt = $link -> prepare('SELECT Items.itemID, name, priceOrd, quantityOrd, quantity FROM OrderedItems JOIN Items WHERE OrderedItems.itemID = Items.itemID AND OrderedItems.orderID = ?');
            $stmt -> bindParam(1, $orderID);
            $stmt -> execute();
            $items = $stmt-> fetchAll();
            foreach($items as $item){
        ?>
        <tr>
            <td><?php echo $item['itemID']?></td>
            <td colspan="2"><?php echo $item['name']?></td>
            <td><?php echo $item['quantityOrd']?></td>
            <td><?php echo $item['quantity']?></td>
            <td><?php echo $item['priceOrd']?></td>
            <td></td>
            <td>Total</td>
        </tr>
        <?php } ?>
        </table>
        <?php } ?>
    </div>
    </main>
    <footer></footer>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.0/js/bootstrap.min.js"></script>
    <script>
    window.onscroll = function() {myFunction()};

    var navbar = document.getElementById("navbar");
    var container = document.getElementById("container");
    var sidebar = document.getElementById("sidebar-nav");
    var sticky = navbar.offsetTop;

    function myFunction() {
    if (window.pageYOffset >=sticky) {
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
