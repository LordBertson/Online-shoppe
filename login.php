<!DOCTYPE html>
<html lang="en">
<head>
    <?php require './repetitive/head.html'?>
    <title>KuchINÉ - Login</title>
    <?php require './repetitive/style.html'?>
    <style>
        #fbutton{
            display: inline-block;

        }
        #fbutton i{
            color:blue;
            font-size: 55px;
        }
        #submit{
            margin-bottom:35px;
        }

        .form-control{
            width: 20vw;
        }
    </style>
</head>

<body>
    <?php
    require_once __DIR__ . '/vendor/autoload.php'; 
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    session_start();
    $email = "";
    $password1 = "";
    $failedLogin = false;
    $userExists = false;

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
            if(isset($_GET['status'])){
            if($_GET['status']=='user_exists'){
                $userExists = true;
            }
            }
            $link->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
            if(empty($_POST)==false){
                $email = $_POST["email"];
                $password1 = $_POST["password"];
                $hashedpass = hash('sha256',$password1);
                $stmt = $link -> prepare('SELECT * FROM Users WHERE email=?');
                    $stmt -> bindParam(1, $email);
                    $stmt -> execute();
                    $data = $stmt-> fetch();
                    if($stmt->rowCount() == 0){
                        $failedLogin = true;
                    }else{
                        if($email==$data["email"] && $hashedpass==$data["passwordHash"]){                                                                                                                                                                        
                            $_SESSION["email"] = $email;
                            $_SESSION["id"] = $data['userID'];
                            $_SESSION["privilege"] = $data['privilege'];
                            header("Location: ./shop.php");
                            exit;
                        }
                        $failedLogin = true;
                    }

            }
        }
        require './unsafe/creditals.php';
           
        
        $helper = $fb->getRedirectLoginHelper();
        
        $permissions = ['email']; // Optional permissions
        $loginUrl = $helper->getLoginUrl('https://eso.vse.cz/~vybp01/online-shoppe/fb-callback.php', $permissions);
    ?>
    <?php require './repetitive/header2.html'?>
    <main class="container">
    <br>
    <?php if($failedLogin){echo '<font color="red">Invalid creditals!</font><br>';} ?><?php if($userExists){echo '<font color="red">User already exists, try using email and password!</font><br>';} ?>
    <h1 class="text-center">Login</h1>
    <div class="row justify-content-center">
        <form id="form" class="form-signup" method="POST" action="./login.php">
            <div class="form-group">
                <label>E-mail</label>
                <input class="form-control" name="email" value="<?php if(isset($_SESSION['email'])){echo $_SESSION['email'];} ?>">
            </div>
            <div class="form-group">
                <label>Password</label>
                <input class="form-control" type="password" name="password" value="">
            </div>
            <button id="submit" class="btn btn-primary" type="submit">Login</button>
            <div id='fbutton'><a href='<?php echo htmlspecialchars($loginUrl)?>'><i class="fab fa-facebook-square"></i></a></div>
            </form>
    </div>
</main>
    <footer></footer>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.0/js/bootstrap.min.js"></script>
    </body>
</html>
