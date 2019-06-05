<!DOCTYPE html>
<html lang="en">
<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


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
        if(isset($_GET['id'])){
            $itemID = $_GET['id'];
            $stmt= $link->prepare('SELECT * FROM Items WHERE itemID = ?');
            $stmt->execute([$itemID]);
            $item = $stmt-> fetch();
        } else{
            header("Location: ./shop.php");
        }
            if(isset($_GET['cart']) and isset($_GET['id'])){
                if(!isset($_SESSION['id'])){
                    header("Location: ./login.php");
                    exit;    
                }
                if($_GET['cart']=='true'){
                    $itemID = $_GET['id'];
                    $userID = $_SESSION["id"];
                    $stmt = $link -> prepare('SELECT * FROM ShoppingCarts WHERE userID=?');
                        $stmt -> bindParam(1, $userID);
                        $stmt -> execute();
                        $cart = $stmt-> fetch();
                        if(empty($cart)){
                            $stmt= $link->prepare('INSERT INTO ShoppingCarts (userID) VALUES (?)');
                            $stmt->execute([$userID]);
                            $stmt = $link -> prepare('SELECT * FROM ShoppingCarts WHERE userID=?');
                            $stmt -> bindParam(1, $userID);
                            $stmt -> execute();
                            $cart = $stmt-> fetch();
                        }
                    $stmt= $link->prepare('SELECT * FROM ItemsInCarts WHERE itemID = ? AND cartID = (SELECT cartID FROM ShoppingCarts WHERE userID=?)');
                    $stmt->execute([$itemID,$userID]);
                    $addedItem = $stmt-> fetch();
                    if(!empty($addedItem)){
                        $quantity = $addedItem['quantityAdded'] + 1;
                        $stmt= $link->prepare('UPDATE ItemsInCarts SET quantityAdded = ? WHERE itemID = ? AND cartID = (SELECT cartID FROM ShoppingCarts WHERE userID=?)');
                        $stmt->execute([$quantity,$itemID,$userID]);
                    }else{
                        $quantity = $addedItem['quantityAdded'] + 1;
                        $stmt= $link->prepare('INSERT INTO ItemsInCarts SET quantityAdded = ?, itemID = ?, cartID = (SELECT cartID FROM ShoppingCarts WHERE userID=?)');
                        $stmt->execute([$quantity,$itemID,$userID]);
                    }
                header("Location: ./item.php?id=$itemID");
                }
            }

        }
?>
<head>
    <?php require './repetitive/head.html'?>
    <title>KuchINÉ - Shopping Cart</title>
    <?php require './repetitive/style.html'?>
    <style>
    .container{
                height: 100%;
                width: 83.5vw;
                box-sizing: content-box;
            }
    

    .row {
        padding-top: 3vw;
        display: flex;
        flex-wrap: nowrap;
    }

    /* Create two equal columns that sits next to each other */
    .column {
        padding: 0vw;
        min-height: 30vw;
        position: relative;
    }
    .left {
        width: 26vw;
        padding-right:2vw;
    }

    .right {
        width: 55vw;
        font-size: 1.2vw;
    }
    .image{
        height:25vw;
        width: 25vw;
        background-color: white;
        border: 3px #C0C0C0 solid;
    }
    #item{
        position: relative;
        top: 50%;
        transform: translateY(-50%);
        width: auto;
        height: auto;
        max-width: 100%;
        max-height: 100%;
    }
    #button{
        background-color: #222222;
        font-size:2vw;
        height: auto;
        width: auto;
        text-align: center;
        
    }
    table{
        width:54vw;
    }
    #priceAlign{
        text-align: right;
    }
    .buttonContainer{
        position:absolute;
        width: 100%;
        bottom: 0;
    }
    #desc{
        width:54vw; 
        word-wrap:break-word;
    }
    #name{
        font-size: 3vw;
        text-transform: uppercase;
        color: black;
    }
    </style>
</head>
<body>
    <?php require './repetitive/header.php'?>
    <main id="container" class="container">
    <div class="row">
        <div class="column left">
            <div class="image"><img id="item" src="<?php echo $item['image']?>" alt="item image"></div>
        </div>
        <div class="column right">
        <div id='name'><?php echo $item['name']?></div>
        <div id='desc'><?php echo $item['description']?></div>
            <div class='buttonContainer'>
            <table><tr><td>Available: <?php echo $item['quantity'] ?> pcs</td><td id='priceAlign'><h1>$<?php echo $item['price']?></h1></td></tr></table>
            <a href='./item.php?id=<?php echo $item['itemID']?>&cart=true'>
                    <div id='button'><i class="fas fa-cart-plus"></i>ADD TO CART</div>
            </a>
            </div>
        </div>
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
