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
        this.init();
    }

    init() {
        this.createPopupHTML();
        this.bindEvents();
        this.loadNotifications();
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
            console.log('Loading notifications from API...');
            const response = await fetch('/WebMuaBanDoCu/public/index.php?page=notification_api&action=get_notifications');
            
            console.log('API Response status:', response.status);
            console.log('API Response headers:', response.headers);
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Invalid response content type:', contentType);
                console.error('Response text:', text);
                throw new Error('Server returned invalid JSON response');
            }
            
            const data = await response.json();
            console.log('API Response data:', data);

            if (data.success) {
                this.notifications = data.data;
                this.renderNotifications();
                this.updateBadges();
                console.log('Notifications loaded successfully');
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
    }

    renderActivityNotifications() {
        const activityList = document.getElementById('activity-list');
        const activities = this.notifications.activities || [];

        if (activities.length === 0) {
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

    updateBadges() {
        const badge = document.getElementById('activity-badge');
        const headerBadge = document.querySelector('.navbar .badge');
        
        if (this.notifications.unreadCount > 0) {
            const count = this.notifications.unreadCount > 99 ? '99+' : this.notifications.unreadCount;
            if (badge) badge.textContent = count;
            if (headerBadge) headerBadge.textContent = count;
        } else {
            if (badge) badge.textContent = '';
            if (headerBadge) headerBadge.style.display = 'none';
        }
    }

    async markAllAsRead() {
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
            }
        } catch (error) {
            console.error('Error marking notifications as read:', error);
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
}

// Initialize notifications popup when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.notificationsPopup = new NotificationsPopup();
});

// Auto-refresh notifications every 5 minutes
setInterval(() => {
    if (window.notificationsPopup && !window.notificationsPopup.isOpen) {
        window.notificationsPopup.loadNotifications();
    }
}, 5 * 60 * 1000); 