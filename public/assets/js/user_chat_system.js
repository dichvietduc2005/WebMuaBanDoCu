let chatVisible = true;
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

function toggleChat() {
    let load_new_messages = null
    let chatContainer = document.getElementById("chat-widget");
    if (chatVisible) {
        load_new_messages = setInterval(() => {
            load_messages();
            if (can_jump_bottom) {
                jump_to_bottom();
            }

        }, 1000)
        chatContainer.style.display = 'block';
        chatVisible = false;
    } else {
        jump_to_bottom();
        clearInterval(load_new_messages);
        chatVisible = true;
        chatContainer.style.display = 'none';
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

window.onload = function () {
    add_scroll_event_to_container();
};