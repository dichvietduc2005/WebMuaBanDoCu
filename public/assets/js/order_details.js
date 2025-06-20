function cancelOrder(orderId) {
  const reason = prompt("Vui lòng nhập lý do hủy đơn hàng:");
  if (reason !== null && reason.trim() !== "") {
    fetch("../../modules/order/cancel_order.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        order_id: orderId,
        reason: reason.trim(),
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Đơn hàng đã được hủy thành công");
          location.reload(); // Tải lại trang để cập nhật trạng thái
        } else {
          alert("Có lỗi xảy ra: " + data.message);
        }
      })
      .catch((error) => {
        alert("Có lỗi xảy ra khi hủy đơn hàng.");
        console.error("Error:", error);
      });
  }
}

function reorderItems(orderId) {
  // Thêm orderId làm tham số
  if (
    confirm(
      "Bạn có muốn thêm tất cả sản phẩm trong đơn hàng này vào giỏ hàng không?"
    )
  ) {
    // TODO: Triển khai logic gọi API để thêm lại sản phẩm vào giỏ hàng.
    // Ví dụ:

    fetch("../../modules/order/reorder.php", {
      // Giả sử bạn có endpoint này
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ order_id: orderId }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Các sản phẩm đã được thêm lại vào giỏ hàng!");
          // Cập nhật icon giỏ hàng nếu có
          if (
            typeof updateCartIcon === "function" &&
            data.cartItemCount !== undefined
          ) {
            updateCartIcon(data.cartItemCount);
          }
          // Có thể chuyển hướng đến trang giỏ hàng
          // window.location.href = '../../public/cart/';
        } else {
          alert("Lỗi khi đặt lại đơn hàng: " + data.message);
        }
      })
      .catch((error) => {
        console.error("Error reordering:", error);
        alert("Lỗi kết nối khi đặt lại đơn hàng.");
      });

    alert(
      'Chức năng "Đặt lại đơn hàng" đang được phát triển. Yêu cầu Order ID: ' +
        orderId
    );
  }
}

function printOrder() {
  window.print();
}

document.addEventListener("DOMContentLoaded", function () {
  // Animation cho card chi tiết đơn hàng
  const card = document.querySelector(".order-details-card");
  if (card) {
    card.style.opacity = "0";
    card.style.transform = "translateY(30px)";

    setTimeout(() => {
      card.style.transition = "all 0.6s ease";
      card.style.opacity = "1";
      card.style.transform = "translateY(0)";
    }, 100);
  }

  // Tự động thêm CSS cho việc in ấn vào <head>
  const printStyles = `
        @media print {


            body {
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                font-size: 11pt;
                color: #000 !important;
                background-color: #fff !important;
                margin: 0;
                padding: 0;
            }

            * {
                box-shadow: none !important;
                text-shadow: none !important;
            }

            .breadcrumb,
            .order-details-body .detail-section:last-child .d-flex.flex-wrap.gap-3,
            .btn,
            header, /* Assuming a general <header> tag for the site header */
            footer /* Assuming a general <footer> tag for the site footer */
            /* Add any other specific selectors for elements to hide, e.g., #main-nav, .sidebar */
            {
                display: none !important;
            }

            .order-details-container {
                max-width: 100% !important;
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important; /* Thử giảm padding của container này xuống 0 */
                                       /* Hoặc một giá trị nhỏ, ví dụ 5px, để lề trang @page xử lý */
                border: none !important;
                /* page-break-before: avoid; */ /* Đảm bảo container không tự ngắt trang trước nó */
            }

            .order-details-card {
                border: 1px solid #ddd !important;
                padding: 15px; /* Padding này ổn */
                margin-bottom: 20px;
                margin-top: 0 !important; /* Quan trọng: Thử reset margin-top của card */
                /* page-break-before: avoid; */ /* Đảm bảo card không tự ngắt trang trước nó */
            }

            .order-details-header {
                background-color: #f0f0f0 !important;
                color: #000 !important;
                padding: 10px 15px !important;
                border-bottom: 1px solid #ddd !important;
                text-align: center;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .order-details-header h1 {
                font-size: 1.5rem !important; /* Or 16pt */
                margin-bottom: 5px !important;
            }

            .order-details-header .d-flex.gap-3 { /* Status badges container */
                justify-content: center !important;
                margin-top: 10px !important;
            }

            .detail-section {
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 1px dashed #eee;
            }
            .detail-section:last-child {
                border-bottom: none;
            }

            .section-title {
                font-size: 1.1rem !important; /* Or 13pt */
                color: #333 !important;
                margin-bottom: 10px;
                padding-bottom: 5px;
                border-bottom: 1px solid #ccc;
            }
            .section-title i {
                margin-right: 8px;
            }

            .detail-grid {
                display: grid;
                /* Consider a simpler layout for print if grid is too complex */
                /* grid-template-columns: 1fr; */ /* Stack all items */
                grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); /* Adjust minmax */
                gap: 8px 15px; /* row-gap column-gap */
            }

            .detail-item {
                display: flex;
                flex-direction: column;
                padding: 4px 0;
                 page-break-inside: avoid; /* Avoid breaking individual items */
            }
            .detail-label {
                font-weight: bold;
                color: #444 !important; /* Slightly darker for better contrast */
                margin-bottom: 2px;
                font-size: 0.85rem; /* Or 9pt */
            }
            .detail-value {
                font-size: 0.9rem; /* Or 10pt */
            }
            .detail-value .badge {
                font-size: 0.8rem !important; /* Or 8pt */
            }

            .badge {
                border: 1px solid #888 !important; /* Darker border for badges */
                padding: .25em .5em !important;
                background-color: #fff !important;
                color: #000 !important;
                font-weight: normal !important;
                font-size: 0.85em !important; /* Or 9pt */
                border-radius: 3px !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                display: inline-block; /* Ensure proper rendering */
                line-height: 1.3; /* Adjust line height for badges */
            }

            /* Keep color hints for borders if desired, but ensure text is black */
            .badge.bg-success, .badge.text-success { border-color: #28a745 !important; }
            .badge.bg-danger, .badge.text-danger { border-color: #dc3545 !important; }
            .badge.bg-warning, .badge.text-warning { border-color: #ffc107 !important; }
            /* Add other badge types if necessary */

            .items-table {
                width: 100%;
                border-collapse: collapse !important;
                margin-top: 15px;
            }
            .items-table th, .items-table td {
                border: 1px solid #bbb !important; /* Slightly darker table borders */
                padding: 6px 8px !important; /* Adjust padding */
                text-align: left;
                vertical-align: top;
                font-size: 0.9rem; /* Or 10pt for table content */
            }
            .items-table th {
                background-color: #e0e0e0 !important; /* Lighter gray for table header */
                font-weight: bold;
                color: #000 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .items-table .product-info {
                display: flex;
                align-items: center; /* Vertically align items in product-info */
            }
            .items-table .product-image {
                max-width: 45px !important;
                max-height: 45px !important;
                object-fit: contain;
                border: 1px solid #ccc;
                margin-right: 8px;
                vertical-align: middle;
            }
            .items-table .product-placeholder {
                width: 45px; height: 45px; border: 1px solid #ccc; margin-right: 8px;
                display: flex; align-items: center; justify-content: center; color: #999;
            }
            .items-table .product-name {
                font-weight: bold;
                margin-bottom: 2px;
                font-size: 0.9rem; /* Match table content */
            }
            .items-table .price, .items-table .quantity {
                text-align: right;
            }
            .items-table td[data-label]::before { /* Hide data-label for responsive tables */
                display: none;
            }

            .order-total {
                margin-top: 15px;
                text-align: right;
                padding-top: 8px;
                border-top: 1px solid #aaa; /* Darker border for total */
            }
            .order-total h4 {
                font-size: 1.1rem !important; /* Or 13pt */
                font-weight: bold;
            }
            .total-amount {
                color: #000 !important;
            }

            a {
                text-decoration: none !important;
                color: #000 !important;
            }
            /* To show URLs of links (optional, can be noisy) */
            /*
            a[href]::after {
                content: " (" attr(href) ")";
                font-size: 0.8em;
                color: #555;
                display: none; // Default to hidden, enable if needed for specific links
            }
            a[href^="http"]:not([href*="localhost"]):not([href*="${window.location.hostname}"])::after {
                display: inline; // Show for external links only
            }
            */

            /* Page break control */
            .detail-section, .items-table tr {
    page-break-inside: avoid;
}

            .items-table thead { /* Ensure table header repeats on new pages */
                display: table-header-group !important;
            }
             .items-table tbody {
                display: table-row-group !important;
            }


            /* Page setup */
            @page {
                margin: 0.7in; /* Adjust margins as needed */
                /* Example: Add page numbers - browser support varies
                @bottom-center {
                    content: "Page " counter(page) " of " counter(pages);
                    font-size: 9pt;
                    color: #666;
                }
                */
            }

            /* Optional: Add a "Printed on" date */
            body::before { /* Use ::before to put it at the top */
                display: block;
                content: "Printed on: " attr(data-print-date);
                text-align: right;
                font-size: 0.8em;
                color: #555;
                margin-bottom: 10px;
            }
        }
    `;
  const styleSheet = document.createElement("style");
  styleSheet.type = "text/css";
  // Use textContent for modern browsers, innerText for older ones (though appendChild(createTextNode) is more robust)
  if (styleSheet.styleSheet) {
    // IE
    styleSheet.styleSheet.cssText = printStyles;
  } else {
    styleSheet.appendChild(document.createTextNode(printStyles));
  }
  document.head.appendChild(styleSheet);

  // Set the print date attribute on the body
  const today = new Date();
  const formattedDate = `${today.getDate()}/${
    today.getMonth() + 1
  }/${today.getFullYear()} ${today.getHours()}:${String(
    today.getMinutes()
  ).padStart(2, "0")}`;
  document.body.setAttribute("data-print-date", formattedDate);

  // Animation for card (remains unchanged)
  // const card = document.querySelector('.order-details-card'); // Already declared above
  // if (card) { ... } // This part is fine
});

// Các sự kiện beforeprint và afterprint có thể giữ lại nếu bạn muốn thêm logic JS phức tạp hơn
// Ví dụ: thay đổi class của body để áp dụng style đặc biệt không chỉ qua @media print
window.addEventListener("beforeprint", function () {
  // document.body.classList.add('is-printing');
});

window.addEventListener("afterprint", function () {
  // document.body.classList.remove('is-printing');
});
