<?php
session_start();
ob_start();

// Simple admin login check (change password as needed)
$adminLoggedIn = false;
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $adminLoggedIn = true;
}

// Handle admin login
if (isset($_POST['admin_login'])) {
    $password = $_POST['admin_password'];
    if ($password === 'admin123') { // Change this password
        $_SESSION['admin_logged_in'] = true;
        $adminLoggedIn = true;
    }
}

// Handle admin logout
if (isset($_POST['admin_logout'])) {
    unset($_SESSION['admin_logged_in']);
    header("Location: admin.php");
    exit;
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

// Fetch all orders
$sql = "SELECT * FROM orders ORDER BY orderDate DESC";
$ordersResult = $conn->query($sql);
$orders = [];
if ($ordersResult) {
    $orders = $ordersResult->fetch_all(MYSQLI_ASSOC);
}

// Fetch order details summary - FIXED VERSION
$orderDetailsSql = "SELECT od.orderId, COUNT(od.prodId) as itemCount, SUM(od.prodQty) as totalQty 
                    FROM orderDetails od GROUP BY od.orderId";
$detailsResult = $conn->query($orderDetailsSql);
$orderSummary = [];
if ($detailsResult) {
    $orderSummary = $detailsResult->fetch_all(MYSQLI_ASSOC);
}

// CREATE LOOKUP ARRAY FOR FAST ACCESS - NEW FIX
$summaryLookup = [];
foreach($orderSummary as $summary) {
    $summaryLookup[$summary['orderId']] = $summary;
}

$conn->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel | DAVA</title>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    <link href="css/home.css" rel="stylesheet" type="text/css" />
    <link href="css/basket.css" rel="stylesheet" type="text/css" />
    <link href="css/checkout.css" rel="stylesheet" type="text/css" />
    <script src="javascript/jquery-1.8.3.min.js" type="text/javascript"></script>
    <style>
        .admin-login { text-align: center; padding: 100px 20px; }
        .admin-content { padding: 20px; }
        .order-item { border: 1px solid #ddd; margin-bottom: 15px; padding: 15px; border-radius: 5px; }
        .status-pending { background: #fff3cd; color: #856404; padding: 4px 8px; border-radius: 3px; }
        .status-shipped { background: #d1ecf1; color: #0c5460; padding: 4px 8px; border-radius: 3px; }
        .stats-row { display: flex; gap: 20px; margin-bottom: 30px; }
        .stat-box { flex: 1; background: #f8f9fa; padding: 20px; text-align: center; border-radius: 5px; }
        .no-details { color: #999; font-style: italic; }
    </style>
</head>

<body>
    <div id="container">
        <div id="headerDiv">
            <?php if ($adminLoggedIn): ?>
                <span id='welcomeSpan'><strong>Admin Panel</strong></span>
                <form method="POST" style="display: inline; float: right;">
                    <button type="submit" name="admin_logout" style="background: none; border: none; color: #dd0b0bff; cursor: pointer;">Logout</button>
                </form>
            <?php endif; ?>
            <p style="clear: both;">
                <a href="index.php">← Back to Store</a>
            </p>
        </div>

        <form action="search.php" method="post" style="<?php echo $adminLoggedIn ? 'display:none;' : ''; ?>">
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

        <div id="basketDiv" class="admin-content">
            <h3 id="basketHeading"> <?php echo $adminLoggedIn ? 'Admin Dashboard' : 'Admin Login Required'; ?> </h3>
            
            <?php if (!$adminLoggedIn): ?>
                <div class="admin-login">
                    <h4>Enter Admin Password</h4>
                    <form method="POST">
                        <input type="password" name="admin_password" placeholder="Password" required style="padding: 10px; width: 200px; margin: 10px;">
                        <br>
                        <button type="submit" name="admin_login" style="padding: 10px 20px; background: #007cba; color: white; border: none; border-radius: 5px; cursor: pointer;">Login</button>
                    </form>
                    <p style="margin-top: 20px; color: #666;">Default: <strong>admin123</strong> (change this!)</p>
                </div>
            <?php else: ?>
                
                <!-- Stats Overview -->
                <div class="stats-row">
                    <div class="stat-box">
                        <h4><?php echo count($orders); ?></h4>
                        <p>Total Orders</p>
                    </div>
                    <div class="stat-box">
                        <h4>&#8377;<?php echo number_format(array_sum(array_column($orders, 'grandTotal')), 2); ?></h4>
                        <p>Total Revenue</p>
                    </div>
                    <div class="stat-box">
                        <h4><?php echo array_sum(array_column($orderSummary, 'itemCount')); ?></h4>
                        <p>Total Items Sold</p>
                    </div>
                </div>

                <!-- Orders List -->
                <h4>Recent Orders (<?php echo count($orders); ?> total)</h4>
                
                <?php if (empty($orders)): ?>
                    <p>No orders found. <a href="index.php">Visit store</a></p>
                <?php else: ?>
                    <?php foreach ($orders as $index => $order): ?>
                        <div class="order-item">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <h5>Order #<?php echo $order['orderId']; ?></h5>
                                <span class="status-<?php echo strtolower($order['status']); ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </div>
                            
                            <p><strong>Date:</strong> <?php echo date('d-m-Y H:i', strtotime($order['orderDate'])); ?></p>
                            <p><strong>Customer:</strong> <?php echo htmlspecialchars($order['customerName']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($order['customerEmail']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($order['customerPhone'] ?? 'N/A'); ?></p>
                            <p><strong>Total:</strong> &#8377;<?php echo number_format($order['grandTotal'], 2); ?>
                               (Subtotal: &#8377;<?php echo number_format($order['subtotal'], 2); ?> + Shipping: &#8377;<?php echo $order['shippingCost']; ?>)
                            </p>
                            
                            <!-- FIXED Order Items Summary -->
                            <?php 
                            $thisOrderSummary = isset($summaryLookup[$order['orderId']]) ? $summaryLookup[$order['orderId']] : null;
                            ?>
                            <?php if ($thisOrderSummary): ?>
                                <p><strong>Items:</strong> <?php echo $thisOrderSummary['itemCount']; ?> products (<?php echo $thisOrderSummary['totalQty']; ?> units)</p>
                            <?php else: ?>
                                <p class="no-details"><strong>Items:</strong> No details available</p>
                            <?php endif; ?>
                            
                            <div style="margin-top: 10px; font-size: 12px; color: #666;">
                                <?php echo htmlspecialchars($order['address']); ?><br>
                                <?php echo $order['postCode']; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                
            <?php endif; ?>
            
            <div id="basketThickLine"></div>
        </div>

        <div id="footerDiv">
            <p>
                <a href="#">Terms of Use</a> &#124;
                <a href="#">Privacy Policy</a> &#124;
                <a href="#">&copy;2025 All Rights Reserved.</a>
            </p>
        </div>
    </div>
</body>
</html>
<?php ob_flush(); ?>
