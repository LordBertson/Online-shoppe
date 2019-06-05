<!DOCTYPE html>
<html lang="en">
<?php session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
        $url = "./shop.php";
        $page = 1;
        if(isset($_GET['page'])){
            if(is_numeric($_GET['page'])){
                $page = $_GET['page'];
            }
        }
        if(isset($_GET['orderBy'])){
            $url = $url."?orderBy=".$_GET['orderBy'];
        }
        if(isset($_GET['category'])){
            if($url=="./shop.php"){
                $url = $url."?category=".$_GET['category'];
            }else{
                $url = $url."&category=".$_GET['category'];
            }
        }
        $urlp = $url;
        if(isset($_GET['page'])){
            if($url=="./shop.php"){
                $url = $url."?page=".$_GET['page'];
            }else{
                $url = $url."&page=".$_GET['page'];
            }
        }

        if(!isset($_SESSION['id']) and isset($_GET["item"])){
            header("Location: ./login.php");
        }else if(isset($_SESSION['id']) and isset($_GET["item"])){
            $userID = $_SESSION["id"];
        }
        if(isset($_GET['status'])){
            if($_GET['status']=='logout'){
                session_destroy();
                header("Location: ./login.php");
                exit;
            }}
            require './repetitive/dbconnect.php';
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false
            ];
            $dsn = "mysql:host=$server;dbname=$database;charset=$charset";
            try {
                 $link = new PDO($dsn, $user, $pass, $options);
            } catch (\PDOException $e) {
                 throw new \PDOException($e->getMessage(), (int)$e->getCode());
            } 
                if (!$link) {
                    printf($messErr_connectionDatabaseFailed);
                    printf("<br />");
                }else{
                    $link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
                    if(isset($_GET['category'])){
                        $category = $_GET['category'];
                        $stmt = $link -> prepare('SELECT name FROM Items WHERE category = ? AND available=1');
                        $stmt -> execute([$category]);
                        $items = $stmt-> fetchAll();
                    }else{
                        $stmt = $link -> prepare('SELECT name FROM Items WHERE available = 1');
                        $stmt -> execute();
                        $items = $stmt-> fetchAll();
                    }
                    $lastpage = ceil(count($items)/9);
                    }
        if(isset($_GET['item'])){
            if(is_numeric($_GET["item"])){
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
                        $cartID = $cart["cartID"];
                        $item = $_GET["item"];
                        $stmt= $link->prepare('SELECT * FROM ItemsInCarts WHERE itemID = ? AND cartID = ?');
                        $stmt->execute([$item,$cartID]);
                        $cart = $stmt-> fetch();
                        $quantity = $cart['quantityAdded'] + 1;
                        if(empty($cart)){
                            $stmt= $link->prepare('INSERT INTO ItemsInCarts (itemID,cartID,quantityAdded) VALUES (?,?,1)');
                            $stmt->execute([$item,$cartID]);
                        } else {
                            $stmt= $link->prepare('UPDATE ItemsInCarts SET quantityAdded = ? WHERE itemID = ? AND cartID = ?');
                            $stmt->execute([$quantity,$item,$cartID]);
                        }
                        header("Location: ".$url);
                        unset($_SESSION['url']);
            }
        }
?>
<head>
    <?php require './repetitive/head.html'?>
    <title>KuchINÉ - Login</title>
    <?php require './repetitive/style.html'?>
    <?php require './repetitive/style-dropdown.html'?>
    <style>
    a{
        padding:0;
    }
    .container{
                box-sizing: content-box;
                padding:0;
                padding-left:19vw;
                
                height: 100vh;
                width: 80vw;
            }
    .window{
        height: 30w;
        width: 24vw;
        background-color: #909090;
        margin: 0.5vw;
        padding: 1vw;
    }
    .image{
        height:22vw;
        width: 22vw;
        background-color: white;
    }
    .item{
        position: relative;
        top: 50%;
        transform: translateY(-50%);
        width: auto;
        height: auto;
        max-width: 100%;
        max-height: 100%;
    }
    .itemInfo{
        width:21vw;
        margin-left:1vw;
        height: 7vw;
    }
    .itemName{
        color: black;
        font-size:1.2vw;
        text-align: left;
    }
    .itemPrice{
        color: white;
        font-size:1.7vw;
        text-align:left;
    }
    .cartButton{
        color:white;
        font-size:1.7vw;
        width:2vw;
        text-align:right;
    }
    li {
        display: inline;
    }
    li a{
        color: black;
    }
    ul{
        padding:0;
        margin:0;
        width: auto;
        color: black;
        text-align: right;

    }
    #pages{
        padding:0;
        width: 100%;
        color: black;
        font-size: 20px;
        text-align: center;
        margin: 0;
    }
    #under{
        text-decoration: underline;
    }
    </style>
</head>
    <body>
    <?php
    #DB #######################
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
            $toshow = ($page-1)*9;
                if(isset($_GET['category']) and isset($_GET['orderBy'])){
                    $category = $_GET['category'];
                    $orderBy = $_GET['orderBy'];
                    if($orderBy == 'descending'){
                        $stmt = $link -> prepare('SELECT * FROM Items WHERE category = ? AND available=1 ORDER BY price DESC, itemID DESC LIMIT '.$toshow.',9');
                        $stmt -> execute([$category]);
                        $data = $stmt-> fetchAll();
                    } else if($orderBy == 'ascending'){
                        $stmt = $link -> prepare('SELECT * FROM Items WHERE category = ? AND available=1 ORDER BY price ASC, itemID DESC LIMIT '.$toshow.',9');
                        $stmt -> execute([$category]);
                        $data = $stmt-> fetchAll();    
                    }
                } else if(isset($_GET['category']) and !isset($_GET['orderBy'])){
                    $category = $_GET['category'];
                    $stmt = $link -> prepare('SELECT * FROM Items WHERE category = ? AND available=1 ORDER BY itemID DESC LIMIT '.$toshow.',9');
                    $stmt -> execute([$category]);
                    $data = $stmt-> fetchAll();
                } else if(!isset($_GET['category']) and isset($_GET['orderBy'])){
                    $orderBy = $_GET['orderBy'];
                    if($orderBy == 'descending'){
                        $stmt = $link -> prepare('SELECT * FROM Items WHERE available=1 ORDER BY price DESC, itemID DESC LIMIT '.$toshow.',9');
                        $stmt -> execute();
                        $data = $stmt-> fetchAll();
                    } else if($orderBy == 'ascending'){
                        $stmt = $link -> prepare('SELECT * FROM Items WHERE available=1 ORDER BY price ASC, itemID DESC LIMIT '.$toshow.',9');
                        $stmt -> execute();
                        $data = $stmt-> fetchAll();    
                    }
                } else {
                    $stmt = $link -> prepare('SELECT * FROM Items WHERE available=1 ORDER BY itemID DESC LIMIT '.$toshow.',9');
                    $stmt -> execute([(int)$toshow]);
                    $data = $stmt-> fetchAll();
                }
            }
            

    ?>
    <?php require './repetitive/header.php'?>
    <div id="sidebar-nav" class="sidebar">
        <button class="dropdown-btn">Categories 
            <i class="fa fa-chevron-circle-down"></i>
        </button>
        <div class="dropdown-container">
            <?php if(isset($_GET['orderBy'])){ ?>
                <a href="./shop.php?orderBy=<?php echo $_GET['orderBy'] ?>&category=knives">Knives</a>
                <a href="./shop.php?orderBy=<?php echo $_GET['orderBy'] ?>&category=cutlery">Cutlery</a>
                <a href="./shop.php?orderBy=<?php echo $_GET['orderBy'] ?>&category=plates">Plates</a>
                <a href="./shop.phporderBy=<?php echo $_GET['orderBy'] ?>&category=bowls">Bowls</a>
                <a href="./shop.php?orderBy=<?php echo $_GET['orderBy'] ?>&category=boards">Cutting Boards</a>
                <a href="./shop.php?orderBy=<?php echo $_GET['orderBy'] ?>&category=spatulas">Spatulas</a>
                <a href="./shop.php?orderBy=<?php echo $_GET['orderBy'] ?>&category=scoops">Scoops</a>
                <a href="./shop.php?orderBy=<?php echo $_GET['orderBy'] ?>&category=pans">Pans</a>
                <a href="./shop.php?orderBy=<?php echo $_GET['orderBy'] ?>&category=pots">Pots</a>
            <?php }else{?>
                <a href="./shop.php?category=knives">Knives</a>
                <a href="./shop.php?category=cutlery">Cutlery</a>
                <a href="./shop.php?category=plates">Plates</a>
                <a href="./shop.php?category=bowls">Bowls</a>
                <a href="./shop.php?category=boards">Cutting Boards</a>
                <a href="./shop.php?category=spatulas">Spatulas</a>
                <a href="./shop.php?category=scoops">Scoops</a>
                <a href="./shop.php?category=pans">Pans</a>
                <a href="./shop.php?category=pots">Pots</a>
            <?php }?>
        </div>
        <button class="dropdown-btn">Shopping Cart
            <i class="fa fa-chevron-circle-down"></i>
        </button>
        <div class="dropdown-container">
            <?php
            if(isset($_SESSION['email'])){
                $userID = $_SESSION['id'];
                $stmt= $link->prepare('SELECT userID, quantityAdded, name FROM (SELECT userID, itemID, quantityAdded FROM ShoppingCarts  JOIN ItemsInCarts ON ShoppingCarts.cartID = ItemsInCarts.cartID) AS A JOIN Items ON A.itemID = Items.itemID WHERE userID = ? AND available=1');
                $stmt->execute([$userID]);
                $cart = $stmt-> fetchAll();
                foreach($cart as $item){
                    echo '<a href=\'home.php\'>'.$item['quantityAdded'].'x '.$item['name'].'</a>';
                }
                if(empty($cart)){
                    echo 'Empty!';
                }
            } else {
                echo '<span style=\'display: inline;\'>You must login to see items in your cart.</span>';
            }
            ?>
        </div>
    </div>
    
    <main id="container" class="container">
    <ul>
        <?php if(isset($_GET['category'])){ ?>
        <li>price</li>
        <li><a href="shop.php?orderBy=ascending&category=<?php echo $_GET['category'] ?>"><i class="fa fa-caret-up" aria-hidden="true"></i></a></li>
        <li><a href="shop.php?orderBy=descending&category=<?php echo $_GET['category'] ?>"><i class="fa fa-caret-down" aria-hidden="true"></i></a></li>
        <?php }else{?>
        <li>price</li>
        <li><a href="shop.php?orderBy=ascending"><i class="fa fa-caret-up" aria-hidden="true"></i></a></li>
        <li><a href="shop.php?orderBy=descending"><i class="fa fa-caret-down" aria-hidden="true"></i></a></li>
        <?php }?>
    </ul>
    <table>
    <?php
    $i=0;
    foreach($data as $item){
        if($i%3 == 0){
        echo "<tr>";
        }
            echo "<td>";
            ?>
            <div class="window">
            <a href='./item.php?id=<?php echo $item['itemID']?>'>
                <div class="image">
                <img class="item" src="<?php echo $item['image']?>" alt="item image">
                </div>
            </a>
                <table class="itemInfo">
                    <tr>
                        <td colspan="2"  class="itemName"><?php echo $item['name']?></td>
                    </tr>
                    <tr>
                        <td  class="itemPrice">$<?php echo $item['price']?></td>
                        <?php if($url == './shop.php'){?>
                        <td class="cartButton"><div><a href="./shop.php<?php echo '?item='.$item['itemID']?>"><i class="fas fa-cart-plus"></i></a></div></td>
                        <?php }else{ ?>
                        <td class="cartButton" ><div><a href="<?php echo $url ?><?php echo '&item='.$item['itemID']?>"><i class="fas fa-cart-plus"></i></a></div></td>
                        <?php }?>
                    </tr>
                </table>
            </div>
            <?php
            echo "</td>";
        if($i%3 == 2){
        echo "</tr>";
        }
    $i=$i+1;
    }
    ?>
    </table>
    <ul id='pages'>
        <?php for($x=1;$x<=$lastpage;$x++){ ?>
            <li <?php if($x==$page){echo 'id=\'under\'';} ?>><a href=<?php if($urlp=='./shop.php'){echo $urlp.'?page='.$x;}else{echo $urlp.'&page='.$x;}?>><?php echo $x ?></a></li>
        <?php } ?>
    </ul>
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
        sidebar.style.position = "fixed";
        sidebar.style.top = "38px";
        container.style.paddingTop = "38px";
        
    } else {
        navbar.classList.remove("sticky");
        sidebar.style.position = "absolute";
        sidebar.style.top = navbar.style.bottom;
        container.style.paddingTop = "0px";
    }
    }
    </script>
    
    <script>
        var dropdown = document.getElementsByClassName("dropdown-btn");
        var i;

        for (i = 0; i < dropdown.length; i++) {
        dropdown[i].addEventListener("click", function() {
            this.classList.toggle("active");
            var dropdownContent = this.nextElementSibling;
            if (dropdownContent.style.display === "block") {
            dropdownContent.style.display = "none";
            } else {
            dropdownContent.style.display = "block";
            }
        });
        }
    </script>
    </body>
</html>
