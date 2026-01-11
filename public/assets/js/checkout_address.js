/**
 * checkout_address.js
 * Xử lý fetch dữ liệu địa giới hành chính từ Provinces Open API v2 (2025)
 * Cấu trúc: Tỉnh/Thành phố -> Phường/Xã/Thị trấn (2 cấp)
 */
document.addEventListener('DOMContentLoaded', function() {
    const provinceSelect = document.getElementById('province');
    const wardSelect = document.getElementById('ward');
    const fullAddressInput = document.getElementById('txt_inv_addr1');
    const cityInput = document.getElementById('txt_bill_city');

    const API_BASE = 'https://provinces.open-api.vn/api/v2';

    // Fetch Provinces
    fetch(`${API_BASE}/p/`)
        .then(response => response.json())
        .then(data => {
            // Sắp xếp theo tên cho dễ tìm
            data.sort((a, b) => a.name.localeCompare(b.name));
            data.forEach(province => {
                const option = document.createElement('option');
                option.value = province.code;
                option.text = province.name;
                provinceSelect.add(option);
            });
        })
        .catch(err => console.error("Error fetching provinces:", err));

    // Province Change -> Fetch Wards Directly (API v2 depth=2)
    provinceSelect.addEventListener('change', function() {
        wardSelect.innerHTML = '<option value="">Chọn Phường/Xã</option>';
        wardSelect.disabled = true;

        if (this.value) {
            fetch(`${API_BASE}/p/${this.value}?depth=2`)
                .then(response => response.json())
                .then(data => {
                    if (data.wards && data.wards.length > 0) {
                        data.wards.sort((a, b) => a.name.localeCompare(b.name));
                        data.wards.forEach(ward => {
                            const option = document.createElement('option');
                            option.value = ward.code;
                            option.text = ward.name;
                            wardSelect.add(option);
                        });
                        wardSelect.disabled = false;
                    }
                    updateHiddenFields();
                })
                .catch(err => console.error("Error fetching wards:", err));
        } else {
            updateHiddenFields();
        }
    });

    wardSelect.addEventListener('change', updateHiddenFields);

    function updateHiddenFields() {
        const provinceText = provinceSelect.options[provinceSelect.selectedIndex]?.text || '';
        const wardText = wardSelect.options[wardSelect.selectedIndex]?.text || '';
        const specificAddress = document.getElementById('specific_address').value;

        // Cập nhật City cho VNPay
        cityInput.value = provinceText;

        // Cập nhật địa chỉ đầy đủ cho DB (API v2: Tỉnh + Phường)
        let addrParts = [];
        if (specificAddress) addrParts.push(specificAddress);
        if (wardText && wardText !== 'Chọn Phường/Xã') addrParts.push(wardText);
        if (provinceText && provinceText !== 'Chọn Tỉnh/Thành') addrParts.push(provinceText);
        
        fullAddressInput.value = addrParts.join(', ');
    }

    // Lắng nghe sự kiện nhập địa chỉ cụ thể
    document.getElementById('specific_address').addEventListener('input', updateHiddenFields);
});
