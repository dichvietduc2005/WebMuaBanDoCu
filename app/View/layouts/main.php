<?php
/**
 * Main Layout Template
 * Wrapper cho tất cả pages
 * 
 * Variables từ LayoutManager:
 * - $content: Main page content
 * - $baseUrl: BASE_URL
 * - $container: Service Container
 * - $viewRenderer: View renderer
 * - và các models/helpers khác
 */
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'HIHand Shop' ?></title>
    
    <!-- CSS -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="<?= $baseUrl ?>public/assets/css/index.css">
</head>
<body>
    <!-- Header Component -->
    <header>
        <?php
        // Render header component with common data
        $headerData = [
            'categories' => $categories ?? [],
            'cart_count' => $cart_count ?? 0,
            'unread_notifications' => $unread_notifications ?? 0,
        ];
        echo renderComponent('header', $headerData);
        ?>
    </header>
    
    <!-- Main Content -->
    <main>
        <?= $content ?? '' ?>
    </main>
    
    <!-- Footer Component -->
    <footer>
        <?= renderComponent('footer') ?>
    </footer>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= $baseUrl ?>public/assets/js/components/header.js"></script>
</body>
</html>

