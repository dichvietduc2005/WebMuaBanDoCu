/**
 * Notifications Popup Handler
 * Xử lý hiển thị popup thông báo theo phong cách Cho Tốt
 */

class NotificationsPopup {
    constructor() {
        this.isOpen = false;
        this.currentTab = 'activity';
        this.notifications = {
            activities: [],
            news: [],
            unreadCount: 0
        };
        this.refreshInterval = null;
        this.init();
    }

    init() {
        this.createPopupHTML();
        this.bindEvents();
        this.loadNotifications();
        this.startAutoRefresh();
    }

    createPopupHTML() {
        const popupHTML = `
            <div id="notifications-popup" class="notifications-popup">
                <div class="notifications-header">
                    <h3>Thông Báo</h3>
                    <button class="btn-close" onclick="notificationsPopup.closePopup()">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="notifications-tabs">
                    <button class="tab-btn active" data-tab="activity">
                        <span>Hoạt Động</span>
                        <span class="unread-badge" id="activity-badge"></span>
                    </button>
                    <button class="tab-btn" data-tab="news">
                        <span>Tin Tức</span>
                    </button>
                </div>
                <div class="notifications-content">
                    <div class="tab-content active" id="activity-tab">
                        <div class="notifications-list" id="activity-list">
                            <div class="loading">
                                <i class="fas fa-spinner fa-spin"></i>
                                <span>Đang tải...</span>
                            </div>
                        </div>
                    </div>
                    <div class="tab-content" id="news-tab">
                        <div class="notifications-list" id="news-list">
                            <div class="loading">
                                <i class="fas fa-spinner fa-spin"></i>
                                <span>Đang tải...</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="notifications-footer">
                    <button class="btn-mark-all-read" onclick="notificationsPopup.markAllAsRead()">
                        Đánh dấu tất cả đã đọc
                    </button>
                </div>
            </div>
            <div id="notifications-overlay" class="notifications-overlay" onclick="notificationsPopup.closePopup()"></div>
        `;

        // Thêm popup vào body nếu chưa tồn tại
        if (!document.getElementById('notifications-popup')) {
            document.body.insertAdjacentHTML('beforeend', popupHTML);
        }
    }

    bindEvents() {
        // Tab switching
        document.addEventListener('click', (e) => {
            if (e.target.closest('.tab-btn')) {
                const tabBtn = e.target.closest('.tab-btn');
                const tabName = tabBtn.dataset.tab;
                this.switchTab(tabName);
            }
        });

        // Notification bell click
        document.addEventListener('click', (e) => {
            if (e.target.closest('[href*="notifications.php"], .notifications-bell')) {
                e.preventDefault();
                this.togglePopup();
            }
        });

        // ESC key to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.isOpen) {
                this.closePopup();
            }
        });

        // Page focus/blur events để refresh notifications
        document.addEventListener('visibilitychange', () => {
            if (!document.hidden) {
                // Trang được focus lại, refresh notifications
                this.loadNotifications();
            }
        });

        // Window focus event - Removed to duplicate refreshing with visibilitychange
        // window.addEventListener('focus', () => {
        //     this.loadNotifications();
        // });

        // Storage event để sync giữa các tab
        window.addEventListener('storage', (e) => {
            if (e.key === 'notifications_updated') {
                this.loadNotifications();
            }
        });
    }

    startAutoRefresh() {
        // Auto-refresh notifications every 5 minutes (300 seconds)
        this.refreshInterval = setInterval(() => {
            if (!this.isOpen && !document.hidden) {
                this.loadNotifications();
            }
        }, 300 * 1000);
    }

    togglePopup() {
        if (this.isOpen) {
            this.closePopup();
        } else {
            this.openPopup();
        }
    }

    openPopup() {
        const popup = document.getElementById('notifications-popup');
        const overlay = document.getElementById('notifications-overlay');
        
        if (popup && overlay) {
            popup.classList.add('show');
            overlay.classList.add('show');
            document.body.classList.add('notifications-open');
            this.isOpen = true;
            this.loadNotifications();
        }
    }

    closePopup() {
        const popup = document.getElementById('notifications-popup');
        const overlay = document.getElementById('notifications-overlay');
        
        if (popup && overlay) {
            popup.classList.remove('show');
            overlay.classList.remove('show');
            document.body.classList.remove('notifications-open');
            this.isOpen = false;
        }
    }

    switchTab(tabName) {
        // Update tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

        // Update tab content
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.remove('active');
        });
        document.getElementById(`${tabName}-tab`).classList.add('active');

        this.currentTab = tabName;
    }

    async loadNotifications() {
        try {
            // console.log('Loading notifications from API...');
            const response = await fetch('/WebMuaBanDoCu/public/index.php?page=notification_api&action=get_notifications');
            
            // console.log('API Response status:', response.status);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                // console.error('Invalid response content type:', contentType);
                throw new Error('Server returned invalid JSON response');
            }
            
            const data = await response.json();
            // console.log('API Response data:', data);

            if (data.success) {
                this.notifications = data.data;
                this.renderNotifications();
                this.updateBadges();
                // console.log('Notifications loaded successfully');
            } else {
                console.error('API error:', data.error);
                this.showError(data.error || 'Không thể tải thông báo');
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            if (error.name === 'TypeError' && error.message.includes('fetch')) {
                this.showError('Không thể kết nối tới server. Vui lòng kiểm tra kết nối mạng.');
            } else if (error.message.includes('JSON')) {
                this.showError('Lỗi phản hồi từ server. Vui lòng thử lại sau.');
            } else {
                this.showError('Lỗi không xác định: ' + error.message);
            }
        }
    }

    renderNotifications() {
        this.renderActivityNotifications();
        this.renderNewsNotifications();
        this.updateFooterVisibility();
    }

    renderActivityNotifications() {
        const activityList = document.getElementById('activity-list');
        const activities = this.notifications.activities || [];
        const isLoggedIn = this.notifications.isLoggedIn !== false;

        if (!isLoggedIn) {
            activityList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <p>Vui lòng đăng nhập để xem danh sách hoạt động</p>
                    <button class="btn-login" onclick="window.location.href='/WebMuaBanDoCu/app/View/user/login.php'">
                        Đăng ký / Đăng nhập
                    </button>
                </div>
            `;
            return;
        }

        if (activities.length === 0) {
            activityList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-bell-slash"></i>
                    <p>Chưa có thông báo hoạt động nào</p>
                </div>
            `;
            return;
        }

        const activityHTML = activities.map(activity => `
            <div class="notification-item ${activity.is_read ? '' : 'unread'}" data-id="${activity.id}">
                <div class="notification-content">
                    <p>${activity.message}</p>
                    <span class="notification-time">${this.formatTime(activity.created_at)}</span>
                </div>
                ${!activity.is_read ? '<div class="unread-dot"></div>' : ''}
            </div>
        `).join('');

        activityList.innerHTML = activityHTML;
    }

    renderNewsNotifications() {
        const newsList = document.getElementById('news-list');
        const news = this.notifications.news || [];

        if (news.length === 0) {
            newsList.innerHTML = `
                <div class="empty-state">
                    <i class="fas fa-newspaper"></i>
                    <p>Không có tin tức mới</p>
                </div>
            `;
            return;
        }

        const newsHTML = news.map(item => `
            <div class="notification-item">
                <div class="notification-content">
                    <p>${item.message}</p>
                    <span class="notification-time">${this.formatTime(item.created_at)}</span>
                </div>
            </div>
        `).join('');

        newsList.innerHTML = newsHTML;
    }

    updateFooterVisibility() {
        const footer = document.querySelector('.notifications-footer');
        const isLoggedIn = this.notifications.isLoggedIn !== false;
        const hasUnread = this.notifications.unreadCount > 0;
        const hasActivities = this.notifications.activities && this.notifications.activities.length > 0;
        
        if (footer) {
            if (isLoggedIn && (hasUnread || hasActivities)) {
                footer.style.display = 'block';
            } else {
                footer.style.display = 'none';
            }
        }
    }

    updateBadges() {
        const badge = document.getElementById('activity-badge');
        // Chỉ target notification badges, không phải cart badges
        const headerBadges = document.querySelectorAll('.notifications-bell .badge, .badge.bg-danger:not(.cart-count)');
        
        // console.log('Updating badges with unread count:', this.notifications.unreadCount);
        
        if (this.notifications.unreadCount > 0) {
            const count = this.notifications.unreadCount > 99 ? '99+' : this.notifications.unreadCount;
            
            if (badge) {
                badge.textContent = count;
                badge.style.display = 'inline';
            }
            
            // Cập nhật tất cả notification badges trong header
            headerBadges.forEach(headerBadge => {
                if (headerBadge && !headerBadge.classList.contains('cart-count')) {
                    headerBadge.textContent = count;
                    headerBadge.style.display = 'flex';
                }
            });
        } else {
            if (badge) {
                badge.textContent = '';
                badge.style.display = 'none';
            }
            
            // Ẩn tất cả notification badges trong header
            headerBadges.forEach(headerBadge => {
                if (headerBadge && !headerBadge.classList.contains('cart-count')) {
                    headerBadge.style.display = 'none';
                }
            });
        }
        
        // Trigger storage event để sync với other tabs - REMOVED to prevent infinite loop
        // localStorage.setItem('notifications_updated', Date.now().toString());
    }

    async markAllAsRead() {
        if (!this.notifications.isLoggedIn) {
            this.showError('Vui lòng đăng nhập để sử dụng tính năng này');
            return;
        }

        try {
            const formData = new FormData();
            formData.append('action', 'mark_read');

            const response = await fetch('/WebMuaBanDoCu/public/index.php?page=notification_api', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();
            if (data.success) {
                this.loadNotifications();
                // Hiển thị thông báo thành công (tùy chọn)
                console.log('Đã đánh dấu tất cả thông báo là đã đọc');
            } else {
                this.showError('Không thể đánh dấu thông báo đã đọc');
            }
        } catch (error) {
            console.error('Error marking notifications as read:', error);
            this.showError('Lỗi kết nối. Vui lòng thử lại');
        }
    }

    formatTime(timeString) {
        const now = new Date();
        const time = new Date(timeString);
        const diffInMinutes = Math.floor((now - time) / (1000 * 60));

        if (diffInMinutes < 1) return 'Vừa xong';
        if (diffInMinutes < 60) return `${diffInMinutes} phút trước`;
        if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)} giờ trước`;
        if (diffInMinutes < 43200) return `${Math.floor(diffInMinutes / 1440)} ngày trước`;
        
        return time.toLocaleDateString('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric'
        });
    }

    showError(message) {
        const errorHTML = `
            <div class="error-state">
                <i class="fas fa-exclamation-triangle"></i>
                <p>${message}</p>
                <button onclick="notificationsPopup.loadNotifications()">Thử lại</button>
            </div>
        `;
        
        document.getElementById('activity-list').innerHTML = errorHTML;
        document.getElementById('news-list').innerHTML = errorHTML;
    }

    destroy() {
        if (this.refreshInterval) {
            clearInterval(this.refreshInterval);
        }
    }
}

// Initialize notifications popup when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.notificationsPopup = new NotificationsPopup();
});

// Function để refresh notifications từ bên ngoài
window.refreshNotifications = function() {
    if (window.notificationsPopup) {
        window.notificationsPopup.loadNotifications();
    }
};