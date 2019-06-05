<!DOCTYPE html>
<html lang="en">
<head>
    <?php require './repetitive/head.html'?>
    <?php require './repetitive/style.html'?>
    <title>KuchINÉ - Registration</title>
</head>
<body>
    <?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    session_start();
    $name = "";
    $email = "";
    $password1 = "";
    $password2 = "";
    $phoneno = "";
    $allset = false;
    $namebool = true;
    $mailbool = true;
    $pass1bool = true;
    $pass2bool = true;
    $duplicatebool = true;
    $phonebool = true;

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

            if(empty($_POST)==false){
                $allset = true;
                $name = $_POST["name"];
                $email = $_POST["email"];
                $phoneno = $_POST["phone"];
                $password1 = $_POST["password1"];
                $password2 = $_POST["password2"];
                
                if(!$name){
                    if(!ctype_alnum($name)){
                        $errname = "Name must be alphanumeric!";
                        $namebool = false;
                        $allset = false;
                    }
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $erremail = "Invalid email format!";
                    $mailbool = false;
                    $allset = false;
                }
                $match = preg_match('/\+(\d){3}( )?(\d){3}( )?(\d){3}( )?(\d){3}/', $phoneno, $matched);
                if (!($match)) {
                    $errphone = "Invalid phone number format!";
                    $phonebool = false;
                    $allset = false;
                } else{
                    $phoneno = preg_replace("/ /", "", $phoneno);
                    $phoneno = preg_replace("/\+/", "", $phoneno);
                }
                if ($password1 == ""){
                    $errpass1 = "Password can't be empty!";
                    $pass1bool = false;
                    $allset = false;
                }
                if ($password1 != $password2) {
                    $errpass2 =  "Passwords don't match!";
                    $pass2bool = false;
                    $allset = false;
                }
                $stmt = $link -> prepare('SELECT * FROM Users WHERE email=?');
                    $stmt -> bindParam(1, $email);
                    $stmt -> execute();
                    $result = $stmt->rowCount();
                    if($result != 0){
                        $errduplicate = "User with this email already exists!";
                        $duplicatebool = false;
                        $allset=false;
                    }
                if($allset == false){

                }else{
                    $hashedpass = hash('sha256',$password1);
                    $stmt = $link -> prepare("INSERT INTO Users (email, userName, passwordHash, privilege) VALUES (?,?,?,1)");
                    $stmt->execute([$email,$name,$hashedpass]);
                    print_r($link->errorInfo());
                    $_SESSION["email"] = $email;
                    $_SESSION["pass"] = $password1;
                    header("Location: ./login.php");
                }
            }
        }


    ?>
    <?php require './repetitive/header2.html'?>
    <main class="container">
    <br>
    <h1> Registration form </h1>
    <div class="row justify-content-center">
        <form id="form" class="form-signup" style="width:220px" method="POST" action="./formular.php">
            <div class="form-group">
                <label>Name* </label>
                <input class="form-control" name="name" value="<?php echo $name; ?>">
                <small class="text-muted">Example: John Smith
                </small>
                <div>
                <div class="error"><?php if($namebool == false){echo $errname;}?></div>
                </div>
            </div>
            <div class="form-group">
                <label>E-mail*</label>
                <input class="form-control" name="email" value="<?php echo $email; ?>">
                <small class="text-muted">Example: name@mailprovider.com</small>
                <div>
                <div class="error"><?php if($mailbool == false){echo $erremail;}elseif($duplicatebool == false){echo $errduplicate;}?></div>
                </div>
            </div>
            <div class="form-group">
                <label>Phone Number*</label>
                <input class="form-control" name="phone" value="<?php echo $phoneno; ?>">
                <small class="text-muted">Format: +421 123 456 789</small>
                <div>
                <div class="error"><?php if($phonebool == false){echo $errphone;}?></div>
                </div>
            </div>
            <div class="form-group">
                <label>Password*</label>
                <input class="form-control" type="password" name="password1" value="">
                <small class="text-muted">Enter a strong password.</small>
                <div>
                <div class="error"><?php if($pass1bool == false){echo $errpass1;}?></div>
                </div>
            </div>
            <div class="form-group">
                <label>Password again*</label>
                <input class="form-control" type="password" name="password2" value="">
                <small class="text-muted">Repeat password.</small>
                <div>
                <div class="error"><?php if($pass2bool == false){echo $errpass2;}?></div>
                </div>
            </div>
            <button class="btn btn-primary" type="submit">Submit</button>
        </form>
    </div>
</main>
    <footer></footer>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.0/js/bootstrap.min.js"></script>
    </body>
</html>
