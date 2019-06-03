<!DOCTYPE html>
<html lang="en">
<head>
    <?php require './repetitive/head.html'?>
    <?php require './repetitive/style.html'?>
    <title>KuchINÉ - Shopping Cart</title>
</head>
<body>
    <style>
        #lista{
            color:blue;
        }
        th,td{
            padding-right:10px;
            border-top: 2px solid #333333;
        }
        td{
            color:#333333;
        }
        th{
            color:#111111;
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
                align:center;
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
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
if(!isset($_SESSION['email'])) {
    header("Location: ./login.php");
}
if($_SESSION['privilege']!=3){
    header("Location: ./shop.php");
}

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
        $stmt = $link -> prepare('SELECT * FROM Items');
                    $stmt -> bindParam(1, $email);
                    $stmt -> execute();
                    $data = $stmt-> fetchAll();
    }



?>
    <?php require './repetitive/header.php'?>
    <main id="container" class="container">
    <?php
    if(isset($_GET['status'])){
        if($_GET['status'] == 'changed'){
            echo '<font color=\'red\'>Lock expired and other user started editing, no changes saved<font>';
        } elseif($_GET['status'] == 'locked') {
            echo '<font color=\'red\'>Some other user is editing this record<font>';
        }
    }
    ?>
    <h1 align='center'> Items </h1>
    <div class="row justify-content-center">
    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Category</th>
            <th>Price</th>
            <th>Quantity</th>
            <th>Description</th>
            <th>edit</th>
        </tr>
        <?php
        foreach($data as $item){ 
        ?>
        <tr>
            <td><?php echo $item['itemID']?></td>
            <td><?php echo $item['name']?></td>
            <td><?php echo $item['category']?></td>
            <td><?php echo $item['price']?></td>
            <td><?php echo $item['quantity']?></td>
            <td><?php echo $item['description']?></td>
            <td><a id="lista" href="./edit_item.php?id=<?php echo $item['itemID'] ?>">edit</a></td>
        </tr>
        <?php } ?>
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th><a href='./edit_item.php' id='id'>+</a></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
    </table>
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
