<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Hoàn tiền giao dịch</title>
    <!-- Bootstrap core CSS -->
    <link href="../../assets/css/bootstrap.min.css" rel="stylesheet"/>
    <!-- Custom styles for this template -->
    <link href="../../assets/css/jumbotron-narrow.css" rel="stylesheet">  
    <script src="../../assets/js/jquery-1.11.3.min.js"></script>
</head>
<body>
    <div class="container">
        <div class="header clearfix">
            <nav>
                <ul class="nav nav-pills pull-right">
                    <li><a href="../index.php">Trang chủ</a></li>
                    <li><a href="../cart/index.php">Giỏ hàng</a></li>
                    <li><a href="history.php">Lịch sử GD</a></li>
                </ul>
            </nav>
            <h3 class="text-muted">VNPAY DEMO</h3>
        </div>
        <div style="width: 100%;padding-top:0px;font-weight: bold;color: #333333"><h3>Hoàn tiền</h3></div>
        <div style="width: 100% ;border-bottom: 2px solid black;padding-bottom: 20px" >
            <form action="../../modules/payment/vnpay/refund.php" id="frmCreateOrder" method="post">        
                <div class="form-group">
                    <label >OrderID</label>
                    <input class="form-control" data-val="true"  name="orderid" type="text" value="" />
                </div>
                <div class="form-group">
                    <label>Kiểu hoàn tiền </label>
                    <select name="trantype" id="trantype" class="form-control">
                        <option value="02">Hoàn tiền toàn phần</option>
                        <option value="03">Hoàn tiền 1 phần</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="amount">Số tiền</label>
                    <input class="form-control" data-val="true" data-val-number="The field Amount must be a number." data-val-required="The Amount field is required." id="amount" max="100000000" min="1" name="amount" type="number" value="10000" />
                </div>
                <div class="form-group">
                    <label>Payment Date</label>
                    <input class="form-control" data-val="true"  name="paymentdate" type="text" value="" />
                </div>
                <div class="form-group">
                    <label >Email người khởi tạo GD hoàn tiền</label>
                    <input class="form-control" data-val="true"  name="mail" type="email" value="" />
                </div>
                <input type="submit"  class="btn btn-warning" value="Hoàn tiền" />
            </form>
        </div>
        
        <footer class="footer" style="margin-top: 30px;">
            <p>&copy; Web Mua Ban Do Cu <?php echo date('Y')?></p>
        </footer>
    </div>
</body>
</html>
