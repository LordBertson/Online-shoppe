<!DOCTYPE html>
<html lang="en">
<head>
    <?php require './repetitive/head.html'?>
    <?php require './repetitive/style.html'?>
    <title>KuchINÉ - Shopping Cart</title>
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
        width: 100%;

    }
    button{
        width: 100%;
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
</head>
<body>
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

        if(!empty($_POST)){
            $i=0;
            $j=0;
            foreach ( $_POST as $key => $value){
                $i=$i+1; 
                if($value != 0){
                    $stmt = $link -> prepare("UPDATE ItemsInCarts SET quantityAdded=? WHERE itemID=? AND cartID=(SELECT cartID FROM ShoppingCarts WHERE userID=?)");
                    $stmt->execute([$value,$key,$userID]);
                } else{
                    $j=$j+1;
                    $stmt = $link -> prepare("DELETE FROM ItemsInCarts WHERE itemID=? AND cartID=(SELECT cartID FROM ShoppingCarts WHERE userID=?)");
                    $stmt->execute([$key,$userID]);
                }
            }
            if($i==$j){
                header("Location: ./home.php");
                exit; 
            }
            $_SESSION['status']='ok';
            header("Location: ./order.php"); 

        }
        $stmt= $link->prepare('SELECT A.itemID, userID, quantityAdded, price, name FROM (SELECT userID, itemID, quantityAdded FROM ShoppingCarts  JOIN ItemsInCarts ON ShoppingCarts.cartID = ItemsInCarts.cartID) AS A JOIN Items ON A.itemID = Items.itemID WHERE userID = ?');
        $stmt->execute([$userID]);
        $cart = $stmt-> fetchAll();
    }
?>
    <?php require './repetitive/header.php'?>
    <main id="container" class="container">
    <?php if(!empty($cart)){ ?>
        <h1>Shopping Cart</h1>
        <form id="form" class="form-signup" style="width:auto" method="POST" action="./home.php">
        <table>
        <tr>
            <th>Name</th>
            <th>Quantity</th>
            <th>Per Piece</th>
        </tr>
        <?php
        foreach($cart as $item){ 
        ?>
                        <tr id='<?php echo $item['itemID'] ?>'>
                            <td><?php echo $item['name']?></td>
                            <td><input class="form-control" name="<?php echo $item['itemID'] ?>" value="<?php echo $item['quantityAdded']?>"></td>
                            <td id='price<?php echo $item['itemID'] ?>'>$<?php echo $item['price']?></td>
                        </tr>
        <?php 
        } ?>
        </table>
        <button class="btn btn-primary" type="submit">TO ORDER COMPLETION</button>
        </form>
    <?php }else{ ?>
        <h2>Your cart is empty!</h2>
    <?php } ?>
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
