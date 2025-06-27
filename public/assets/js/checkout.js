document.addEventListener("DOMContentLoaded", function () {
  // Quantity controls
  const quantityInputs = document.querySelectorAll(".quantity-input");
  const decreaseBtns = document.querySelectorAll(".quantity-decrease");
  const increaseBtns = document.querySelectorAll(".quantity-increase");
  const removeBtns = document.querySelectorAll(".remove-btn");

  // Update quantity
  function updateQuantity(input, change) {
    let currentValue = parseInt(input.value);
    let newValue = currentValue + change;

    if (newValue < 1) newValue = 1;

    input.value = newValue;
    updateItemTotal(input);
    showToast("Cập nhật giỏ hàng thành công", "success");
  }

  // Update item total
  function updateItemTotal(input) {
    const item = input.closest(".cart-item");
    const priceText = item.querySelector(
      ".item-price span:last-child"
    ).textContent;
    const price = parseInt(priceText.replace(/[^\d]/g, ""));
    const quantity = parseInt(input.value);
    const total = price * quantity;

    item.querySelector(".item-total").textContent =
      formatCurrency(total) + " VNĐ";

    updateOrderSummary();
  }

  // Format currency
  function formatCurrency(amount) {
    return amount.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
  }

  // Update order summary
  function updateOrderSummary() {
    let subtotal = 0;

    document.querySelectorAll(".item-total").forEach((totalElement) => {
      const totalText = totalElement.textContent;
      subtotal += parseInt(totalText.replace(/[^\d]/g, ""));
    });

    const discount = 500000;
    const total = subtotal - discount;

    document.querySelector(
      ".summary-row:nth-child(1) .summary-value"
    ).textContent = formatCurrency(subtotal) + " VNĐ";

    document.querySelector(
      ".summary-row.total .summary-total-value"
    ).textContent = formatCurrency(total) + " VNĐ";
  }

  // Show toast notification
  function showToast(message, type = "success") {
    const toastContainer = document.querySelector(".toast-container");
    const toastId = "toast-" + Date.now();

    const toast = document.createElement("div");
    toast.className = `toast ${type === "error" ? "toast-error" : ""}`;
    toast.id = toastId;
    toast.innerHTML = `
                    <div class="toast-icon">
                        <i class="fas ${
                          type === "error"
                            ? "fa-exclamation-circle"
                            : "fa-check-circle"
                        }"></i>
                    </div>
                    <div class="toast-content">
                        <div class="toast-header">
                            <div class="toast-title">${
                              type === "error" ? "Lỗi" : "Thành công"
                            }</div>
                            <button class="toast-close" onclick="document.getElementById('${toastId}').remove()">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <div class="toast-message">${message}</div>
                    </div>
                `;

    toastContainer.appendChild(toast);

    // Auto remove after 3 seconds
    setTimeout(() => {
      if (document.getElementById(toastId)) {
        toast.style.animation = "slideOut 0.3s ease";
        setTimeout(() => {
          toast.remove();
        }, 300);
      }
    }, 3000);
  }

  // Remove item
  function removeItem(btn) {
    const item = btn.closest(".cart-item");
    item.classList.add("removing");

    setTimeout(() => {
      item.remove();
      updateOrderSummary();
      showToast("Đã xóa sản phẩm khỏi giỏ hàng", "success");

      // Update cart count
      const cartBadge = document.querySelector(".badge.bg-danger");
      if (cartBadge) {
        const currentCount = parseInt(cartBadge.textContent);
        cartBadge.textContent = currentCount - 1;
      }
    }, 300);
  }

  // Event listeners
  decreaseBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const input = btn.parentElement.querySelector(".quantity-input");
      updateQuantity(input, -1);
    });
  });

  increaseBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      const input = btn.parentElement.querySelector(".quantity-input");
      updateQuantity(input, 1);
    });
  });

  quantityInputs.forEach((input) => {
    input.addEventListener("change", () => {
      if (input.value < 1) input.value = 1;
      updateItemTotal(input);
    });
  });

  removeBtns.forEach((btn) => {
    btn.addEventListener("click", () => {
      removeItem(btn);
    });
  });

  // Checkout button
  document
    .querySelector(".checkout-btn")
    .addEventListener("click", function () {
      const isChecked = document.getElementById("termsCheck").checked;

      if (!isChecked) {
        showToast("Vui lòng chấp nhận điều khoản và điều kiện", "error");
        return;
      }

      // Here you would normally redirect to checkout page
      showToast("Đang chuyển hướng đến trang thanh toán...", "success");
    });

  // Initialize
  updateOrderSummary();
});
