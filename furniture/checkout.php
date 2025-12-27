<?php
session_start();
ob_start();

if (!isset($_SESSION["basket"])) {
    $basket = array();
    $_SESSION["basket"] = $basket;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "furniture_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// NEW: Handle order saving when clicking "Confirm Order"
if (isset($_GET['confirm']) && !empty($_SESSION["customer"]) && !empty($_SESSION["basket"])) {
    $customerEmail = $_SESSION["customer"]["email"] ?? '';
    $customerName = $_SESSION["customer"]["name"] ?? '';
    
    if (!empty($customerEmail)) {
        // Get customer details from session/DB
        $email = $customerEmail;
        include_once 'php/connect.php'; // Keep your existing connection for customer lookup
        
        $query = "SELECT firstName, lastName, email, address, postCode FROM customer WHERE email='$email' LIMIT 1";
        $result = mysqli_query($connection, $query);
        $customer = mysqli_fetch_assoc($result) ?: [];
        
        $name = ($customer["firstName"] ?? "") . " " . ($customer["lastName"] ?? "");
        $address = $customer["address"] ?? "No address provided";
        $postCode = $customer["postCode"] ?? "No postcode";
        
        // Calculate totals from basket
        $basket = $_SESSION["basket"];
        $subtotal = 0;
        foreach ($basket as $item) {
            $subtotal += $item["price"] * $item["qty"];
        }
        $shippingCost = 50;
        $grandTotal = $subtotal + $shippingCost;
        $orderDate = date('Y-m-d H:i:s');
        
        // FIXED: Correct bind_param types - NO SPACES!
        $sql = "INSERT INTO orders (customerName, customerEmail, address, postCode, orderDate, subtotal, shippingCost, grandTotal, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssddds", $name, $customerEmail, $address, $postCode, $orderDate, $subtotal, $shippingCost, $grandTotal);
        
        if ($stmt->execute()) {
            $orderId = $conn->insert_id;
            
            // SAVE ORDER DETAILS
            $detailSql = "INSERT INTO orderDetails (orderId, prodId, prodQty) VALUES (?, ?, ?)";
            $detailStmt = $conn->prepare($detailSql);
            
            foreach ($basket as $item) {
                $detailStmt->bind_param("iii", $orderId, $item["id"], $item["qty"]);
                $detailStmt->execute();
            }
            
            // Clear basket
            unset($_SESSION["basket"]);
            
            $stmt->close();
            $detailStmt->close();
            header("Location: thankyou.php?orderId=" . $orderId);
            exit;
        } else {
            echo "Order save failed: " . $stmt->error;
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Checkout | DAVA</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link href="css/home.css" rel="stylesheet" type="text/css" />
    <link href="css/basket.css" rel="stylesheet" type="text/css" />
    <link href="css/checkout.css" rel="stylesheet" type="text/css" />
    <script src="javascript/jquery-1.8.3.min.js" type="text/javascript"></script>
    <script src="javascript/validation.js" type="text/javascript"></script>

    <script type="text/javascript">
        function btnUpdate() {
            var frmUpdate = document.getElementById("frmUpdate");
            var qty = frmUpdate.qtyUpdate.value;

            if (isEmpty(qty)) {
                alert("Please enter quantity!");
                return false;
            }

            if (!isInteger(qty)) {
                alert("Please enter whole number!");
                return false;
            }

            if (qty < 0) {
                alert("Please enter positive number!");
                return false;
            }
        }
    </script>
</head>

<body>
    <div id="container">
        <div id="headerDiv">
            <?php
            if (isset($_POST["btnLogout"])) {
                unset($_SESSION["customer"]);
            }
            if (isset($_SESSION["customer"])) {
                $custName = $_SESSION["customer"]["name"] ?? "Customer";
                echo "<span id='welcomeSpan'><a id='aWelcome' href='account.php'>Welcome, $custName</a></span>";
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
                    my cart&nbsp;<?php $size = sizeof($_SESSION["basket"]);
                                    echo "<span id='nItems'>$size</span>"; ?>&nbsp;items
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

        <div id="basketDiv">
            <h3 id="basketHeading"> Review Order </h3>

            <table id="basketTable">
                <tr>
                    <th id="thProdName" colspan="2">Product Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th id="thLineTotal">&nbsp;&nbsp;Line Total</th>
                </tr>
                <tr>
                    <td class='tdFirstThinLine' colspan='5'> </td>
                </tr>
                <?php
                if (!isset($_SESSION["customer"]) || !isset($_SESSION["basket"])) {
                    header("Location: index.php");
                    exit;
                } else {
                    $basket = $_SESSION["basket"];
                    $total = 0;

                    foreach ($basket as $key => $item) {
                        $id = $item["id"];
                        $type = $item["type"];
                        $imgName = $item["imageName"];
                        $name = $item["name"];
                        $price = $item["price"];
                        $qty = $basket[$key]["qty"];
                        $cost = $qty * $price;
                        $total = $total + ($price * $qty);
                        echo "<tr id='tr$id'>
                                <td class='tdProdImg'> <img src='css/images/$type/$imgName' width='50' height='52' alt='image $imgName'/> </td>
                                <td class='tdName'> <p>$name</p> </td>
                                <td class='tdPrice'>&#8377;$price</td>
                                <td class='tdQty'>$qty</td>
                                <td class='tdLineTotal'>&nbsp;&nbsp;&#8377;$cost</td>
                              </tr>
                              <tr><td class='tdThinLine' colspan='5'> </td></tr>";
                    }

                    $shippingCost = 50;
                    define("VATRATE", 0.2);
                    $grandTotal = $total + $shippingCost;

                    // RANDOM DELIVERY DATE (3-7 days from today)
                    $minDays = 3;
                    $maxDays = 7;
                    $randomDays = rand($minDays, $maxDays);
                    $deliveryDate = date('d-m-Y', strtotime("+$randomDays days"));

                    echo "<tr class='trEmptySpace'><td colspan='5'></td></tr> ";
                    echo "<tr>  <td colspan='2'></td>  <td class='tdEnd' colspan='2'>  Subtotal:       </td>  <td class='tdEndData'>&nbsp;&nbsp;&#8377;$total        </td>  </tr>";
                    echo "<tr>  <td colspan='2'></td>  <td class='tdEnd' colspan='2'>  Shipping Cost:  </td>  <td class='tdEndData'>&nbsp;&nbsp;&#8377;$shippingCost  </td>  </tr>";
                    echo "<tr>  <td colspan='2'></td>  <td class='tdEnd' colspan='2'>Est. Delivery:  </td>  <td class='tdEndData'>&nbsp;&nbsp;$deliveryDate  </td>  </tr>";
                    echo "<tr>  <td colspan='2'></td>  <td class='tdGrandTotal' colspan='2'>  Grand Total:    </td>  <td class='tdGrandTotalData'>&nbsp;&nbsp;&#8377;$grandTotal   </td>  </tr>";
                }
                ?>
            </table>

            <!-- DIRECT FROM DATABASE - SHOWS REAL DATA -->
            <div id="custDetailsDiv">
                <?php
                // GET REAL DATA FROM DATABASE (bypasses session issues)
                $email = $_SESSION["customer"]["email"] ?? "";
                if (!empty($email)) {
                    include_once 'php/connect.php'; // Your DB connection file
                    
                    $query = "SELECT firstName, lastName, email, address, postCode 
                              FROM customer WHERE email='$email' LIMIT 1";
                    $result = mysqli_query($connection, $query);
                    $customer = mysqli_fetch_assoc($result) ?: [];
                    
                    $name = ($customer["firstName"] ?? "") . " " . ($customer["lastName"] ?? "");
                    $email = $customer["email"] ?? "";
                    $address = $customer["address"] ?? "No address provided";
                    $postCode = $customer["postCode"] ?? "No postcode";
                } else {
                    $name = "Customer";
                    $email = "";
                    $address = "No address provided";
                    $postCode = "No postcode";
                }
                
                echo "<h6>Your order will be sent</h6>
                      <p><strong>To:</strong>&nbsp;$name</p>
                      <p><strong>Email:</strong>&nbsp;$email</p>
                      <p><strong>Address:</strong>&nbsp;$address</p>
                      <p><strong>PostCode:</strong>&nbsp;$postCode</p>";
                ?>
            </div>

            <div id="checkOutDiv">
                <a id="aContinueShop" href="basket.php">Back to Basket</a>
                <!-- MODIFIED: Now saves to database before redirecting -->
                <a id="aCheckout" href="checkout.php?confirm=1">Confirm Order</a>
            </div>

            <div id="basketThickLine"></div>
        </div>

        <div id="footerDiv">
            <p>
                <a href="#">Terms of Use</a>
                &#124;
                <a href="#">Privacy Policy</a>
                &#124;
                <a href="#">&copy;2020 All Rights Reserved.</a>
            </p>
        </div>
    </div>
</body>
</html>
<?php 
$conn->close();
ob_flush(); 
?>
