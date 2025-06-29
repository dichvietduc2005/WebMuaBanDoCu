// // This file can be used for search-related JavaScript functionality in the future.
// // For example, implementing AJAX search suggestions.

// $(document).ready(function() {
//     // Xử lý tìm kiếm khi nhấn Enter hoặc bấm nút search
//     $('#search-form').on('submit', function(e) {
//         e.preventDefault();
//         var keyword = $('#search-input').val().trim();        if (keyword.length === 0) {
//             window.location.href = '../../../public/TrangChu.php';
//             return;
//         }
//         // Chuyển hướng sang trang kết quả tìm kiếm với tham số keyword
//         window.location.href = 'products.php?keyword=' + encodeURIComponent(keyword);
//     });

//     // Tính năng gợi ý tìm kiếm (autocomplete) - có thể mở rộng sau
//     $('#search-input').on('input', function() {
//         var keyword = $(this).val().trim();
//         if (keyword.length >= 2) {
//             // TODO: Có thể thêm AJAX để lấy gợi ý từ server
//             // $.get('ajax/search_suggestions.php', {q: keyword}, function(data) {
//             //     // Hiển thị danh sách gợi ý
//             // });
//         }
//     });
// });
