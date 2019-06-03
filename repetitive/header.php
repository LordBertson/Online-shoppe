<header>
<div id="header" class="header">
    <div class="crop">
        <img src="./imgs/top-bar.jpg" alt="top">
        <a id="top" href="./shop.php" class="centered">KuchINÉ</div>
    </div>
</div>

<div id="navbar" class="navbar">
        <div class="navbar-left">
        <a href="./shop.php"><i class="fa fa-home"></i> Shop</a>
        <?php if(isset($_SESSION['email'])){?>
        <a href="./user.php">Logged in as: <?php echo $_SESSION['email'] ?></a>
        <?php } ?>
        <a href="./privacy-policy.php">Privacy policy</a>
        </div>
        <div class="navbar-right">
        <?php if(isset($_SESSION['privilege'])){ if($_SESSION['privilege']==3){ ?>
            <a href="./list_items.php"><i class="fa fa-terminal"></i> Edit Items</a>
            <a href="./list_orders.php"><i class="fa fa-check-square"></i> Orders</a>
        <?php }} ?>
        <?php if(!isset($_SESSION['id'])){ ?>
        <a href="./login.php"><i class="fas fa-sign-in-alt"></i>Login</a>
        <a href="./formular.php"><i class="fa fa-list-ul"></i> Register</a>
        <?php } ?>
        <?php if(isset($_SESSION['email'])){?>
            <a href="./home.php"><i class="fa fa-shopping-cart"></i> Shopping Cart</a>
        <a href="./shop.php?status=logout"><i class="fas fa-sign-out-alt"></i>Logout</a>
        <?php } ?>
        </div>
</div>
</header>