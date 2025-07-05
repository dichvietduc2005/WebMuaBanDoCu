// Real-time Cart Count Manager
class CartCountManager {
    constructor() {
        this.apiUrl = '/WebMuaBanDoCu/app/Controllers/cart/CartCountAPI.php';
        this.updateInterval = 5000; // Update every 5 seconds
        this.intervalId = null;
        this.lastCount = 0;
        this.isUserLoggedIn = false;
        
        this.init();
    }

    init() {
        // Check if user is logged in by looking for cart elements
        this.isUserLoggedIn = document.querySelector('.cart-count') !== null || 
                               document.querySelector('[href*="/cart/index.php"]') !== null;
        
        if (this.isUserLoggedIn) {
            this.startPeriodicUpdate();
            this.setupEventListeners();
        }
    }

    startPeriodicUpdate() {
        // Update immediately
        this.updateCartCount();
        
        // Then update periodically
        this.intervalId = setInterval(() => {
            this.updateCartCount();
        }, this.updateInterval);
    }

    stopPeriodicUpdate() {
        if (this.intervalId) {
            clearInterval(this.intervalId);
            this.intervalId = null;
        }
    }

    async updateCartCount() {
        try {
            const response = await fetch(this.apiUrl, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'Cache-Control': 'no-cache'
                }
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            
            if (data.success) {
                this.updateCartUI(data.count);
            }
        } catch (error) {
            console.error('Error updating cart count:', error);
        }
    }

    updateCartUI(newCount) {
        const count = parseInt(newCount) || 0;
        
        // Only update if count has changed
        if (count === this.lastCount) {
            return;
        }
        
        this.lastCount = count;
        
        // Find all cart count elements
        const cartCountElements = document.querySelectorAll('.cart-count');
        
        cartCountElements.forEach(element => {
            if (count > 0) {
                // Show the badge and update count
                element.textContent = count > 99 ? '99+' : count.toString();
                element.style.display = 'flex';
                
                // Add animation effect
                element.style.transform = 'scale(1.2)';
                element.style.transition = 'transform 0.2s ease';
                setTimeout(() => {
                    element.style.transform = 'scale(1)';
                }, 200);
            } else {
                // Hide the badge if count is 0
                element.style.display = 'none';
            }
        });
        
        // Update any cart count text elements
        const cartCountTexts = document.querySelectorAll('.cart-count-text');
        cartCountTexts.forEach(element => {
            element.textContent = count.toString();
        });
        
        // Trigger custom event for other parts of the application
        window.dispatchEvent(new CustomEvent('cartCountUpdated', {
            detail: { count: count }
        }));
    }

    setupEventListeners() {
        // Listen for cart-related events
        document.addEventListener('cartItemAdded', () => {
            // Update immediately when item is added
            setTimeout(() => this.updateCartCount(), 500);
        });

        document.addEventListener('cartItemRemoved', () => {
            // Update immediately when item is removed
            setTimeout(() => this.updateCartCount(), 500);
        });

        document.addEventListener('cartCleared', () => {
            // Update immediately when cart is cleared
            setTimeout(() => this.updateCartCount(), 500);
        });

        // Listen for visibility changes to pause/resume updates
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                this.stopPeriodicUpdate();
            } else {
                this.startPeriodicUpdate();
            }
        });

        // Listen for focus/blur events
        window.addEventListener('focus', () => {
            if (!this.intervalId) {
                this.startPeriodicUpdate();
            }
        });
    }

    // Manual update method for external use
    forceUpdate() {
        this.updateCartCount();
    }

    // Set custom update interval
    setUpdateInterval(milliseconds) {
        this.updateInterval = milliseconds;
        
        if (this.intervalId) {
            this.stopPeriodicUpdate();
            this.startPeriodicUpdate();
        }
    }
}

// Initialize cart count manager when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.cartCountManager === 'undefined') {
        window.cartCountManager = new CartCountManager();
    }
});

// Helper functions for backward compatibility
window.updateCartCount = function() {
    if (window.cartCountManager) {
        window.cartCountManager.forceUpdate();
    }
};

window.triggerCartUpdate = function() {
    if (window.cartCountManager) {
        window.cartCountManager.forceUpdate();
    }
};
