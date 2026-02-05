document.addEventListener("DOMContentLoaded", function () {
  const paymentOptions = document.querySelectorAll(".payment-option");
  const checkoutForm = document.getElementById("checkout-form");

  // Handle payment option selection styling
  paymentOptions.forEach((option) => {
    option.addEventListener("click", function () {
      const radio = this.querySelector('input[type="radio"]');
      if (radio && !radio.disabled) {
        radio.checked = true;
        // Update active class
        paymentOptions.forEach(opt => opt.classList.remove('active'));
        this.classList.add('active');
      }
    });
  });

  // Validation before submit
  if (checkoutForm) {
    checkoutForm.addEventListener("submit", function (e) {
      const fullname = document.getElementById('txt_billing_fullname').value;
      const province = document.getElementById('province').value;
      const district = document.getElementById('district').value;
      const ward = document.getElementById('ward').value;
      const address = document.getElementById('specific_address').value;

      // Bỏ district vì dùng API v2 (2 cấp)
      if (!fullname || !province || !ward || !address) {
        e.preventDefault();
        showToast("Vui lòng điền đầy đủ thông tin giao hàng", "error");
        return;
      }

      showToast("Đang chuyển hướng đến cổng thanh toán...", "success");
    });
  }

  // Show toast notification
  function showToast(message, type = "success") {
    // Check if container exists, if not create it
    let toastContainer = document.querySelector(".toast-container");
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.className = 'toast-container';
      document.body.appendChild(toastContainer);
    }

    const toastId = "toast-" + Date.now();
    const toast = document.createElement("div");
    toast.className = `toast ${type === "error" ? "toast-error" : ""}`;
    toast.id = toastId;
    toast.style.display = "flex";

    toast.innerHTML = `
        <div class="toast-icon">
            <i class="fas ${type === "error" ? "fa-exclamation-circle" : "fa-check-circle"}"></i>
        </div>
        <div class="toast-content">
            <div class="toast-header" style="background: none; border: none; padding: 0; display: flex; justify-content: space-between; align-items: center;">
                <div class="toast-title" style="font-weight: 700;">${type === "error" ? "Lỗi" : "Thành công"}</div>
                <button class="toast-close" type="button" style="background: none; border: none; cursor: pointer; color: #aaa;">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="toast-message">${message}</div>
        </div>
    `;

    toastContainer.appendChild(toast);

    // Close button event
    toast.querySelector('.toast-close').addEventListener('click', () => toast.remove());

    // Auto remove after 3 seconds
    setTimeout(() => {
      const t = document.getElementById(toastId);
      if (t) {
        t.style.animation = "slideOut 0.3s ease";
        setTimeout(() => {
          t.remove();
        }, 300);
      }
    }, 3000);
  }

  // Reusable function to apply coupon
  async function applyCouponCode(code, btnElement) {
    if (!code) {
      showToast("Vui lòng chọn hoặc nhập mã giảm giá", "error");
      return;
    }

    if (btnElement) {
      btnElement.disabled = true;
      btnElement.dataset.originalText = btnElement.innerHTML;
      btnElement.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
    }

    try {
      const baseUrl = window.baseUrl || (window.APP_CONFIG ? window.APP_CONFIG.baseUrl : '/');
      const response = await fetch(`${baseUrl}app/Controllers/cart/CartController.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=apply_coupon&code=${encodeURIComponent(code)}`
      });
      const data = await response.json();

      if (data.success) {
        showToast(data.message, "success");
        setTimeout(() => location.reload(), 500);
      } else {
        showToast(data.message, "error");
        if (btnElement) {
          btnElement.disabled = false;
          btnElement.innerHTML = btnElement.dataset.originalText;
        }
      }
    } catch (error) {
      showToast("Lỗi kết nối máy chủ", "error");
      if (btnElement) {
        btnElement.disabled = false;
        btnElement.innerHTML = btnElement.dataset.originalText;
      }
    }
  }

  // Handle manual apply in modal
  const manualApplyBtn = document.getElementById('manual-apply-btn');
  const manualInput = document.getElementById('manual-coupon-code');
  if (manualApplyBtn && manualInput) {
    manualApplyBtn.addEventListener('click', function () {
      applyCouponCode(manualInput.value.trim(), manualApplyBtn);
    });

    // Allow enter key
    manualInput.addEventListener('keypress', function (e) {
      if (e.key === 'Enter') {
        applyCouponCode(manualInput.value.trim(), manualApplyBtn);
      }
    });
  }

  // Handle confirm selection in modal
  const confirmVoucherBtn = document.getElementById('confirm-voucher-btn');
  if (confirmVoucherBtn) {
    confirmVoucherBtn.addEventListener('click', function () {
      const selectedRadio = document.querySelector('input[name="selected_voucher"]:checked');
      if (selectedRadio) {
        applyCouponCode(selectedRadio.value, confirmVoucherBtn);
      } else {
        showToast("Vui lòng chọn một mã giảm giá", "error");
      }
    });
  }

  // Handle remove coupon
  const removeCouponBtn = document.getElementById('remove-coupon-btn');
  if (removeCouponBtn) {
    removeCouponBtn.addEventListener('click', async function () {
      const originalText = removeCouponBtn.innerHTML;
      removeCouponBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

      try {
        const baseUrl = window.baseUrl || (window.APP_CONFIG ? window.APP_CONFIG.baseUrl : '/');
        const response = await fetch(`${baseUrl}app/Controllers/cart/CartController.php`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'action=remove_coupon'
        });

        const data = await response.json();

        if (data.success) {
          showToast(data.message, "success");
          setTimeout(() => location.reload(), 1000);
        } else {
          showToast(data.message || 'Lỗi khi gỡ mã', "error");
          removeCouponBtn.innerHTML = originalText;
        }
      } catch (error) {
        showToast("Lỗi kết nối máy chủ", "error");
        removeCouponBtn.innerHTML = originalText;
      }
    });
  }

  // Global access
  window.showCheckoutToast = showToast;
});
