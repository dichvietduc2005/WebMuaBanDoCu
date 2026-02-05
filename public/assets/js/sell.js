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
    toastEl.addEventListener('hidden.bs.toast', function () {
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

// Quản lý file ảnh mô tả
let selectedDescFiles = [];

const imagesDescInput = document.getElementById('images_desc');
const imagesDescPreview = document.getElementById('images_desc_preview');
const imagesDescError = document.getElementById('images_desc_error');

if (imagesDescInput) {
    imagesDescInput.addEventListener('change', function (e) {
        const files = Array.from(e.target.files);
        // Cộng dồn file, loại trùng theo tên + size
        files.forEach(file => {
            if (
                selectedDescFiles.length < 3 &&
                !selectedDescFiles.some(f => f.name === file.name && f.size === file.size)
            ) {
                selectedDescFiles.push(file);
            }
        });
        if (selectedDescFiles.length > 3) {
            imagesDescError.textContent = 'Bạn chỉ được chọn tối đa 3 ảnh mô tả!';
            selectedDescFiles = selectedDescFiles.slice(0, 3);
        } else {
            imagesDescError.textContent = '';
        }
        renderImagesDescPreview();
        // Reset input để lần sau chọn lại file cũ vẫn nhận được
        e.target.value = '';
    });
}

function renderImagesDescPreview() {
    if (!imagesDescPreview) return;
    imagesDescPreview.innerHTML = '';
    selectedDescFiles.forEach((file, idx) => {
        const reader = new FileReader();
        reader.onload = function (e) {
            const wrap = document.createElement('div');
            wrap.style.position = 'relative';
            wrap.style.display = 'inline-block';
            wrap.style.marginRight = '10px';
            wrap.style.marginBottom = '10px';
            const img = document.createElement('img');
            img.src = e.target.result;
            img.style.width = '70px';
            img.style.height = '70px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '8px';
            img.style.border = '1px solid #ddd';
            // Nút xóa
            const del = document.createElement('button');
            del.innerHTML = '&times;';
            del.type = 'button';
            del.style.position = 'absolute';
            del.style.top = '0';
            del.style.right = '0';
            del.style.background = 'rgba(0,0,0,0.6)';
            del.style.color = '#fff';
            del.style.border = 'none';
            del.style.borderRadius = '50%';
            del.style.width = '22px';
            del.style.height = '22px';
            del.style.cursor = 'pointer';
            del.onclick = function () {
                selectedDescFiles.splice(idx, 1);
                renderImagesDescPreview();
            };
            wrap.appendChild(img);
            wrap.appendChild(del);
            imagesDescPreview.appendChild(wrap);
        };
        reader.readAsDataURL(file);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    var form = document.querySelector('.sell-card form');
    if (!form) return;

    // Auto-Format Price Input
    var priceInput = document.getElementById('price');
    if (priceInput) {
        priceInput.addEventListener('input', function (e) {
            // Remove non-digit chars
            let value = this.value.replace(/\D/g, '');
            // Format with dots
            if (value) {
                value = value.replace(/\B(?=(\d{3})+(?!\d))/g, ".");
            }
            this.value = value;
        });
    }

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

        // Clean Price before sending
        let rawPrice = formData.get('price').toString().replace(/\./g, '');
        formData.set('price', rawPrice);

        // Xóa các file ảnh mô tả cũ (nếu có)
        formData.delete('images[]');
        // Thêm lại đúng các file đã chọn
        selectedDescFiles.forEach(file => {
            formData.append('images[]', file);
        });

        fetch((window.baseUrl || '') + 'app/Models/sell/SellModel.php', {
            method: 'POST',
            body: formData
        })
            .then(res => res.json())
            .then(data => {
                showToast(data.success ? 'success' : 'danger', data.success ? 'Thành công!' : 'Thất bại!', data.message);
                if (data.success) {
                    setTimeout(function () {
                        window.location.href = (window.baseUrl || '') + 'app/View/product/Product.php';
                    }, 1500);
                }
            })
            .catch(() => {
                showToast('danger', 'Lỗi!', 'Có lỗi kết nối máy chủ!');
            });
    });
});

document.addEventListener("DOMContentLoaded", function () {
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
            optionDiv.addEventListener("click", function (e) {
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
        selectedDiv.addEventListener("click", function (e) {
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