<div class="chat-container" id="chat-widget">
    <div class="chat-header">
        💬 Hỗ trợ trực tuyến
    </div>
    <div class="chat-body" id="chat-body">
        <div class="chat-messages-container" id="ChatMessagesContainer">
            <div class="chat-messages" id="messages">

            </div>
        </div>
        <div class="chat-input">
            <input type="text" id="chat-input" onkeydown="on_key_press(event)" placeholder="Nhập tin nhắn..." style="width: 80%;" />
            <button id="button-send-message" onclick="send_messages()"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </div>
</div>