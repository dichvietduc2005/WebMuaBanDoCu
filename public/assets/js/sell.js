
// Toast helper
function showToast(type, title, message) {
    const toastContainer = document.getElementById('toast-container') || createToastContainer();
    const toastEl = document.createElement('div');
    toastEl.className = `toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0`;
    toastEl.setAttribute('role', 'alert');
    toastEl.setAttribute('aria-live', 'assertive');
    toastEl.setAttribute('aria-atomic', 'true');
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body text-white">
                <strong>${title}</strong> ${message}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    `;
    toastContainer.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', function() {
        toastEl.remove();
    });
}
function createToastContainer() {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '1090';
        document.body.appendChild(container);
    }
    return container;
}

document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('.sell-card form');
    if (!form) return;
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Gộp mô tả trước khi gửi
        var desc = document.getElementById('description').value.trim();
        var date = document.getElementById('purchase_date') ? document.getElementById('purchase_date').value : '';
        var attach = document.getElementById('attachments') ? document.getElementById('attachments').value.trim() : '';
        var fullDesc = desc;
        if (date) {
            fullDesc += "\nMua từ: " + date;
        }
        if (attach) {
            fullDesc += "\nSản phẩm đính kèm: " + attach;
        }
        document.getElementById('description').value = fullDesc;

        var formData = new FormData(form);

        fetch('../../Models/sell/SellModel.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                showToast(data.success ? 'success' : 'danger', data.success ? 'Thành công!' : 'Thất bại!', data.message);
                if (data.success) {
                    setTimeout(function() {
                        window.location.href = '/WebMuaBanDoCu/app/View/product/Product.php';
                    }, 1500);
                }
            })
            .catch(() => {
                showToast('danger', 'Lỗi!', 'Có lỗi kết nối máy chủ!');
            });
    });
});
document.getElementById('images_desc').addEventListener('change', function (e) {
    const errorDiv = document.getElementById('images_desc_error');
    if (this.files.length > 3) {
        errorDiv.textContent = 'Bạn chỉ được chọn tối đa 3 ảnh mô tả!';
        this.value = '';
    } else {
        errorDiv.textContent = '';
    }
});
// ...existing code...
document.addEventListener("DOMContentLoaded", function() {
    // Tìm tất cả các wrapper có class 'custom-select-wrapper'
    const wrappers = document.querySelectorAll(".custom-select-wrapper");

    wrappers.forEach(wrapper => {
        const selectEl = wrapper.querySelector("select");
        if (!selectEl) return;

        // Tạo div hiển thị mục đã chọn
        const selectedDiv = document.createElement("DIV");
        selectedDiv.setAttribute("class", "select-selected");
        selectedDiv.innerHTML = selectEl.options[selectEl.selectedIndex].innerHTML;
        wrapper.appendChild(selectedDiv);

        // Tạo div chứa danh sách các mục
        const itemsDiv = document.createElement("DIV");
        itemsDiv.setAttribute("class", "select-items select-hide");

        // Lặp qua các option của select gốc để tạo các div tương ứng
        for (let i = 0; i < selectEl.length; i++) {
            // Không tạo div cho option đầu tiên nếu nó là placeholder
            if (selectEl.options[i].value === "") {
                continue;
            }
            const optionDiv = document.createElement("DIV");
            optionDiv.innerHTML = selectEl.options[i].innerHTML;

            // Sự kiện click cho từng mục
            optionDiv.addEventListener("click", function(e) {
                // Cập nhật select gốc
                for (let j = 0; j < selectEl.length; j++) {
                    if (selectEl.options[j].innerHTML == this.innerHTML) {
                        selectEl.selectedIndex = j;
                        break;
                    }
                }
                // Cập nhật div hiển thị
                selectedDiv.innerHTML = this.innerHTML;
                
                // Đóng danh sách
                selectedDiv.classList.remove("select-arrow-active");
                itemsDiv.classList.add("select-hide");
            });
            itemsDiv.appendChild(optionDiv);
        }
        wrapper.appendChild(itemsDiv);

        // Sự kiện click để đóng/mở danh sách
        selectedDiv.addEventListener("click", function(e) {
            e.stopPropagation();
            closeAllSelect(this);
            this.nextSibling.classList.toggle("select-hide");
            this.classList.toggle("select-arrow-active");
        });
    });

    // Hàm đóng tất cả các danh sách select khác
    function closeAllSelect(elmnt) {
        const items = document.querySelectorAll(".select-items");
        const selected = document.querySelectorAll(".select-selected");
        
        selected.forEach((sel, i) => {
            if (elmnt !== sel) {
                sel.classList.remove("select-arrow-active");
                items[i].classList.add("select-hide");
            }
        });
    }

    // Đóng danh sách khi click ra ngoài
    document.addEventListener("click", closeAllSelect);
});