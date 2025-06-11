<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Lịch sử thanh toán</title>
    <link href="/vnpay_php/assets/bootstrap.min.css" rel="stylesheet"/>
    <link href="/vnpay_php/assets/jumbotron-narrow.css" rel="stylesheet">
    <script src="/vnpay_php/assets/jquery-1.11.3.min.js"></script>
    <style>
        .status-pending { color: #f0ad4e; }
        .status-success { color: #5cb85c; }
        .status-failed { color: #d9534f; }
        .table-responsive { margin-top: 20px; }
    </style>
</head>
<body>
    <?php 
    require_once("../config/config.php");
    
    // Truy vấn lấy tất cả lịch sử thanh toán, đơn mới nhất lên đầu
    $sql = "SELECT * FROM `payment_history` ORDER BY `id` DESC;";
    
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $payment_history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
        $error_message = "Lỗi truy vấn database: " . $e->getMessage();
        $payment_history = [];
    }
      // Hàm format trạng thái
    function formatStatus($status) {
        switch(strtolower($status)) {
            case '00':
            case 'success':
                return '<span class="status-success"><strong>Thành công</strong></span>';
            case '01':
            case 'pending':
                return '<span class="status-pending"><strong>Đang xử lý</strong></span>';
            case '02':
            case 'failed':
            case 'fail':
                return '<span class="status-failed"><strong>Thất bại</strong></span>';
            default:
                return '<span class="text-muted">' . htmlspecialchars($status) . '</span>';
        }
    }
    
    // Hàm format bank code
    function formatBankCode($bank_code) {
        $banks = [
            'NCB' => 'Ngân hàng NCB',
            'AGRIBANK' => 'Ngân hàng Agribank',
            'SCB' => 'Ngân hàng SCB',
            'SACOMBANK' => 'Ngân hàng SacomBank',
            'EXIMBANK' => 'Ngân hàng EximBank',
            'VIETINBANK' => 'Ngân hàng Vietinbank',
            'VIETCOMBANK' => 'Ngân hàng VCB',
            'HDBANK' => 'Ngân hàng HDBank',
            'TECHCOMBANK' => 'Ngân hàng Techcombank',
            'VPBANK' => 'Ngân hàng VPBank',
            'MBBANK' => 'Ngân hàng MBBank',
            'ACB' => 'Ngân hàng ACB',
            'OCB' => 'Ngân hàng OCB',
            'BIDV' => 'Ngân hàng BIDV'
        ];
        
        return $banks[$bank_code] ?? $bank_code;
    }
    
    // Hàm format số tiền
    function formatMoney($amount) {
        return number_format($amount, 0, ',', '.') . ' VNĐ';
    }
    
    // Hàm format ngày tháng
    function formatDate($datetime) {
        return date('d/m/Y H:i:s', strtotime($datetime));
    }
    ?>

    <div class="container">
        <div class="header clearfix">
            <nav>
                <ul class="nav nav-pills pull-right">
                    <li><a href="index.php">Trang chủ</a></li>
                    <li class="active"><a href="#">Lịch sử thanh toán</a></li>
                </ul>
            </nav>
            <h3 class="text-muted">VNPAY - Lịch sử thanh toán</h3>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <h2>Lịch sử thanh toán</h2>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <strong>Lỗi!</strong> <?php echo $error_message; ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($payment_history)): ?>
                    <div class="alert alert-info">
                        <strong>Thông báo:</strong> Chưa có lịch sử thanh toán nào.
                    </div>
                <?php else: ?>
                    <!-- Thống kê tổng quan -->
                    <div class="row" style="margin-bottom: 20px;">
                        <div class="col-md-3">
                            <button class="btn btn-primary btn-lg btn-block filter-btn" data-status="all">
                                <strong><?php echo count($payment_history); ?></strong><br>Tổng GD
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-success btn-lg btn-block filter-btn" data-status="success">
                                <strong><?php echo count(array_filter($payment_history, function($p) { return in_array(strtolower($p['transaction_status'] ?? ''), ['00', 'success']); })); ?></strong><br>Thành công
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-warning btn-lg btn-block filter-btn" data-status="pending">
                                <strong><?php echo count(array_filter($payment_history, function($p) { return in_array(strtolower($p['transaction_status'] ?? ''), ['01', 'pending']); })); ?></strong><br>Đang xử lý
                            </button>
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-danger btn-lg btn-block filter-btn" data-status="failed">
                                <strong><?php echo count(array_filter($payment_history, function($p) { return in_array(strtolower($p['transaction_status'] ?? ''), ['02', 'failed', 'fail']); })); ?></strong><br>Thất bại
                            </button>
                        </div>
                    </div>

                    <!-- Bảng lịch sử -->
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">                            <thead>
                                <tr class="info">
                                    <th>#</th>
                                    <th>Mã hóa đơn</th>
                                    <th>Mã giao dịch</th>
                                    <th>Số tiền</th>
                                    <th>Ngân hàng</th>
                                    <th>Loại thẻ</th>
                                    <th>Trạng thái</th>
                                    <th>Ngày thanh toán</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($payment_history as $index => $payment):
                                    $status_class = strtolower($payment['transaction_status'] ?? 'unknown');
                                    // Normalize common status variations for class consistency
                                    if ($status_class === '00') $status_class = 'success';
                                    if ($status_class === '01') $status_class = 'pending';
                                    if ($status_class === '02' || $status_class === 'fail') $status_class = 'failed';
                                ?>
                                <tr class="payment-row status-<?php echo htmlspecialchars($status_class); ?>">
                                    <td><?php echo $index + 1; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($payment['order_id'] ?? 'N/A'); ?></strong>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($payment['txn_ref'] ?? 'N/A'); ?></code>
                                    </td>
                                    <td class="text-right">
                                        <strong><?php echo formatMoney($payment['amount'] ?? 0); ?></strong>
                                    </td>
                                    <td>
                                        <?php echo formatBankCode($payment['bank_code'] ?? 'N/A'); ?>
                                    </td>
                                    <td class="text-center">
                                        <span class="label label-info"><?php echo htmlspecialchars($payment['card_type'] ?? 'N/A'); ?></span>
                                    </td>
                                    <td class="text-center">
                                        <?php echo formatStatus($payment['transaction_status'] ?? 'unknown'); ?>
                                        <?php if (!empty($payment['response_code'])): ?>
                                            <br><small class="text-muted">Code: <?php echo htmlspecialchars($payment['response_code']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php echo formatDate($payment['pay_date'] ?? $payment['created_at'] ?? date('Y-m-d H:i:s')); ?>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-xs btn-info" onclick="viewDetail(<?php echo $payment['id'] ?? $index; ?>, '<?php echo htmlspecialchars($payment['order_id'] ?? ''); ?>')">
                                            Chi tiết
                                            <!-- xử lí chi tiết hơn bổ sung người thanh toán, mã giao dịch, mã ngân hàng, loại thẻ, trạng thái thanh toán, ngày thanh toán -->
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Phân trang (nếu cần) -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination pull-right">
                            <li class="disabled"><a href="#" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li>
                            <li class="active"><a href="#">1</a></li>
                            <li><a href="#" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>
                        </ul>
                    </nav>
                <?php endif; ?>
            </div>
        </div>

        <!-- Modal chi tiết giao dịch -->
        <div class="modal fade" id="detailModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                        <h4 class="modal-title">Chi tiết giao dịch</h4>
                    </div>
                    <div class="modal-body" id="modalContent">
                        <!-- Nội dung sẽ được load bằng JavaScript -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Đóng</button>
                    </div>
                </div>
            </div>
        </div>

        <footer class="footer">
            <p>&copy; VNPAY <?php echo date('Y'); ?></p>
        </footer>
    </div>

    <!-- Bootstrap JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
      <script>
        function viewDetail(paymentId, orderId) {
            // Hiển thị modal với thông tin chi tiết
            $('#modalContent').html('<p>Đang tải thông tin...</p>');
            $('#detailModal').modal('show');
            
            // Gọi AJAX để lấy chi tiết giao dịch
            $.ajax({
                url: 'get_payment_detail.php',
                method: 'POST',
                data: { id: paymentId },
                success: function(response) {
                    try {
                        var data = JSON.parse(response);
                        if (data.success) {
                            var payment = data.payment;
                            $('#modalContent').html(`
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <tr><td><strong>ID:</strong></td><td>` + payment.id + `</td></tr>
                                        <tr><td><strong>Mã đơn hàng:</strong></td><td>` + payment.order_id + `</td></tr>
                                        <tr><td><strong>Mã giao dịch:</strong></td><td>` + payment.txn_ref + `</td></tr>
                                        <tr><td><strong>Số tiền:</strong></td><td class="text-danger"><strong>` + new Intl.NumberFormat('vi-VN').format(payment.amount) + ` VNĐ</strong></td></tr>
                                        <tr><td><strong>Ngân hàng:</strong></td><td>` + payment.bank_code + `</td></tr>
                                        <tr><td><strong>Loại thẻ:</strong></td><td>` + payment.card_type + `</td></tr>
                                        <tr><td><strong>Mã phản hồi:</strong></td><td>` + payment.response_code + `</td></tr>
                                        <tr><td><strong>Trạng thái:</strong></td><td>` + payment.transaction_status + `</td></tr>
                                        <tr><td><strong>Secure Hash:</strong></td><td><small>` + payment.secure_hash + `</small></td></tr>
                                        <tr><td><strong>Ngày thanh toán:</strong></td><td>` + payment.pay_date + `</td></tr>
                                        <tr><td><strong>Ngày tạo:</strong></td><td>` + payment.created_at + `</td></tr>
                                    </table>
                                </div>
                            `);
                        } else {
                            $('#modalContent').html('<div class="alert alert-danger">Không thể tải thông tin chi tiết!</div>');
                        }
                    } catch(e) {
                        // Fallback hiển thị thông tin cơ bản
                        $('#modalContent').html(`
                            <div class="table-responsive">
                                <table class="table">
                                    <tr><td><strong>ID Giao dịch:</strong></td><td>` + paymentId + `</td></tr>
                                    <tr><td><strong>Mã đơn hàng:</strong></td><td>` + orderId + `</td></tr>
                                    <tr><td><strong>Thời gian:</strong></td><td>` + new Date().toLocaleString('vi-VN') + `</td></tr>
                                </table>
                            </div>
                        `);
                    }
                },
                error: function() {
                    // Fallback hiển thị thông tin cơ bản
                    $('#modalContent').html(`
                        <div class="table-responsive">
                            <table class="table">
                                <tr><td><strong>ID Giao dịch:</strong></td><td>` + paymentId + `</td></tr>
                                <tr><td><strong>Mã đơn hàng:</strong></td><td>` + orderId + `</td></tr>
                                <tr><td><strong>Thời gian xem:</strong></td><td>` + new Date().toLocaleString('vi-VN') + `</td></tr>
                                <tr><td colspan="2"><em>Không thể kết nối server để lấy chi tiết</em></td></tr>
                            </table>
                        </div>
                    `);
                }
            });
        }

        $(document).ready(function() {
            $('.filter-btn').on('click', function() {
                var filterType = $(this).data('status');
                
                if (filterType === 'all') {
                    $('.payment-row').show();
                } else {
                    $('.payment-row').hide();
                    $('.payment-row.status-' + filterType).show();
                }
            });
        });

        // Auto refresh trang sau 30 giây (tùy chọn)
        // setTimeout(function() {
        //     location.reload();
        // }, 30000);
    </script>
</body>
</html>
