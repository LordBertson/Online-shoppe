<!DOCTYPE html>
<html lang="en">
<head>
    <?php require './repetitive/head.html'?>
    <title>KuchINÉ - Shopping Cart</title>
    <?php require './repetitive/style.html';
    session_start();?>
    <style>
        h1{
            padding-top:20px;
        }
        .image{
        margin-top: 2vw;
        height:30vw;
        width: 30vw;
        background-color: white;
        outline: 2px #c0c0c0 solid;
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
        .radio, .radio-inline{
            padding-top: 10px;
            margin:0;
            outline: 0;
            height:auto;
        }
    </style>
</head>
<body>
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if(!isset($_SESSION['email'])) {
    header("Location: ./login.php");
    exit;
}
if($_SESSION['privilege']!=3){
    header("Location: ./shop.php");
    exit;
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
$lenBool=true;
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
        if(strlen($description)>1500){
            $lenBool = false;
        }
        $available = $_POST['available'];
        echo $available;

        if($helperBool and $priceBool and $quantityBool and $lenBool){
            if(!isset($_SESSION['itemID'])){
                $stmt = $link -> prepare("INSERT INTO Items (name, image, price, quantity, category, description, available) VALUES (?,?,?,?,?,?,?)");
                $stmt->execute([$itemName,$imgurl,$price,$quantity,$category,$description,$available]);
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
                $stmt = $link -> prepare("UPDATE Items SET name=?, image=?, price=?, quantity=?, category=?, description=?, lockExpires=0, editor=0, available=? WHERE itemID=?");
                $stmt->execute([$itemName,$imgurl,$price,$quantity,$category,$description,$available,$itemID]);
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
    <?php require './repetitive/header.php'?>
    <main id="container" class="container">
    <div class='error'>
    <?php if($helperBool == false){echo "Check that all values are filled";}?>   
    <?php if($priceBool == false){echo "Check that price is numeric";}?>
    <?php if($quantityBool == false){echo "Check that quantity is numeric";}?>
    <?php if($lenBool == false){echo "Description can be no longer than 1500 characters";}?>                   
    </div>
    <h1> Edit Items </h1>
    <div class="row justify-content-center">
        <form id="edit" class="form" method="POST" action="./edit_item.php">
            <div class="form-group">
            <div>
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
                Avaiable:
                <label class="radio-inline"><input class="radio" type="radio" name="available" value="1" <?php if(isset($data)) {if($data['available']==1){echo 'checked';}}?>>yes</label>
                <label class="radio-inline"><input class="radio" type="radio" name="available" value="0" <?php if(isset($data)) {if($data['available']==0){echo 'checked';}}?>>no</label>

                </div>
                </div>
                <div class="col">
                <label>Quantity</label>
                <input class="form-control" name="quantity" value="<?php echo $quantity ?>">
                <div>
                </div>
                </div>
            </div>
            <div id="url" class="form-group">
                <label>ImageURL</label>
                <input class="form-control" id="imgurl" name="imgurl" onChange="getImage();" value="<?php echo $imgurl ?>">
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea maxlength="500" id="desc" class="form-control" name="description"><?php echo $description ?></textarea>
                <small id='charactersRemaining' class="text-muted">500</small>
            </div>
            <button class="btn btn-primary" type="submit">Submit</button>
        </form>
        <div class="image">
        <img class="item" id='image' src="<?php if($_POST['imgurl']){echo $imgurl;}else{echo './imgs/icon.png';}?>" onerror="this.src='./imgs/icon.png';" alt="item" />
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
    window.onload = function() {
        getImage();
    };
    var image = document.getElementById("image");
    var url = document.getElementById("imgurl");
    function getImage(){
        image.src = url.value;
    }
    </script>
    <script>
    var el;                                                    

    function countCharacters(e) {                                    
    var textEntered, countRemaining, counter;          
    textEntered = document.getElementById('desc').value;  
    counter = (500 - (textEntered.length));
    countRemaining = document.getElementById('charactersRemaining'); 
    countRemaining.textContent = counter;      
    }
    el = document.getElementById('desc');                   
    el.addEventListener('keyup', countCharacters, false);
    </script>
</body>
</html>
