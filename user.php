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
        input,textarea{
            outline: 2px #c0c0c0 solid;
            height: 30px;
            max-width:20vw;
            min-width:20vw;
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
$namebool = true;
$mailbool1 = true;
$mailbool2 = true;
$passbool = true;
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
        $userID = $_SESSION['id'];
        $link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $stmt = $link -> prepare('SELECT * FROM Users WHERE userID=?');
                    $stmt -> bindParam(1, $userID);
                    $stmt -> execute();
                    $data = $stmt-> fetch();
                    $name = $data['userName'];
                    $email = $data['email'];
                    $privilege_prev = $data['privilege'];
    }
    if(!empty($_POST)){
        if($_POST['email']){
            $email = $_POST['email'];
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $mailbool1 = false;
            }
        }
        if($_POST['name']){
            $name = $_POST['name'];
            if(!preg_match("/^[a-zA-Z ]*$/",$name)){
                $namebool = false;
            }
        }
        if($_POST['password1'] or $_POST['password2']){
            $password1 = $_POST['password1'];
            $password2 = $_POST['password2'];
            if($password1 == $password2){
                $hashedpass = hash('sha256',$password1);
                $stmt = $link -> prepare('SELECT * FROM Users WHERE email=?');
                    $stmt -> bindParam(1, $email);
                    $stmt -> execute();
                    $data = $stmt-> fetch();
                    if(empty($data) or $_SESSION['email']==$email){
                        if($namebool and $mailbool1){
                            $stmt = $link -> prepare('UPDATE Users SET email=?, userName=?, passwordHash=? WHERE userID =? ');
                            $stmt -> execute([$email,$name,$hashedpass,$userID]);
                        }
                    }
                    else{
                        $mailbool2 = false;
                    }
            } else{
                $passbool = false;
            }
        }else{
            $stmt = $link -> prepare('SELECT * FROM Users WHERE email=?');
                    $stmt -> bindParam(1, $email);
                    $stmt -> execute();
                    $data = $stmt-> fetch();
                    if(empty($data) or $_SESSION['email']==$email){
                        if($namebool and $mailbool1){
                            $stmt = $link -> prepare('UPDATE Users SET email=?, userName=? WHERE userID =? ');
                            $stmt -> execute([$email,$name,$userID]);
                        }
                    }else{
                        $mailbool2 = false;
                    }
        }
    }
?>
    <?php require './repetitive/style.html'?>
    <?php require './repetitive/header.php'?>
    <main id="container" class="container">
    <font color="red"><?php if($namebool == false){echo 'Error: Name must only contain alphanumeric characters and spaces!<br>';}?></font>
    <font color="red"><?php if($mailbool1 == false){echo 'Error: Not a valid email!<br>';}elseif($mailbool2 == false){echo 'Error: User with this email is already registered!<br>';}?></font>
    <font color="red"><?php if($passbool == false){echo 'Error: Passwords do not match!<br>';}?></font>
    <h1 align='center'> Profile </h1>
    <div class="row justify-content-center">
        <form id="edit" class="form" method="POST" action="./user.php">
            <div class='form-group'>
            <label>Name</label>
            <input class="form-control" name="name" value="<?php echo $name ?>">
            </div>
            <div class='form-group'>
            <label>Email</label>
            <input class="form-control" name="email" value="<?php echo $email ?>">
            </div>
            <div class='form-group'>
            <label>Password</label>
            <input class="form-control" name="password1" value="">
            </div>
            <div class='form-group'>
            <label>Repeat password</label>
            <input class="form-control" name="password2" value="">
            </div>
            <button class="btn btn-primary" type="submit">Save changes</button>
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
