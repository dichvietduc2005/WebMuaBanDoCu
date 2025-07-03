/**
 * search.js - Tính năng tìm kiếm nâng cao và gợi ý
 * Cung cấp tính năng tìm kiếm, autocomplete, và trải nghiệm người dùng tốt hơn
 */

$(document).ready(function() {
    const searchInput = $('#search-input');
    const searchForm = $('#search-form');
    const searchSuggestions = $('<div class="search-suggestions"></div>');
    let typingTimer;
    const doneTypingInterval = 300; // thời gian chờ sau khi người dùng ngừng gõ (ms)
    
    // Thêm container gợi ý vào DOM
    searchInput.after(searchSuggestions);
    
    // Thêm nút xóa vào bên phải input
    const clearButton = $('<button type="button" class="search-clear-btn"><i class="fas fa-times"></i></button>');
    searchInput.after(clearButton);
    clearButton.hide();
    
    // Xử lý tìm kiếm khi nhấn Enter hoặc bấm nút search
    searchForm.on('submit', function(e) {
        e.preventDefault();
        let keyword = searchInput.val().trim();
        
        if (keyword.length === 0) {
            window.location.href = '/WebMuaBanDoCu/public/index.php';
            return;
        }
        // Chuyển hướng sang trang kết quả tìm kiếm với tham số keyword
        window.location.href = '/WebMuaBanDoCu/app/View/product/products.php?keyword=' + encodeURIComponent(keyword);
    });

    // Xử lý nút xóa
    clearButton.on('click', function() {
        searchInput.val('').focus();
        searchSuggestions.hide();
        clearButton.hide();
    });

    // Hiện/ẩn nút xóa dựa trên nội dung input
    searchInput.on('input', function() {
        const keyword = $(this).val().trim();
        
        // Hiển thị/ẩn nút xóa
        if (keyword.length > 0) {
            clearButton.show();
        } else {
            clearButton.hide();
            searchSuggestions.hide();
        }
        
        // Thiết lập timer cho tính năng gợi ý
        clearTimeout(typingTimer);
        if (keyword.length >= 2) {
            typingTimer = setTimeout(function() {
                fetchSuggestions(keyword);
            }, doneTypingInterval);
        } else {
            searchSuggestions.hide();
        }
    });
    
    // Tính năng gợi ý tìm kiếm (autocomplete)
    function fetchSuggestions(keyword) {
        $.ajax({
            url: '/WebMuaBanDoCu/app/Controllers/extra/api.php',
            method: 'GET',
            data: {
                action: 'search_suggestions',
                keyword: keyword,
                limit: 8
            },
            dataType: 'json',
            success: function(data) {
                if (data.success && data.suggestions.length > 0) {
                    displaySuggestions(data.suggestions);
                } else {
                    searchSuggestions.hide();
                }
            },
            error: function() {
                searchSuggestions.hide();
            }
        });
    }
    
    // Hiển thị danh sách gợi ý
    function displaySuggestions(suggestions) {
        searchSuggestions.empty();
        suggestions.forEach(function(suggestion) {
            const item = $('<div class="suggestion-item"></div>').text(suggestion);
            
            // Xử lý khi click vào gợi ý
            item.on('click', function() {
                searchInput.val(suggestion);
                searchSuggestions.hide();
                searchForm.submit();
            });
            
            searchSuggestions.append(item);
        });
        searchSuggestions.show();
    }
    
    // Ẩn gợi ý khi click ra ngoài
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.search-container').length) {
            searchSuggestions.hide();
        }
    });
    
    // Điều hướng trong danh sách gợi ý bằng phím mũi tên
    searchInput.on('keydown', function(e) {
        const suggestions = $('.suggestion-item');
        const activeClass = 'active';
        let activeIndex = -1;
        
        // Tìm phần tử đang active
        suggestions.each(function(index) {
            if ($(this).hasClass(activeClass)) {
                activeIndex = index;
                return false;
            }
        });
        
        switch (e.keyCode) {
            case 40: // phím xuống
                e.preventDefault();
                suggestions.removeClass(activeClass);
                activeIndex = (activeIndex + 1) % suggestions.length;
                suggestions.eq(activeIndex).addClass(activeClass);
                break;
                
            case 38: // phím lên
                e.preventDefault();
                suggestions.removeClass(activeClass);
                activeIndex = activeIndex <= 0 ? suggestions.length - 1 : activeIndex - 1;
                suggestions.eq(activeIndex).addClass(activeClass);
                break;
                
            case 13: // phím Enter
                if (activeIndex >= 0) {
                    e.preventDefault();
                    searchInput.val(suggestions.eq(activeIndex).text());
                    searchSuggestions.hide();
                    searchForm.submit();
                }
                break;
                
            case 27: // phím ESC
                searchSuggestions.hide();
                break;
        }
    });
    
    // Highlight các từ tìm kiếm trong kết quả
    function highlightKeyword(text, keyword) {
        if (!keyword) return text;
        const regex = new RegExp('(' + keyword + ')', 'gi');
        return text.replace(regex, '<span class="highlight">$1</span>');
    }
    
    // Áp dụng highlight nếu đang ở trang kết quả tìm kiếm
    const urlParams = new URLSearchParams(window.location.search);
    const searchKeyword = urlParams.get('keyword');
    
    if (searchKeyword) {
        $('.product-title').each(function() {
            const originalText = $(this).text();
            $(this).html(highlightKeyword(originalText, searchKeyword));
        });
    }
});
