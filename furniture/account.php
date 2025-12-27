<?php
session_start();
if (!isset($_SESSION["basket"])) {
    $_SESSION["basket"] = [];
}

if (!isset($_SESSION["customer"])) {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Account | DAVA</title>
    <meta charset="UTF-8">
    <link href="css/home.css" rel="stylesheet">
    <link href="css/login.css" rel="stylesheet">
    <link href="css/account.css" rel="stylesheet">
</head>
<body>
    <div id="container">
        <div id="headerDiv">
            <!--/////////////////////////// WELCOME USER ////////////////////////////////-->

            <?php
            if (isset($_POST["btnLogout"])) {
                unset($_SESSION["customer"]);
            }
            if (isset($_SESSION["customer"])) {
                $custName = $_SESSION["customer"]["name"];
                echo "<span id='welcomeSpan'><a id='aWelcome' href='account.php'>Welcome, $custName</a></span>";
                echo "  <script> 
                            $(function() 
                                {
                                    $('#login').remove();
                                })
                            </script>";
            }
            ?>
            <!--///////////////////////// END OF WELCOME USER ///////////////////////////-->
            <p>
                <a id="login" href="login.php">login &#124;</a>
                <a id="cart" href="basket.php">
                    <img src="css/images/imgCartW26xH26.png" width="26" height="26" alt="Cart Image" />
                    my cart&nbsp;<?php $size = sizeof($_SESSION["basket"]);
                                    echo "$size"; ?>&nbsp;items
                </a>
            </p>
        </div>
        <!--///////////////////////////////NAVIGATION PANEL//////////////////////////-->
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
        <!--///////////////////////////////END OF NAVIGATION/////////////////////////-->
    
    <?php
    include_once("php/connect.php");
    $errorMessage = "";
    $message = "";

    // SET DEFAULT VALUES (NO WARNINGS)
    $firstName = $_SESSION["customer"]["firstName"] ?? "";
    $lastName = $_SESSION["customer"]["lastName"] ?? "";
    $email = $_SESSION["customer"]["email"] ?? "";
    $pwd = $_SESSION["customer"]["password"] ?? "";
    $address = $_SESSION["customer"]["address"] ?? "";
    $postCode = $_SESSION["customer"]["postCode"] ?? "";

    if (isset($_POST["btnUpdate"])) {
        $firstName = trim($_POST["txtFirstName"]);
        $lastName = trim($_POST["txtLastName"]);
        $postEmail = trim($_POST["txtEmail"]);
        $pwd = $_POST["txtPwd"];
        $address = trim($_POST["txtAddress"]);
        $postCode = trim($_POST["txtPostCode"]);

        // SIMPLE VALIDATION
        if (strlen($firstName) > 0 && strlen($lastName) > 0 && strlen($postEmail) > 0 && 
            strlen($pwd) >= 6 && strlen($address) >= 10) {
            
            $currentEmail = $email;
            $safeEmail = mysqli_real_escape_string($connection, $postEmail);
            
            // CHECK EMAIL NOT USED BY OTHERS
            $query = "SELECT * FROM customer WHERE email='$safeEmail' AND email != '$currentEmail'";
            $result = mysqli_query($connection, $query);
            
            if (mysqli_num_rows($result) == 0) {
                $salt = "*@!";
                $hashedPwd = md5($salt . $pwd . $salt);
                
                $updateQuery = "UPDATE customer SET 
                                firstName='$firstName',
                                lastName='$lastName',
                                email='$safeEmail',
                                password='$hashedPwd',
                                address='$address',
                                postCode='$postCode'
                                WHERE email='$currentEmail'";
                
                if (mysqli_query($connection, $updateQuery)) {
                    // UPDATE SESSION
                    $_SESSION["customer"] = [
                        "firstName" => $firstName,
                        "lastName" => $lastName,
                        "name" => $firstName . " " . $lastName,
                        "email" => $safeEmail,
                        "password" => $pwd,
                        "address" => $address,
                        "postCode" => $postCode
                    ];
                    $message = "Account updated successfully";
                } else {
                    $errorMessage = "Update failed";
                }
            } else {
                $errorMessage = "Email already registered";
            }
        } else {
            $errorMessage = "Please fill all fields";
        }
    }
    ?>
    
    <div id="accountBoxDiv">
        <div id="accountThickLine"></div>
        <div id="accountDiv">
            <h3>Your Account</h3>
            <p>Edit your personal information</p>
            
            <?php if ($message): ?>
                <div id="messageBox" style="color:green;background:#d4edda;padding:15px;margin:10px 0;border:1px solid #c3e6cb;">
                    <strong><?php echo $message; ?></strong>
                </div>
                <script>
                setTimeout(function(){document.getElementById('messageBox').style.display='none';}, 2000);
                </script>
            <?php endif; ?>
            
            <?php if ($errorMessage): ?>
                <div id="errorBox" style="color:red;background:#f8d7da;padding:15px;margin:10px 0;border:1px solid #f5c6cb;">
                    <strong><?php echo $errorMessage; ?></strong>
                </div>
                <script>
                setTimeout(function(){document.getElementById('errorBox').style.display='none';}, 2000);
                </script>
            <?php endif; ?>
            
            <form method="post">
                <span>First Name:</span>
                <input type="text" name="txtFirstName" value="<?php echo htmlspecialchars($firstName); ?>" style="width:200px"><br><br>
                
                <span>Last Name:</span>
                <input type="text" name="txtLastName" value="<?php echo htmlspecialchars($lastName); ?>" style="width:200px"><br><br>
                
                <span>Email:</span>
                <input type="text" name="txtEmail" value="<?php echo htmlspecialchars($email); ?>" style="width:200px"><br><br>
                
                <span>Password:</span>
                <input type="password" name="txtPwd" value="<?php echo htmlspecialchars($pwd); ?>" style="width:200px"><br><br>
                
                <span>Address:</span>
                <input type="text" name="txtAddress" value="<?php echo htmlspecialchars($address); ?>" style="width:300px"><br><br>
                
                <span>Post Code:</span>
                <input type="text" name="txtPostCode" value="<?php echo htmlspecialchars($postCode); ?>" style="width:100px"><br><br>
                
                <input type="submit" name="btnUpdate" value="Update">
                <input type="submit" name="btnLogout" value="Logout">
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
