<?php
session_start();
if (!isset($_SESSION["basket"])) {
    $_SESSION["basket"] = [];
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login | DAVA</title>
    <meta charset="UTF-8">
    <link href="css/home.css" rel="stylesheet">
    <link href="css/login.css" rel="stylesheet">
</head>
<body>
    <div id="container">
        <div id="headerDiv">
            <?php
            if (isset($_POST["btnLogout"])) {
                unset($_SESSION["customer"]);
            }
            if (isset($_SESSION["customer"])) {
                $custName = $_SESSION["customer"]["name"];
                echo "<span id='welcomeSpan'><a id='aWelcome' href='account.php'>Welcome, $custName</a></span>";
                // keep jQuery to hide login link
                echo "  <script> 
                            $(function() 
                                {
                                    $('#login').remove();
                                })
                        </script>";
            }
            ?>
            <p>
                <a id="login" href="login.php">login &#124;</a>
                <a id="cart" href="basket.php">
                    <img src="css/images/imgCartW26xH26.png" width="26" height="26" alt="Cart Image" />
                    my cart&nbsp;<?php $size = sizeof($_SESSION["basket"]); echo "$size"; ?>&nbsp;items
                </a>
                <?php if (isset($_SESSION["customer"])) { ?>
                    <!-- visible Logout button -->
                    <form method="post" style="display:inline;">
                        <input type="submit"
                               name="btnLogout"
                               value="Logout"
                               style="border:none; background:none; color:#fff; cursor:pointer; text-decoration:underline; margin-left:10px;">
                    </form>
                <?php } ?>
            </p>
        </div>

        <form action="search.php" method="post">
            <div id="navigationDiv">
                <ul>
                    <li> <a class="logo" href="index.php"></a> </li>
                    <li> <a class="button" style="width:110px" href="prodList.php?prodType=bed">BEDS</a> </li>
                    <li> <a class="button" style="width:110px" href="prodList.php?prodType=chair">CHAIRS</a> </li>
                    <li> <a class="button" style="width:110px" href="prodList.php?prodType=chest">CHESTS</a> </li>
                    <li> <a class="button" style="width:120px" href="contactus.php">Contact Us</a> </li>
                    <li class="txtNav"> <input type="text" name="txtSearch" /> </li>
                    <li class="searchNav"> <input type="submit" name="btnSearch" value="" /> </li>
                </ul>
            </div>
        </form>
    
    <?php
    include_once("php/connect.php");
    $message = "";
    
    if (isset($_POST["btnLogin"])) {
        $email = mysqli_real_escape_string($connection, trim($_POST["txtEmail"]));
        $pwd = $_POST["txtPwd"];
        $salt = "*@!"; 
        $hashedPwd = md5($salt . $pwd . $salt);
        
        $query = "SELECT * FROM customer WHERE email='$email' AND password='$hashedPwd'";
        $result = mysqli_query($connection, $query);
        
        if ($row = mysqli_fetch_assoc($result)) {
            $_SESSION["customer"] = [
                "name" => $row["firstName"]." ".$row["lastName"],
                "email" => $email
            ];
            header("Location: index.php"); exit;
        }
        $message = "Not registered";
    }
    
    if (isset($_POST["btnRegister"])) {
        $firstName = trim($_POST["txtFirstName"]);
        $lastName = trim($_POST["txtLastName"]);
        $email = trim($_POST["txtEmail"]);
        $pwd = $_POST["txtPwd"];
        $verifyPwd = $_POST["txtVerifyPwd"];
        $address = trim($_POST["txtAddress"]);
        $postCode = trim($_POST["txtPostCode"]);
        
        if ($firstName && $lastName && $email && $pwd && $verifyPwd && $address && $postCode && $pwd === $verifyPwd && strlen($pwd) >= 6 && strlen($address) >= 10) {
            
            $safeEmail = mysqli_real_escape_string($connection, $email);
            $check = mysqli_query($connection, "SELECT email FROM customer WHERE email='$safeEmail'");
            
            if (mysqli_num_rows($check) == 0) {
                $salt = "*@!"; 
                $hashedPwd = md5($salt . $pwd . $salt);
                $insert = mysqli_query($connection, 
                    "INSERT INTO customer (firstName,lastName,email,password,address,postCode) 
                     VALUES ('$firstName','$lastName','$safeEmail','$hashedPwd','$address','$postCode')");
                
                if ($insert) {
                    $message = "Registered successfully";
                } else {
                    $message = "Not registered";
                }
            } else {
                $message = "Not registered";
            }
        } else {
            $message = "Not registered";
        }
    }
    ?>
    
    <div id="loginBoxDiv">
        <a href="admin.php">admin login</a>
        <h3>Login or Create Account</h3>
        <hr class="loginThinLine">
        
        <?php if ($message): ?>
            <div id="messageBox" style="padding:15px;margin:10px 0;border:1px solid 
                <?php echo $message == 'Registered successfully' ? '#c3e6cb' : '#f5c6cb'; ?>;">
                <strong style="color:<?php echo $message == 'Registered successfully' ? 'green' : 'red'; ?>;">
                    <?php echo $message; ?>
                </strong>
            </div>
            <script>
                setTimeout(function() {
                    document.getElementById('messageBox').style.display = 'none';
                }, 2000); // 2 seconds
            </script>
        <?php endif; ?>
        
        <div id="hasAccountDiv">
            <h5>Existing Customers</h5>
            <form method="post">
                Email: <input type="text" name="txtEmail" style="width:200px"><br><br>
                Password: <input type="password" name="txtPwd" style="width:200px"><br><br>
                <input type="submit" name="btnLogin" value="Login">
            </form>
        </div>

        <div>
            <h5>New Customers</h5>
            <form method="post">
                First Name: <input type="text" name="txtFirstName" style="width:200px"><br><br>
                Last Name: <input type="text" name="txtLastName" style="width:200px"><br><br>
                Email: <input type="text" name="txtEmail" style="width:200px"><br><br>
                Password: <input type="password" name="txtPwd" style="width:200px"><br><br>
                Verify Password: <input type="password" name="txtVerifyPwd" style="width:200px"><br><br>
                Address: <input type="text" name="txtAddress" style="width:300px"><br><br>
                Post Code: <input type="text" name="txtPostCode" style="width:100px"><br><br>
                <input type="submit" name="btnRegister" value="Register">
            </form>
        </div>
        <div id="loginThickLine"></div>
    </div>

    <div id="footerDiv">
        <p>Terms | Privacy | &copy;2020</p>
    </div>
</div>
</body>
</html>
