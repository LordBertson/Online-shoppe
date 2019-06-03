<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>KuchINÉ - Shopping Cart</title>
    <link rel="stylesheet" href="https://bootswatch.com/4/lux/bootstrap.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.1/css/all.css">
    <link rel="stylesheet" href="./css/styles.css"> 
</head>
<body>
    <?php   
    session_start();
    if(!isset($_SESSION['email'])) {
        header("Location: ./login.php");
    }
    #DB #######################
    $server="localhost";
    $user="php";
    $password="php";
    $database="php";
    $messErr_connectionDatabaseFailed = "Error : connection failed. Please try later.";
         
    $link = new mysqli($server, $user, $password, $database);
    
    if (!$link) {
        printf($messErr_connectionDatabaseFailed);
        printf("<br />");
    }else{
        $email = $_SESSION["email"];
        $stmt = $link -> prepare('SELECT privilege FROM users WHERE email=?');
            $stmt -> bind_param('s', $email);
            $stmt -> execute();
            $stmt->store_result();
            $stmt->bind_result($privilege);
            $stmt->fetch();
            if($privilege!=3){
                header("Location: ../home.php");
            }
    }
    $stmt = $link->prepare('SELECT * FROM users');
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
    $arr[$row["email"]] = $row["privilege"];   
        if(empty($_POST)==false){
            foreach ($arr as $key => $value) {
                $newprivilege = $_POST[str_replace(".", "_", $key)];
                if($value != $newprivilege){
                    $stmt = $link -> prepare('UPDATE users SET privilege = ? WHERE email=?;');
                    $stmt -> bind_param('is', $newprivilege, $key);
                    $stmt -> execute();
                    $stmt -> close();
                }
            }
        }
    }
    ?>
     <style>
    body{
        background-color: #f0f0f0;
    }
    input{
        outline: 2px #c0c0c0 solid;
        height: 40px;
    }
    nav{
        height: 50px;
    }
    a {
    border: none;
    color: white;
    opacity: 0.6;
    transition: 0.3s;
    }

    a:hover {opacity: 1}
    </style>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <a class="navbar-brand" href="#">Users</a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarColor01" aria-controls="navbarColor01" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarColor01">
              
            </div>
        </nav>
    </header><main class="container">
    <br>
    <h1 class="text-center">Users</h1>
    <div class="row justify-content-center">
        <form class="form-signup" method="POST" action="./users.php">
            <div>
            <table border="1" cellpadding="0" cellspacing="0" width="300" bgcolor="#f0f0f0" align="right" class="logintable">
            <tr>
            <td><div style="float:left"> Name</div></td>
            <td><div style="float:left">Privilege</div></td>
    <?php $arr = [];
    $stmt = $link->prepare('SELECT * FROM users');
    $stmt->execute();
    $result = $stmt->get_result();
    while($row = $result->fetch_assoc()) {
    $arr[$row["email"]] = $row["privilege"];
    ?>
            <tr>
            <td><div> <?php echo $row["email"]?> </div></td>
            <td><input type="text" name="<?php echo $row["email"]?>" value="<?php echo $row["privilege"];?>"></td>
            </tr>
    <?php }
    $stmt->close();
    ?>
            </table>
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