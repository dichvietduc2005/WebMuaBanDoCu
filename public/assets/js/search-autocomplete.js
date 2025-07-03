/**
 * Search Autocomplete functionality
 * Thêm tính năng gợi ý tìm kiếm real-time cho thanh search
 */

class SearchAutocomplete {
    constructor() {
        this.searchInput = document.getElementById('search-input');
        this.searchForm = document.getElementById('search-form2');
        this.suggestionsList = null;
        this.isLoading = false;
        this.debounceTimer = null;
        this.currentFocus = -1;
        
        this.init();
    }

    init() {
        if (!this.searchInput || !this.searchForm) return;
        
        this.createSuggestionsList();
        this.bindEvents();
    }

    createSuggestionsList() {
        // Tạo container cho suggestions
        this.suggestionsList = document.createElement('div');
        this.suggestionsList.className = 'search-suggestions';
        this.suggestionsList.style.display = 'none';
        
        // Thêm vào DOM
        this.searchForm.style.position = 'relative';
        this.searchForm.appendChild(this.suggestionsList);
    }

    bindEvents() {
        // Input event cho autocomplete
        this.searchInput.addEventListener('input', (e) => {
            this.handleInput(e.target.value);
        });

        // Keyboard navigation
        this.searchInput.addEventListener('keydown', (e) => {
            this.handleKeydown(e);
        });

        // Click outside để đóng suggestions
        document.addEventListener('click', (e) => {
            if (!this.searchForm.contains(e.target)) {
                this.hideSuggestions();
            }
        });

        // Focus event
        this.searchInput.addEventListener('focus', () => {
            if (this.searchInput.value.trim().length >= 2) {
                this.showSuggestions();
            }
        });
    }

    handleInput(value) {
        clearTimeout(this.debounceTimer);
        
        if (value.trim().length < 2) {
            this.hideSuggestions();
            return;
        }

        // Debounce để tránh call API quá nhiều
        this.debounceTimer = setTimeout(() => {
            this.fetchSuggestions(value.trim());
        }, 300);
    }

    async fetchSuggestions(keyword) {
        if (this.isLoading) return;

        this.isLoading = true;
        this.showLoadingState();

        try {
            const response = await fetch(`/WebMuaBanDoCu/app/Controllers/extra/api.php?action=search_suggestions&keyword=${encodeURIComponent(keyword)}`);
            const data = await response.json();

            if (data.success && data.suggestions) {
                this.displaySuggestions(data.suggestions, keyword);
            } else {
                this.hideSuggestions();
            }
        } catch (error) {
            console.error('Error fetching suggestions:', error);
            this.hideSuggestions();
        } finally {
            this.isLoading = false;
        }
    }

    showLoadingState() {
        this.suggestionsList.innerHTML = `
            <div class="suggestion-item loading">
                <i class="fas fa-spinner fa-spin"></i>
                <span>Đang tìm kiếm...</span>
            </div>
        `;
        this.showSuggestions();
    }

    displaySuggestions(suggestions, keyword) {
        if (!suggestions || suggestions.length === 0) {
            this.hideSuggestions();
            return;
        }

        const html = suggestions.map((suggestion, index) => {
            const highlightedText = this.highlightKeyword(suggestion, keyword);
            return `
                <div class="suggestion-item" data-index="${index}" data-value="${suggestion}">
                    <i class="fas fa-search"></i>
                    <span>${highlightedText}</span>
                </div>
            `;
        }).join('');

        this.suggestionsList.innerHTML = html;
        this.showSuggestions();
        this.bindSuggestionEvents();
    }

    highlightKeyword(text, keyword) {
        const regex = new RegExp(`(${keyword})`, 'gi');
        return text.replace(regex, '<strong>$1</strong>');
    }

    bindSuggestionEvents() {
        const items = this.suggestionsList.querySelectorAll('.suggestion-item:not(.loading)');
        
        items.forEach((item, index) => {
            item.addEventListener('click', () => {
                const value = item.getAttribute('data-value');
                this.selectSuggestion(value);
            });

            item.addEventListener('mouseenter', () => {
                this.setFocus(index);
            });
        });
    }

    handleKeydown(e) {
        const items = this.suggestionsList.querySelectorAll('.suggestion-item:not(.loading)');
        
        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.currentFocus = Math.min(this.currentFocus + 1, items.length - 1);
                this.updateFocus(items);
                break;
                
            case 'ArrowUp':
                e.preventDefault();
                this.currentFocus = Math.max(this.currentFocus - 1, -1);
                this.updateFocus(items);
                break;
                
            case 'Enter':
                if (this.currentFocus >= 0 && items[this.currentFocus]) {
                    e.preventDefault();
                    const value = items[this.currentFocus].getAttribute('data-value');
                    this.selectSuggestion(value);
                }
                break;
                
            case 'Escape':
                this.hideSuggestions();
                this.searchInput.blur();
                break;
        }
    }

    setFocus(index) {
        this.currentFocus = index;
        const items = this.suggestionsList.querySelectorAll('.suggestion-item:not(.loading)');
        this.updateFocus(items);
    }

    updateFocus(items) {
        items.forEach((item, index) => {
            item.classList.toggle('focused', index === this.currentFocus);
        });
    }

    selectSuggestion(value) {
        this.searchInput.value = value;
        this.hideSuggestions();
        this.searchForm.submit();
    }

    showSuggestions() {
        this.suggestionsList.style.display = 'block';
        this.currentFocus = -1;
    }

    hideSuggestions() {
        this.suggestionsList.style.display = 'none';
        this.currentFocus = -1;
    }
}

// CSS cho search suggestions
const searchAutocompleteCSS = `
<style>
.search-suggestions {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #e5e7eb;
    border-top: none;
    border-radius: 0 0 8px 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    z-index: 1000;
    max-height: 300px;
    overflow-y: auto;
}

.suggestion-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 16px;
    cursor: pointer;
    border-bottom: 1px solid #f3f4f6;
    transition: background-color 0.2s ease;
}

.suggestion-item:last-child {
    border-bottom: none;
}

.suggestion-item:hover,
.suggestion-item.focused {
    background-color: #f8fafc;
}

.suggestion-item.loading {
    cursor: default;
    color: #6b7280;
}

.suggestion-item i {
    color: #9ca3af;
    font-size: 14px;
    width: 16px;
}

.suggestion-item.loading i {
    color: #3b82f6;
}

.suggestion-item span {
    flex: 1;
    font-size: 14px;
    color: #374151;
}

.suggestion-item strong {
    color: #3b82f6;
    font-weight: 600;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .search-suggestions {
        font-size: 16px; /* Prevent zoom on iOS */
    }
    
    .suggestion-item {
        padding: 14px 16px;
    }
    
    .suggestion-item span {
        font-size: 15px;
    }
}
</style>
`;

// Thêm CSS vào head
document.head.insertAdjacentHTML('beforeend', searchAutocompleteCSS);

// Khởi tạo khi DOM ready
document.addEventListener('DOMContentLoaded', function() {
    new SearchAutocomplete();
});
