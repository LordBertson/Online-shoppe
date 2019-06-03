<!DOCTYPE html>
<html lang="en">
<head>
    <?php require './repetitive/head.html'?>
    <title>KuchINÉ - Shopping Cart</title>
</head>
<body>
    <style>
        h1{
            padding-top:20px;
        }
        #image{
            margin-top:27px;
            height:auto;
            width: 400px;
            max-height:400px;
            max-width: 400px;
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
$requireConsistencyCheck = false;
$helperBool=true;
$priceBool=true;
$quantityBool=true;
$itemID='';
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
    }
    if(isset($_GET['id'])){
        $_SESSION['itemID'] = $_GET['id'];
        $itemID=$_SESSION['itemID'];
        $timeStartedEditing = time();
        $timeLockExpires = $timeStartedEditing + 60; ##add 60*5 if needed
        $editorID = $_SESSION['id'];
        $stmt = $link -> prepare('SELECT * FROM Items WHERE itemID=?');
                    $stmt -> bindParam(1, $itemID);
                    $stmt -> execute();
                    $data =  $stmt-> fetch();
                    if($data['lockExpires']>time() and $_SESSION['id']!=$data['editor']){
                        header("Location: ./list_items.php?status=locked");
                        exit;
                    } elseif ($data['lockExpires']<time() and $_SESSION['id']!=$data['editor']){
                        $stmt = $link -> prepare('UPDATE Items SET lockExpires = ? , editor = ? WHERE itemID=?');
                        $stmt -> execute([$timeLockExpires, $editorID, $itemID]);
                    }
                    $itemName=$data['name'];
                    $category = $data['category'];
                    $price=$data['price'];
                    $quantity=$data['quantity'];
                    $imgurl=$data['image'];
                    $description=$data['description'];
    }

    if(empty($_POST)==false){
        $itemName=$_POST['itemName'];
        $category = $_POST['category'];
        $price=$_POST['price'];
        $quantity=$_POST['quantity'];
        $imgurl=$_POST['imgurl'];
        $description=$_POST['description'];
        if(!$_POST['itemName']){
            $helperBool=false;
        }
        if(!$_POST['category']){
            $helperBool=false;
        }
        if(!$_POST['price']){
            $helperBool=false;
        }else if(!is_numeric($_POST['price'])){
            $priceBool=false;
        }
        if(!$_POST['quantity']){
            $helperBool=false;
        }else if(!is_numeric($_POST['quantity'])){
            $quantityBool=false;
        }
        if(!$_POST['imgurl']){
            $helperBool=false;
        }
        if(!$_POST['description']){
            $helperBool=false;
        }

        if($helperBool and $priceBool and $quantityBool){
            if(!isset($_SESSION['itemID'])){
                $stmt = $link -> prepare("INSERT INTO Items (name, image, price, quantity, category, description) VALUES (?,?,?,?,?,?)");
                $stmt->execute([$itemName,$imgurl,$price,$quantity,$category,$description]);
                header("Location: ./list_items.php");
                exit;
            }else{
                    $itemID = $_SESSION['itemID'];
                    $stmt = $link -> prepare('SELECT * FROM Items WHERE itemID=?');
                    $stmt -> bindParam(1,$itemID);
                    $stmt -> execute();
                    $data =  $stmt-> fetch();
                    if($data['editor']!=$_SESSION['id']){
                        header("Location: ./list_items.php?status=changed");
                        exit;
                    }
                $itemID = $_SESSION['itemID'];
                $stmt = $link -> prepare("UPDATE Items SET name=?, image=?, price=?, quantity=?, category=?, description=?, lockExpires=0, editor=0 WHERE itemID=?");
                $stmt->execute([$itemName,$imgurl,$price,$quantity,$category,$description,$itemID]);
                unset($_SESSION['itemID']);
                header("Location: ./list_items.php");
                exit;
            }
            $itemName='';
            $category='';
            $price='';
            $quantity='';
            $description='';
            $imgurl='';
        }
    }


?>
    <?php require './repetitive/style.html'?>
    <?php require './repetitive/header.php'?>
    <main id="container" class="container">
    <h1 align='center'> Edit Items </h1>
    <div class="row justify-content-center">
        <form id="edit" class="form" method="POST" action="./edit_item.php">
            <div class="form-group">
            <div>
            <font color="red"><?php if($helperBool == false){echo "Check that all values are filled";}?></font>    
            </div>
            <label>Name</label>
            <input class="form-control" name="itemName" value="<?php echo $itemName ?>">
            </div>
            <div class="form-group">
            <label>Category</label>
            <input class="form-control" name="category" value="<?php echo $category ?>">
            </div>
            <div class="form-row">
                <div class="col">
                <label>Price</label>
                <input class="form-control" name="price" value="<?php echo $price ?>">
                <div>
                <font color="red"><?php if($priceBool == false){echo "not a number";}?></font>
                </div>
                </div>
                <div class="col">
                <label>Quantity</label>
                <input class="form-control" name="quantity" value="<?php echo $quantity ?>">
                <div>
                <font color="red"><?php if($quantityBool == false){echo "not a number";}?></font>
                </div>
                </div>
            </div>
            <div id="url" class="form-group">
                <label>ImageURL</label>
                <input class="form-control" id="imgurl" name="imgurl" onChange="getImage();" value="<?php echo $imgurl ?>">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea id="desc" class="form-control" name="description"><?php echo $description ?></textarea>
            </div>
            <button class="btn btn-primary" type="submit">Submit</button>
        </form>
        <img id="image" src="<?php if($_POST['imgurl']){echo $imgurl;}else{echo 'imgs/icon.png';}?>" onerror="this.src='./imgs/icon.png';" alt="item" />
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
    <script>
    var image = document.getElementById("image");
    var url = document.getElementById("imgurl");
    function getImage(){
        image.src = url.value;
    }
    </script>
</body>
</html>
