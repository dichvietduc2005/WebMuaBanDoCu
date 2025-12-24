<!-- Chat Trigger Button -->
<button class="chat-trigger-btn" id="chat-trigger" onclick="toggle_chat_widget()">
    <i class="fa-solid fa-comment-dots"></i>
</button>

<div class="chat-container" id="chat-widget">
    <div class="chat-header">
        <div class="chat-title">
            <span class="status-dot"></span> 💬 Hỗ trợ trực tuyến
        </div>
        <button id="close-chat-btn" class="close-btn" onclick="toggle_chat_widget()"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <div class="chat-body" id="chat-body">
        <div class="chat-messages-container" id="ChatMessagesContainer">
            <div class="chat-messages" id="messages">
                <!-- Messages will be loaded here -->
            </div>
        </div>
        <div class="chat-input-area">
            <div class="input-wrapper">
                <input type="text" id="chat-input" onkeydown="on_key_press(event)" placeholder="Nhập tin nhắn..." />
                <button id="button-send-message" onclick="send_messages()"><i class="fa-solid fa-paper-plane"></i></button>
            </div>
        </div>
    </div>
</div>