function jump_to_bottom() {
    const chatBox = document.getElementById("chatBox");
    if (chatBox) {
        // Scroll chatBox container, not messagesBox
        chatBox.scrollTop = chatBox.scrollHeight;
    }
}

function add_scroll_event_to_container() {
    const chatBox = document.getElementById("chatBox");
    if (chatBox) {
        chatBox.addEventListener('scroll', function () {
            if (Math.ceil(chatBox.scrollTop) + chatBox.clientHeight >= chatBox.scrollHeight - 10) {
                can_jump_bottom = true;
            } else {
                can_jump_bottom = false;
            }
        });
    }
}

function open_box_chat() {
    const chatBox = document.getElementById("chatBox");
    const user_cards = document.querySelectorAll('.user-card');
    const chatHeader = document.getElementById("chatheader");

    user_cards.forEach(card => {
        card.addEventListener('click', function () {
            userId = this.getAttribute('data-user-id');
            
            // Active state cho user đang chọn
            user_cards.forEach(c => {
                c.classList.remove('bg-gray-100', 'dark:bg-gray-800/60');
                c.classList.add('hover:bg-blue-50', 'dark:hover:bg-gray-800/60');
            });
            this.classList.remove('hover:bg-blue-50', 'dark:hover:bg-gray-800/60');
            this.classList.add('bg-gray-100', 'dark:bg-gray-800/60');
            
            const username = this.getAttribute('data-username');
            const avatar = this.querySelector('.w-11').textContent;
            const fullName = this.querySelector('.font-semibold').textContent;
            
            chatHeader.innerHTML = `
                <div class="w-10 h-10 rounded-full bg-gray-600 flex items-center justify-center text-white font-semibold text-sm">
                    ${avatar}
                </div>
                <div class="flex-1">
                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">
                        ${fullName}
                    </h2>
                    <p class="text-xs text-green-600 dark:text-green-400 flex items-center gap-1">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        Đang hoạt động
                    </p>
                </div>
            `;
            
            // Reset scroll state khi chọn user mới
            can_jump_bottom = true;
            load_messages();
        });
    });
}

function on_key_press(event){
    if(event.key === 'Enter' && !event.shiftKey){
        event.preventDefault();
        send_messages();
    }
}

// Helper function to escape HTML
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Kiểm tra tin nhắn có thể xóa/sửa không (trong 10 phút)
function canEditDelete(sentAt) {
    const sentTime = new Date(sentAt);
    const now = new Date();
    const diffMinutes = (now - sentTime) / (1000 * 60);
    return diffMinutes <= 10;
}

// Xóa tin nhắn
function deleteMessage(messageId, messageElement) {
    if (!confirm('Bạn có chắc muốn xóa tin nhắn này?')) {
        return;
    }
    
    fetch("/WebMuaBanDoCu/app/Controllers/message/DeleteMessageController.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: "message_id=" + messageId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            messageElement.remove();
            showToast('Đã xóa tin nhắn', 'success');
        } else {
            showToast(data.error || 'Không thể xóa tin nhắn', 'error');
        }
    })
    .catch(err => {
        showToast('Lỗi: ' + err, 'error');
    });
}

// Sửa tin nhắn
function editMessage(messageId, currentContent, messageElement) {
    const newContent = prompt('Sửa tin nhắn:', currentContent);
    if (newContent === null || newContent.trim() === '') {
        return;
    }
    
    fetch("/WebMuaBanDoCu/app/Controllers/message/UpdateMessageController.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: "message_id=" + messageId + "&content=" + encodeURIComponent(newContent.trim())
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cập nhật nội dung tin nhắn trong UI
            const contentElement = messageElement.querySelector('.text-sm.text-white');
            if (contentElement) {
                contentElement.textContent = newContent.trim();
            }
            showToast('Đã cập nhật tin nhắn', 'success');
        } else {
            showToast(data.error || 'Không thể sửa tin nhắn', 'error');
        }
    })
    .catch(err => {
        showToast('Lỗi: ' + err, 'error');
    });
}

// Helper để hiển thị toast (nếu có hàm showToast, nếu không thì dùng alert)
function showToast(message, type) {
    if (typeof window.showToast === 'function') {
        window.showToast(message, type);
    } else {
        alert(message);
    }
}

function load_messages() {
    if (!userId) return;
    
    // Lưu trạng thái scroll trước khi load
    const chatBox = document.getElementById("chatBox");
    const wasAtBottom = chatBox && (Math.ceil(chatBox.scrollTop) + chatBox.clientHeight >= chatBox.scrollHeight - 20);
    
    fetch("/WebMuaBanDoCu/app/Controllers/message/GetMessagesController.php", {
        method: "POST",
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: "user_id=" + userId + "&role=admin"
    })
        .then(response => response.json())
        .then(data => {
            const messagesBox = document.getElementById("messagesBox");
            messagesBox.innerHTML = ''; // Clear previous messages
            
            if (data.length === 0) {
                messagesBox.innerHTML = `
                    <div class="flex items-center justify-center h-full">
                        <div class="text-center">
                            <div class="w-20 h-20 mx-auto rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mb-4">
                                <svg class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">Chưa có tin nhắn</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Hãy gửi tin nhắn đầu tiên</p>
                        </div>
                    </div>
                `;
                return;
            }
            
            data.forEach(message => {
                const messageElement = document.createElement("div");
                messageElement.setAttribute('data-message-id', message.id);
                const isAdmin = message.role === 'admin';
                const sentAt = message.sent_at || message.created_at || new Date().toISOString();
                const time = new Date(sentAt).toLocaleTimeString('vi-VN', { hour: '2-digit', minute: '2-digit' });
                const canEdit = isAdmin && canEditDelete(sentAt);
                
                if (isAdmin) {
                    // Tin nhắn của admin - bên phải, màu xanh
                    messageElement.className = 'flex items-end justify-end gap-2 group relative';
                    let actionButtons = '';
                    
                    if (canEdit) {
                        actionButtons = `
                            <div class="absolute right-0 top-0 opacity-0 group-hover:opacity-100 transition-opacity bg-white dark:bg-gray-800 rounded-lg shadow-lg border border-gray-200 dark:border-gray-700 p-1 flex gap-1 z-10">
                                <button 
                                    onclick="editMessage(${message.id}, '${escapeHtml(message.content).replace(/'/g, "\\'")}', this.closest('[data-message-id]'))"
                                    class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition-colors"
                                    title="Sửa"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button 
                                    onclick="deleteMessage(${message.id}, this.closest('[data-message-id]'))"
                                    class="p-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition-colors"
                                    title="Xóa"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        `;
                    }
                    
                    messageElement.innerHTML = `
                        <div class="flex flex-col items-end max-w-[75%] relative">
                            ${actionButtons}
                            <div class="px-4 py-2.5 rounded-2xl rounded-br-sm bg-blue-600 shadow-sm">
                                <p class="text-sm text-white leading-relaxed">${escapeHtml(message.content)}</p>
                            </div>
                            <span class="text-[10px] text-gray-400 mt-1">${time}</span>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-blue-600 flex items-center justify-center text-white font-semibold text-xs flex-shrink-0">
                            A
                        </div>
                    `;
                } else {
                    // Tin nhắn của user - bên trái, màu xám
                    messageElement.className = 'flex items-end gap-2';
                    messageElement.innerHTML = `
                        <div class="w-8 h-8 rounded-full bg-gray-500 flex items-center justify-center text-white font-semibold text-xs flex-shrink-0">
                            U
                        </div>
                        <div class="flex flex-col items-start max-w-[75%]">
                            <div class="px-4 py-2.5 rounded-2xl rounded-bl-sm bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shadow-sm">
                                <p class="text-sm text-gray-900 dark:text-gray-100 leading-relaxed">${escapeHtml(message.content)}</p>
                            </div>
                            <span class="text-[10px] text-gray-400 mt-1">${time}</span>
                        </div>
                    `;
                }
                messagesBox.appendChild(messageElement);
            });
            
            // Chỉ auto-scroll nếu user đang ở bottom hoặc đây là lần đầu load
            if (wasAtBottom || can_jump_bottom) {
                setTimeout(() => {
                    jump_to_bottom();
                }, 50);
            }
        })
        .catch(err => {
            console.error('Error loading messages:', err);
        });
}

function send_messages() {
    const input = document.getElementById("messageInput");
    const sendBtn = document.getElementById("sendButton");
    const content = input.value;
    if (!content.trim() || !userId) return;
    
    input.value = "";
    if (sendBtn) {
        sendBtn.disabled = true;
    }
    
    // Đảm bảo sẽ scroll sau khi gửi
    can_jump_bottom = true;
    
    fetch("/WebMuaBanDoCu/app/Controllers/message/SendMessageController.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: "content=" + encodeURIComponent(content) + "&role=admin" + "&box_chat_id=" + userId
    }).then(res => res.text())
        .then(data => {
            if (data === 'success') {
                load_messages(); // Refresh messages after sending - sẽ auto-scroll trong load_messages
            } else {
                alert("Error sending message: " + data);
            }
        }).catch(err => {
            alert("Error: " + err);
        }).finally(() => {
            if (sendBtn) {
                sendBtn.disabled = false;
            }
        });
}

// Enable / disable send button based on input content
document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById("messageInput");
    const sendBtn = document.getElementById("sendButton");
    if (!input || !sendBtn) return;

    const updateState = () => {
        const hasText = input.value.trim().length > 0;
        if (userId && hasText) {
            sendBtn.disabled = false;
        } else {
            sendBtn.disabled = true;
        }
    };

    input.addEventListener('input', updateState);
});

window.onload = function () {
    open_box_chat();
    add_scroll_event_to_container();
    setInterval(() => {
        if (userId) {
            load_messages();
            // Chỉ auto-scroll nếu user đang ở bottom
            if (can_jump_bottom) {
                setTimeout(() => {
                    jump_to_bottom();
                }, 100);
            }
        }
    }, 2000); // Refresh messages every 2s
};
