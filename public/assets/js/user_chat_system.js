// Prevent double loading of this script
if (typeof window.chatSystemLoaded !== 'undefined' && window.chatSystemLoaded) {
    // Script already loaded, exit
} else {
    // Mark this script as loaded
    window.chatSystemLoaded = true;

    // Use global userId if available, otherwise declare locally
    if (typeof window.userId === 'undefined') {
        window.userId = null;
    }
    
    // Removed unused globals: chatVisible
    let can_jump_bottom = true;

function add_scroll_event_to_container() {
    let containerMessages = document.getElementById("ChatMessagesContainer");
    containerMessages.addEventListener('scroll', function () {
        if (Math.ceil(containerMessages.scrollTop) + containerMessages.clientHeight >= containerMessages.scrollHeight) {
            can_jump_bottom = true;
        } else {
            can_jump_bottom = false
        }
    })
}

function jump_to_bottom() {
    let containerMessages = document.getElementById("ChatMessagesContainer");
    containerMessages.scrollTop = containerMessages.scrollHeight;
}

function on_key_press(event){
    if(event.key == 'Enter'){
        send_messages();
    }
}

let isChatOpen = false;
let chatInterval = null;

function toggle_chat_widget() {
    let chatContainer = document.getElementById("chat-widget");
    let triggerBtn = document.getElementById("chat-trigger");
    
    if (!chatContainer) return;

    if (!isChatOpen) {
        // Open Chat
        chatContainer.classList.add('active'); // CSS handles display:flex
        
        if (triggerBtn) {
            triggerBtn.classList.add('hidden'); // CSS handles display:none
        }
        
        isChatOpen = true;

        // Load messages immediately
        load_messages();
        
        // Start polling
        if (chatInterval) clearInterval(chatInterval);
        chatInterval = setInterval(() => {
            load_messages();
            if (can_jump_bottom) {
                jump_to_bottom();
            }
        }, 1000);
        
        // Focus input
         setTimeout(() => {
             const input = document.getElementById("chat-input");
             if(input) input.focus();
        }, 300);
        
    } else {
        // Close Chat
        chatContainer.classList.remove('active'); // Reverts to display:none
        
        if (triggerBtn) {
            triggerBtn.classList.remove('hidden');
        }
        
        isChatOpen = false;
        
        if (chatInterval) {
            clearInterval(chatInterval);
            chatInterval = null;
        }
    }
}

function send_messages() {
    const input = document.getElementById("chat-input");

    if (input.value.length < 1) return;

    const content = input.value;
    input.value = "";
    fetch("/WebMuaBanDoCu/app/Controllers/message/SendMessageController.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: "content=" + content + "&role=user"
    }).then(res => res.text())
        .then(data => {
            if (data === 'success') {
                load_messages(); // Refresh messages after sending
                setTimeout(() => {
                    jump_to_bottom();
                }, 100); // Delay to ensure messages are loaded before scrolling
            } else {
                alert("Error sending message: " + data);
            }
        }).catch(err => {
            alert("Error: " + err);
        });
}

function load_messages() {
    fetch("/WebMuaBanDoCu/app/Controllers/message/GetMessagesController.php", {
        method: "POST",
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: "user_id=" + userId + "&role=user"
    })
        .then(response => response.json())
        .then(data => {
            const messagesBox = document.getElementById("messages");
            messagesBox.innerHTML = ''; // Clear previous messages
            data.forEach(message => {
                const messageElement = document.createElement("div");
                if (message.role === 'user') {
                    messageElement.className = 'user-message';
                } else if (message.role === 'admin') {
                    messageElement.className = 'admin-message';
                }
                messageElement.textContent = `${message.content}`;
                messagesBox.appendChild(messageElement);
            });

        });
    }

    add_scroll_event_to_container();
}