<!DOCTYPE html>
<html lang="en">
<head>
    <?php require './repetitive/head.html'?>
    <title>KuchINÉ - Shopping Cart</title>
</head>
<body>
<?php   ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
        require './repetitive/style.html';
        require "./vendor/phpmailer/phpmailer/src/PHPMailer.php";
        require_once __DIR__ . '/vendor/autoload.php';   
        $mpdf = new \Mpdf\Mpdf();
        $mail = new PHPMailer\PHPMailer\PHPMailer();
?>
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

    
    h1{
        padding-top:20px;
    }
    form{
        padding-top: 20px;
    }
    input[type=radio]{
        outline: none;
        text-align: center;
        height: auto;
    }
    </style>
<?php
$helperBool = true;
session_start();
if(!isset($_SESSION['email'])) {
    header("Location: ./login.php");
}
if(!isset($_SESSION['status'])){
    header("Location: ./home.php");
}
unset($_SESSION['status']);
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

        $stmt= $link->prepare('SELECT A.itemID, userID, quantityAdded, price, name FROM (SELECT userID, itemID, quantityAdded FROM ShoppingCarts  JOIN ItemsInCarts ON ShoppingCarts.cartID = ItemsInCarts.cartID) AS A JOIN Items ON A.itemID = Items.itemID WHERE userID = ?');
        $stmt->execute([$userID]);
        $cart = $stmt-> fetchAll();

        if(!empty($_POST)){
            if($_POST['firstname'] == ''){
                $helperBool = false;
            }
            if($_POST['lastname'] == ''){
                $helperBool = false;
            }
            if($_POST['street'] == ''){
                $helperBool = false;
            }
            if($_POST['house'] == ''){
                $helperBool = false;
            }
            if($_POST['city'] == ''){
                $helperBool = false;
            }
            if($_POST['postcode'] == ''){
                $helperBool = false;
            }
            if($helperBool){
                $userID = $_SESSION['id'];
                $name = $_POST['firstname'].' '.$_POST['lastname'];
                $address = $_POST['street'].' '.$_POST['house'].', '.$_POST['city'].' '.$_POST['postcode'];
                $datePlaced = date("Y-m-d H:i:s");
                $paymentMethod = $_POST['pay'];
                $deliveryMethod = $_POST['delivery'];
                echo $paymentMethod;
                echo $deliveryMethod;
                $stmt= $link->prepare('INSERT INTO Orders (userID, name, address, datePlaced, paymentMethod, deliveryOption) VALUES (?,?,?,?,?,?) ');
                $stmt->execute([$userID, $name, $address,$datePlaced,$paymentMethod,$deliveryMethod]);
                $stmt= $link->prepare('SELECT orderID FROM Orders WHERE userID = ? AND datePlaced = ? ');
                $stmt->execute([$userID,$datePlaced]);
                $order = $stmt-> fetch();
                $orderID = $order['orderID'];
                foreach($cart as $item){
                    $itemID = $item['itemID'];
                    $priceOrd = $item['price'];
                    $quantityOrd = $item['quantityAdded'];
                    $stmt= $link->prepare('INSERT INTO OrderedItems (orderID,itemID,priceOrd,quantityOrd) VALUES (?,?,?,?) ');
                    $stmt->execute([$orderID,$itemID,$priceOrd,$quantityOrd]);
                    $stmt= $link->prepare('DELETE FROM ItemsInCarts WHERE itemID = ? AND cartID = (SELECT cartID FROM ShoppingCarts WHERE userID = ?) ');
                    $stmt->execute([$itemID,$userID]);
                }
                $pdfContents =                      '<h1 align=\'center\'>Receipt</h1>
                                                    <strong>Name: </strong>'.$name.'<br>
                                                    <strong>Address: </strong>'.$address.'<br>
                                                    <strong>Payment method: </strong>'.$paymentMethod.'<br>
                                                    <strong>Delivery option: </strong>'.$deliveryMethod.'<br>
                                                    <strong>Date placed: </strong>'.$datePlaced.'<br><br>
                                                    ';
                $pdfContents = $pdfContents.        '<table align=\'left\'>
                                                    <tr>
                                                    <th>Name</th>
                                                    <th>Quantity</th>
                                                    <th>Per Piece</th>
                                                    <th>Price</th>
                                                    </tr>';
                $total = 0;
                foreach($cart as $item){
                    $rowTotal = $item['price']*$item['quantityAdded'];
                    $total = $total + $rowTotal;
                    $pdfContents = $pdfContents.    '<tr>
                                                    <td>'.$item['name'].'</td>
                                                    <td>'.$item['quantityAdded'].'</td>
                                                    <td>$'.$item['price'].'</td>
                                                    <td>$'.$rowTotal.'</td>
                                                    </tr>';   
                }
                $pdfContents = $pdfContents.        '<tr>
                                                    <th>Total:</th>
                                                    <th></th>
                                                    <th></th>
                                                    <th>$'.$total.'</th>
                                                    </tr>
                                                    </table>';
                $mpdf->WriteHTML($pdfContents);
                $path = './receipt/'.$orderID.'.pdf';
                $mpdf->Output($path);
                #tu vyrobiť pdf a poslať na mail
                $stmt= $link->prepare('SELECT email FROM Users WHERE userID = ?');
                $stmt->execute([$userID]);
                $user = $stmt-> fetch();

                $mail->setFrom('vybp01@eso.vse.cz', 'Peter Výboch');
                $mail->addAddress($user['email'], $name);
                $mail->Subject  = 'Receipt';
                $mail->Body     = 'Hi! Thank you for shopping at KuchINÉ. Your order '.$orderID.' is waiting to be reviewed. Your receipt is included in the attachment.';
                $mail->addAttachment($path);
                if(!$mail->send()) {
                echo 'Message was not sent.';
                echo 'Mailer error: ' . $mail->ErrorInfo;
                } else {
                echo 'Message has been sent.';
                }
                header("Location: ./order-complete.php?order=$orderID");
            }
        }
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
        foreach($cart as $item){
        $rowTotal = $item['price']*$item['quantityAdded'];
        $total = $total + $rowTotal;

        ?>
                        <tr id='<?php echo $item['itemID'] ?>'>
                            <td><?php echo $item['name']?></td>
                            <td><?php echo $item['quantityAdded']?></td>
                            <td>$<?php echo $item['price']?></td>
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
        <div class="row justify-content-center">
        <?php if(!$helperBool){
            echo '<font color=\'red\'> Fields marked with * must be filled</font>';
        } ?>
        <form id="form" class="form-signup" method="POST" action="./order.php">
            <div class="form-group">
                <label>First name*</label>
                <input class="form-control" name="firstname">
            </div>
            <div class="form-group">
                <label>Last name*</label>
                <input class="form-control" name="lastname">
            </div>
            <div class="form-group">
                <label>Street name*</label>
                <input class="form-control" name="street">
            </div>
            <div class="form-group">
                <label>House number*</label>
                <input class="form-control" name="house">
            </div>
            <div class="form-group">
                <label>City*</label>
                <input class="form-control" name="city">
            </div>
            <div class="form-group">
                <label>Postcode*</label>
                <input class="form-control" name="postcode">
            </div>
            <table><tr>
                <td>
                    <label>Payment options</label><br>
                    <input type="radio" name="pay" value="Cash" checked>Cash<br>
                    <input type="radio" name="pay" value="Card">Card<br>
                    <input type="radio" name="pay" value="Bank transaction">Bank transaction
                </td>
                <td>
                    <label>Delivery options</label><br>
                    <input type="radio" name="delivery" value="Store pickup" checked>Store pickup<br>
                    <input type="radio" name="delivery" value="Postal service">Post<br>
                    <input type="radio" name="delivery" value="Courier service"> Courier service
                </td>
            </tr></table>
            <button id="submit" class="btn btn-primary" type="submit">Finish order</button>
        </form>
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
