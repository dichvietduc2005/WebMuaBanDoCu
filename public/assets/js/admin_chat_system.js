function jump_to_bottom() {
    let containerMessages = document.getElementById("containerMessages");
    containerMessages.scrollTop = containerMessages.scrollHeight;
}

function add_scroll_event_to_container() {
    let containerMessages = document.getElementById("containerMessages");
    containerMessages.addEventListener('scroll', function () {
        if (Math.ceil(containerMessages.scrollTop) + containerMessages.clientHeight >= containerMessages.scrollHeight) {
            can_jump_bottom = true;
        } else {
            can_jump_bottom = false
        }
    })
}

function open_box_chat() {
    const chatBox = document.getElementById("chatBox");
    const user_rows = document.querySelectorAll('.user-row');

    user_rows.forEach(row => {
        row.addEventListener('click', function () {
            userId = this.getAttribute('data-user-id');
            chatBox.style.display = 'flex';
            load_messages();
            setTimeout(() => {
                jump_to_bottom();
            }, 100);
        });
    });

}

function load_messages() {
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
            data.forEach(message => {
                const messageElement = document.createElement("div");
                if (message.role === 'user') {
                    messageElement.className = 'user';
                } else if (message.role === 'admin') {
                    messageElement.className = 'admin';
                }
                messageElement.textContent = `${message.content}`;
                messagesBox.appendChild(messageElement);
            });

        });
}

function send_messages() {
    const input = document.getElementById("messageInput");
    const content = input.value;
    input.value = "";
    fetch("/WebMuaBanDoCu/app/Controllers/message/SendMessageController.php", {
        method: "POST",
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: "content=" + content + "&role=admin" + "&box_chat_id=" + userId
    }).then(res => res.text())
        .then(data => {
            if (data === 'success') {
                load_messages(); // Refresh messages after sending
                setTimeout(() => {
                    jump_to_bottom();
                }, 100);
            } else {
                alert("Error sending message: " + data);
            }
        }).catch(err => {
            alert("Error: " + err);
        });
}

window.onload = function () {
    open_box_chat();
    add_scroll_event_to_container()
    setInterval(() => {
        load_messages();
        if (can_jump_bottom) {
            jump_to_bottom();
        }
    }, 1000); // Refresh messages every 500ms
};