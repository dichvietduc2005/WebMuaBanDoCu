<?php
function sidebar($items, $activeItem = '') {
    ?>
    <div class="col-md-3 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-person-circle me-2"></i> Tài khoản
            </div>
            <ul class="list-group list-group-flush">
                <?php foreach ($items as $item): ?>
                    <li class="list-group-item <?= $activeItem === $item['id'] ? 'active' : '' ?>">
                        <a href="<?= htmlspecialchars($item['url']) ?>" class="text-decoration-none d-flex align-items-center <?= $activeItem === $item['id'] ? 'text-white' : '' ?>">
                            <i class="bi bi-<?= htmlspecialchars($item['icon']) ?> me-2"></i>
                            <?= htmlspecialchars($item['label']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php
}

/**
 * Helper function để tạo sidebar items cho user
 */
function getUserSidebarItems() {
    return [
        [
            'id' => 'profile',
            'url' => '/user/profile',
            'icon' => 'person',
            'label' => 'Thông tin cá nhân'
        ],
        [
            'id' => 'products',
            'url' => '/user/products',
            'icon' => 'box',
            'label' => 'Sản phẩm của tôi'
        ],
        [
            'id' => 'orders',
            'url' => '/user/orders',
            'icon' => 'receipt',
            'label' => 'Đơn hàng'
        ],
        [
            'id' => 'sell',
            'url' => '/sell',
            'icon' => 'plus-circle',
            'label' => 'Đăng bán sản phẩm'
        ],
        [
            'id' => 'change-password',
            'url' => '/user/change-password',
            'icon' => 'key',
            'label' => 'Đổi mật khẩu'
        ]
    ];
}

/**
 * Helper function để tạo sidebar items cho admin
 */
function getAdminSidebarItems() {
    return [
        [
            'id' => 'dashboard',
            'url' => '/admin/dashboard',
            'icon' => 'speedometer2',
            'label' => 'Dashboard'
        ],
        [
            'id' => 'products',
            'url' => '/admin/products',
            'icon' => 'box',
            'label' => 'Quản lý sản phẩm'
        ],
        [
            'id' => 'users',
            'url' => '/admin/users',
            'icon' => 'people',
            'label' => 'Quản lý người dùng'
        ],
        [
            'id' => 'orders',
            'url' => '/admin/orders',
            'icon' => 'receipt',
            'label' => 'Quản lý đơn hàng'
        ],
        [
            'id' => 'categories',
            'url' => '/admin/categories',
            'icon' => 'tags',
            'label' => 'Quản lý danh mục'
        ]
    ];
}