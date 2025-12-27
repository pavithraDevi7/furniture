<?php
session_start();

// build basket from cookie (same logic as index)
if (isset($_COOKIE["basket"])) {
    foreach ($_COOKIE["basket"] as $name => $value) {
        if ($name == "id") {
            $ids = explode(":", $value);
        }
        if ($name == "name") {
            $names = explode(":", $value);
        }
        if ($name == "price") {
            $prices = explode(":", $value);
        }
        if ($name == "qty") {
            $qtys = explode(":", $value);
        }
        if ($name == "imageName") {
            $imageNames = explode(":", $value);
        }
        if ($name == "type") {
            $type = explode(":", $value);
        }
    }

    if (!empty($ids)) {
        $sizeIds = sizeof($ids);
        $basket = array();
        for ($i = 0; $i < $sizeIds; $i++) {
            $basket[] = array(
                "id"        => $ids[$i],
                "name"      => $names[$i],
                "price"     => $prices[$i],
                "qty"       => $qtys[$i],
                "imageName" => $imageNames[$i],
                "type"      => $type[$i]
            );
        }
        $_SESSION["basket"] = $basket;
    }
} else if (!isset($_SESSION["basket"])) {
    $basket = array();
    $_SESSION["basket"] = $basket;
}

// cart size
$size = isset($_SESSION["basket"]) ? sizeof($_SESSION["basket"]) : 0;
?>
<!DOCTYPE html>
<html>

<head>
    <title>Contact Us | DAVA Furniture</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    <link href="css/home.css" rel="stylesheet" type="text/css" />

    <script src="javascript/jquery-1.8.3.min.js" type="text/javascript"></script>
    <script src="javascript/jquery.cycle.all.js" type="text/javascript"></script>
    <script src="javascript/jquery.easing.1.3.js" type="text/javascript"></script>
</head>

<body>
    <div id="containerDiv">
        <div id="headerDiv">
            <?php
            if (isset($_POST["btnLogout"])) {
                unset($_SESSION["customer"]);
            }
            if (isset($_SESSION["customer"])) {
                $custName = $_SESSION["customer"]["name"];
                echo "<span id='welcomeSpan'><a id='aWelcome' href='account.php'>Welcome, $custName</a></span>";
                echo "  <script> 
                            $(function() {
                                $('#login').remove();
                            });
                        </script>";
            }
            ?>
            <p>
                <a id="login" href="login.php">login &#124;</a>
                <a id="cart" href="basket.php">
                    <img src="css/images/imgCartW26xH26.png" width="26" height="26" alt="Cart Image" />
                    my cart&nbsp;<?php echo $size; ?>&nbsp;items
                </a>
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

        <!-- CONTACT CONTENT -->
        <div id="indexBoxDiv3" style="min-height:350px;">
            <h4><span class="orange">Contact</span> DAVA Furniture</h4>
            <p>
                Have questions about our beds, chairs or storage cabinets? Use the form below or reach us using the contact details on the right.
            </p>

            <div style="display:flex; gap:40px; margin-top:20px; flex-wrap:wrap;">
                <!-- Contact Form -->
                <div style="flex:1 1 320px;">
                    <form action="contactus.php" method="post">
                        <p>
                            <label for="name">Name</label><br />
                            <input type="text" id="name" name="name" style="width:250px; padding:5px;" required />
                        </p>
                        <p>
                            <label for="email">Email</label><br />
                            <input type="email" id="email" name="email" style="width:250px; padding:5px;" required />
                        </p>
                        <p>
                            <label for="subject">Subject</label><br />
                            <input type="text" id="subject" name="subject" style="width:250px; padding:5px;" />
                        </p>
                        <p>
                            <label for="message">Message</label><br />
                            <textarea id="message" name="message" rows="5" style="width:250px; padding:5px;" required></textarea>
                        </p>
                        <p>
                            <input type="submit" name="btnSend" value="Send Message" />
                        </p>
                    </form>
                </div>

                <!-- Shop Details -->
                <div style="flex:1 1 260px;">
                    <h5>Showroom Address</h5>
                    <p>
                        DAVA Furniture &amp; Decoration<br />
                        MG Road, Pune, India<br />
                        Phone: +91-98765-43210<br />
                        Email: support@davafurniture.com
                    </p>

                    <h5>Opening Hours</h5>
                    <p>
                        Monday - Saturday: 10:00 AM - 8:00 PM<br />
                        Sunday: 11:00 AM - 6:00 PM
                    </p>
                </div>
            </div>

            <?php
            // simple feedback message (no email send, just confirmation)
            if (isset($_POST["btnSend"])) {
                echo "<p style='margin-top:20px; color:green;'>Thank you for contacting DAVA Furniture. We will get back to you soon.</p>";
            }
            ?>
        </div>

        <div id="footerDiv">
            <p>
                <a href="#">Terms of Use</a>
                &#124;
                <a href="#">Privacy Policy</a>
                &#124;
                <a href="#">&copy;2025 All Rights Reserved.</a>
            </p>
        </div>
    </div>
</body>

</html>
