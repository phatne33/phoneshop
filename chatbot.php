<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chatbot Tư Vấn Điện Thoại</title>
    <!-- CDN Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- CDN Toastify -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.css">
    <script src="https://cdn.jsdelivr.net/npm/toastify-js@1.12.0/src/toastify.min.js"></script>
    <!-- CDN jQuery và DOMPurify -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/2.4.0/purify.min.js"></script>
    <!-- CDN SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Định dạng chatbot container */
        .chatbot-container {
            font-family: Arial, sans-serif;
        }

        /* Nút toggle chat */
        #chat-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #007bff;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 28px;
            cursor: pointer;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s ease, background-color 0.3s ease;
            z-index: 1001;
        }
        #chat-toggle:hover {
            transform: scale(1.1);
            background-color: #0056b3;
        }

        /* Khung chat */
        #chat-box {
            position: fixed;
            bottom: 90px;
            right: 20px;
            width: 320px;
            max-width: 90vw;
            min-width: 250px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
            display: none;
            flex-direction: column;
            overflow: hidden;
            z-index: 1000;
            transition: opacity 0.3s ease, transform 0.3s ease;
            transform: translateY(20px);
            opacity: 0;
        }

        /* Header chat */
        #chat-header {
            background: #007bff;
            color: white;
            padding: 12px;
            font-size: 16px;
            font-weight: 500;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
        }
        #chat-header span {
            cursor: pointer;
            font-size: 18px;
            padding: 0 5px;
        }
        #chat-header span:hover {
            color: #ddd;
        }

        /* Khu vực tin nhắn */
        #chat-messages {
            height: 400px;
            max-height: 60vh;
            overflow-y: auto;
            padding: 15px;
            font-size: 14px;
            border-bottom: 1px solid #eee;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
        }
        .message {
            margin-bottom: 10px;
            padding: 8px 12px;
            border-radius: 8px;
            max-width: 95%;
            word-wrap: break-word;
        }
        .user-message {
            background-color: #007bff;
            color: white;
            margin-left: auto;
            text-align: right;
        }
        .bot-message {
            background-color: #f1f1f1;
            color: #333;
            margin-right: auto;
            text-align: left;
        }
        .typing .dots {
            display: inline-block;
            width: 30px;
            text-align: left;
        }
        .typing .dots::after {
            content: "...";
            animation: typing 1s infinite;
        }
        @keyframes typing {
            0% { content: "."; }
            33% { content: ".."; }
            66% { content: "..."; }
        }

        /* Input chat */
        #chat-input {
            width: 100%;
            padding: 10px;
            border: none;
            border-top: 1px solid #eee;
            outline: none;
            font-size: 14px;
            box-sizing: border-box;
        }
        #chat-input:focus {
            border-top: 1px solid #007bff;
        }

        /* Định dạng danh sách sản phẩm */
        .product-list {
                display: flex
;
    gap: 2px;
    justify-content: flex-start;
        }
        .product-item {
            @apply flex bg-white p-2 rounded-lg shadow-sm items-center;
        }
        .product-item img {
            @apply w-12 h-12 object-cover rounded mr-2;
        }
        .product-item div {
            @apply text-sm text-gray-800;
        }

        /* Định dạng câu tư vấn */
        .consultation-text {
            @apply text-sm font-semibold text-gray-800 mb-2;
        }
        .llm-response {
            @apply text-sm italic text-gray-600 mb-2;
        }

        /* Định dạng bảng mã giảm giá */
        .promotion-table {
            @apply w-full border-collapse mt-2 text-sm;
        }
        .promotion-table th, .promotion-table td {
            @apply border border-gray-200 p-2 text-left;
        }
        .promotion-table th {
            @apply bg-gray-100 font-bold;
        }
        .promotion-table td {
            @apply bg-white;
        }
        .copy-btn {
            @apply bg-green-500 text-white px-2 py-1 rounded cursor-pointer text-center inline-block;
        }
        .copy-btn:hover {
            @apply bg-green-600;
        }

        /* Định dạng nút lựa chọn */
        .option-list {
            @apply flex flex-wrap gap-1 p-2;
        }
        .option-item {
            @apply bg-gray-200 text-black p-2 rounded cursor-pointer text-sm w-full text-left;
        }
        .option-item:hover {
            @apply bg-gray-300;
        }

        /* Định dạng nút Flashsale */
        .more-flashsale {
            @apply bg-orange-500 text-white px-3 py-2 rounded cursor-pointer text-sm text-center inline-block mt-2;
        }
        .more-flashsale:hover {
            @apply bg-orange-600;
        }

        /* Responsive */
        @media (max-width: 768px) {
            #chat-toggle { @apply w-12 h-12 text-2xl bottom-4 right-4; }
            #chat-box { @apply bottom-16 right-4 w-[90vw]; }
            #chat-messages { @apply max-h-[50vh]; }
            .product-item img { @apply w-10 h-10; }
            .product-item div, .consultation-text, .llm-response, .promotion-table th, .promotion-table td, .option-item, .more-flashsale { @apply text-xs; }
        }
        @media (max-width: 480px) {
            #chat-toggle { @apply w-10 h-10 text-xl bottom-2 right-2; }
            #chat-box { @apply bottom-14 right-2 w-[95vw] min-w-0; }
            #chat-messages { @apply max-h-[50vh] p-2; }
            .message { @apply text-xs p-2; }
            #chat-header { @apply text-sm p-2; }
            #chat-input { @apply text-xs p-2; }
            .product-item { @apply p-2; }
            .product-item img { @apply w-8 h-8; }
            .product-item div, .consultation-text, .llm-response, .promotion-table th, .promotion-table td, .option-item, .more-flashsale { @apply text-xs; }
        }
        @tailwind base;
@tailwind components;
@tailwind utilities;

/* Định dạng chatbot container */
.chatbot-container {
    font-family: Arial, sans-serif;
}

/* Nút toggle chat */
#chat-toggle {
    @apply fixed bottom-5 right-5 bg-blue-600 text-white w-16 h-16 rounded-full flex justify-center items-center text-3xl cursor-pointer shadow-lg transition-transform duration-300 z-[1001];
}
#chat-toggle:hover {
    @apply scale-110 bg-blue-700;
}

/* Khung chat */
#chat-box {
    @apply fixed bottom-24 right-5 w-80 max-w-[90vw] min-w-[250px] bg-white rounded-xl shadow-xl flex flex-col overflow-hidden z-[1000] transition-all duration-300;
    transform: translateY(20px);
    opacity: 0;
}

/* Header chat */
#chat-header {
    @apply bg-blue-600 text-white p-3 text-base font-medium flex justify-between items-center cursor-pointer;
}
#chat-header span {
    @apply cursor-pointer text-lg p-1;
}
#chat-header span:hover {
    @apply text-gray-200;
}

/* Khu vực tin nhắn */
#chat-messages {
    @apply h-96 max-h-[60vh] overflow-y-auto p-4 text-sm border-b border-gray-200;
}
a {
    @apply text-blue-600 no-underline;
}
a:hover {
}
.message {
    @apply mb-2 p-3 rounded-lg max-w-[95%] break-words;
}
.user-message {
    @apply bg-blue-600 text-white ml-auto text-right;
}
.bot-message {
    @apply bg-gray-100 text-gray-800 mr-auto text-left;
}
.typing .dots {
    @apply inline-block w-8 text-left;
}
.typing .dots::after {
    content: "...";
    animation: typing 1s infinite;
}
@keyframes typing {
    0% { content: "."; }
    33% { content: ".."; }
    66% { content: "..."; }
}

/* Input chat */
#chat-input {
    @apply w-full p-3 border-none border-t border-gray-200 outline-none text-sm box-border;
}
#chat-input:focus {
    @apply border-t-blue-600;
}

/* Định dạng danh sách sản phẩm */
.product-list {
    @apply flex flex-wrap gap-2 p-2;
}
.product-item {
    @apply flex bg-white p-2 rounded-lg shadow-sm items-center;
}
.product-item img {
    @apply w-12 h-12 object-cover rounded mr-2;
}
.product-item div {
    @apply text-sm text-gray-800;
}

/* Định dạng câu tư vấn */
.consultation-text {
    @apply text-sm font-semibold text-gray-800 mb-2;
}
.llm-response {
    @apply text-sm italic text-gray-600 mb-2;
}

/* Định dạng bảng mã giảm giá */
.promotion-table {
    @apply w-full border-collapse mt-2 text-sm;
}
.promotion-table th, .promotion-table td {
    @apply border border-gray-200 p-2 text-left;
}
.promotion-table th {
    @apply bg-gray-100 font-bold;
}
.promotion-table td {
    @apply bg-white;
}
.copy-btn {
    @apply bg-green-500 text-white px-2 py-1 rounded cursor-pointer text-center inline-block;
}
.copy-btn:hover {
    @apply bg-green-600;
}

/* Định dạng nút lựa chọn */
.option-list {
    @apply flex flex-wrap gap-1 p-2;
}
.option-item {
    @apply bg-gray-200 text-black p-2 rounded cursor-pointer text-sm w-full text-left;
}
.option-item:hover {
    @apply bg-gray-300;
}

/* Định dạng nút Flashsale */
.more-flashsale {
    @apply bg-orange-500 text-white px-3 py-2 rounded cursor-pointer text-sm text-center inline-block mt-2;
}
.more-flashsale:hover {
    @apply bg-orange-600;
}

/* Responsive */
@media (max-width: 768px) {
    #chat-toggle { @apply w-12 h-12 text-2xl bottom-4 right-4; }
    #chat-box { @apply bottom-16 right-4 w-[90vw]; }
    #chat-messages { @apply max-h-[50vh]; }
    .product-item img { @apply w-10 h-10; }
    .product-item div, .consultation-text, .llm-response, .promotion-table th, .promotion-table td, .option-item, .more-flashsale { @apply text-xs; }
}
@media (max-width: 480px) {
    #chat-toggle { @apply w-10 h-10 text-xl bottom-2 right-2; }
    #chat-box { @apply bottom-14 right-2 w-[95vw] min-w-0; }
    #chat-messages { @apply max-h-[50vh] p-2; }
    .message { @apply text-xs p-2; }
    #chat-header { @apply text-sm p-2; }
    #chat-input { @apply text-xs p-2; }
    .product-item { @apply p-2; }
    .product-item img { @apply w-8 h-8; }
    .product-item div, .consultation-text, .llm-response, .promotion-table th, .promotion-table td, .option-item, .more-flashsale { @apply text-xs; }
}
.product-item img{
    width: 60px !important;
}
.sanpham {
    background: white;
    padding: 6px;
    display: flex
;
    width: 100%;
    color: #000000;
    text-decoration: none;
    margin: 5px;
    border-radius: 5px;
    border: 1px solid black;
}
    </style>
</head>
<body>
    <!-- HTML của chatbot -->
    <div class="chatbot-container">
        <!-- Nút bật/tắt chat -->
        <div id="chat-toggle">
            💬
        </div>

        <!-- Khung chat -->
        <div id="chat-box">
            <div id="chat-header">
                Chat hỗ trợ <span onclick="toggleChat()">✖</span>
            </div>
            <div id="chat-messages"></div>
            <input type="text" id="chat-input" placeholder="Nhập tin nhắn..." onkeypress="sendMessage(event)">
        </div>
    </div>

    <script>
    // Cấu hình DOMPurify để cho phép các thẻ và thuộc tính HTML cần thiết
    const purifyConfig = {
        ADD_TAGS: ['table', 'thead', 'tbody', 'tr', 'th', 'td'],
        ADD_ATTR: ['data-code', 'onclick']
    };

    $(document).ready(function() {
        // Gắn sự kiện click cho nút toggle chat
        $('#chat-toggle').on('click', toggleChat);

        // Xử lý sự kiện nhấp vào thẻ <a> trong khung chat
        $('#chat-messages').on('click', 'a[href*="index.php?product_id="]', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            window.location.href = url;
        });

        // Xử lý sự kiện nhấp vào nút lựa chọn
        $('#chat-messages').on('click', '.option-item', function() {
            const option = $(this).data('option');
            sendOptionMessage(option);
        });

        // Xử lý sự kiện nhấp vào nút thêm sản phẩm Flashsale
        $('#chat-messages').on('click', '.more-flashsale', function() {
            const displayedProductIds = $(this).data('exclude-ids') ? $(this).data('exclude-ids').split(',').map(Number) : [];
            sendMoreFlashSale(displayedProductIds);
        });

        // Xử lý sự kiện nhấp vào nút Copy mã giảm giá
        $('#chat-messages').on('click', '.copy-btn', function() {
            const code = $(this).data('code');
            navigator.clipboard.writeText(code).then(() => {
                Toastify({
                    text: `Đã sao chép mã ${code}`,
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#4caf50'
                }).showToast();
            }).catch(() => {
                Toastify({
                    text: 'Lỗi khi sao chép mã',
                    duration: 3000,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: '#f44336'
                }).showToast();
            });
        });
    });

    const userId = <?php echo isset($_SESSION['user_id']) ? json_encode($_SESSION['user_id']) : 'null'; ?>;

    if (!userId) {
        console.warn("Chưa đăng nhập, chatbot sẽ không lưu lịch sử theo user_id.");
    }

    function sendMessage(event) {
        if (!userId) {
            Swal.fire({
                icon: 'warning',
                title: 'Chưa đăng nhập',
                text: 'Vui lòng đăng nhập để sử dụng chatbot!',
                confirmButtonText: 'OK'
            });
            return;
        }

        if (event.key !== "Enter") return;

        let input = document.getElementById("chat-input");
        let message = input.value.trim();
        if (!message) return;
        input.value = "";

        let chatMessages = document.getElementById("chat-messages");
        chatMessages.innerHTML += `<div class="message user-message"><strong>Bạn:</strong> ${DOMPurify.sanitize(message)}</div>`;
        chatMessages.scrollTop = chatMessages.scrollHeight;

        chatMessages.innerHTML += `<div class="message bot-message typing" id="typing-indicator"><strong>Bot:</strong> <span class="dots">...</span></div>`;
        chatMessages.scrollTop = chatMessages.scrollHeight;

        fetch("ajax_handler.php", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-Requested-With": "XMLHttpRequest" },
            body: JSON.stringify({ 
                query: message,
                user_id: userId
            })
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            document.getElementById("typing-indicator").remove();
            if (data.response) {
                let modifiedResponse = data.response;
                if (data.response.includes('Danh sách sản phẩm đang Flashsale')) {
                    const productIds = extractProductIds(data.response);
                    modifiedResponse += `<div class="more-flashsale" data-exclude-ids="${productIds.join(',')}">Thêm sản phẩm Flashsale</div>`;
                }
                chatMessages.innerHTML += `<div class="message bot-message"><strong>Bot:</strong> ${DOMPurify.sanitize(modifiedResponse, purifyConfig)}</div>`;
                chatMessages.scrollTop = chatMessages.scrollHeight;
            } else {
                throw new Error("Phản hồi không chứa response");
            }
        })
        .catch(error => {
            console.error("Lỗi gửi tin nhắn:", error);
            document.getElementById("typing-indicator").remove();
            chatMessages.innerHTML += `<div class="message bot-message"><strong>Bot:</strong> Có lỗi xảy ra: ${error.message}. Vui lòng thử lại!</div>`;
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });
    }

    function sendOptionMessage(option) {
        if (!userId) {
            Swal.fire({
                icon: 'warning',
                title: 'Chưa đăng nhập',
                text: 'Vui lòng đăng nhập để sử dụng chatbot!',
                confirmButtonText: 'OK'
            });
            return;
        }

        let chatMessages = document.getElementById("chat-messages");
        chatMessages.innerHTML += `<div class="message user-message"><strong>Bạn:</strong> ${DOMPurify.sanitize(option)}</div>`;
        chatMessages.scrollTop = chatMessages.scrollHeight;

        chatMessages.innerHTML += `<div class="message bot-message typing" id="typing-indicator"><strong>Bot:</strong> <span class="dots">...</span></div>`;
        chatMessages.scrollTop = chatMessages.scrollHeight;

        fetch("ajax_handler.php", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-Requested-With": "XMLHttpRequest" },
            body: JSON.stringify({ 
                query: option,
                user_id: userId
            })
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            document.getElementById("typing-indicator").remove();
            if (data.response) {
                let modifiedResponse = data.response;
                if (data.response.includes('Danh sách sản phẩm đang Flashsale')) {
                    const productIds = extractProductIds(data.response);
                    modifiedResponse += `<div class="more-flashsale" data-exclude-ids="${productIds.join(',')}">Thêm sản phẩm Flashsale</div>`;
                }
                chatMessages.innerHTML += `<div class="message bot-message"><strong>Bot:</strong> ${DOMPurify.sanitize(modifiedResponse, purifyConfig)}</div>`;
                chatMessages.scrollTop = chatMessages.scrollHeight;
            } else {
                throw new Error("Phản hồi không chứa response");
            }
        })
        .catch(error => {
            console.error("Lỗi gửi tin nhắn:", error);
            document.getElementById("typing-indicator").remove();
            chatMessages.innerHTML += `<div class="message bot-message"><strong>Bot:</strong> Có lỗi xảy ra: ${error.message}. Vui lòng thử lại!</div>`;
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });
    }

    function sendMoreFlashSale(excludeIds) {
        if (!userId) {
            Swal.fire({
                icon: 'warning',
                title: 'Chưa đăng nhập',
                text: 'Vui lòng đăng nhập để sử dụng chatbot!',
                confirmButtonText: 'OK'
            });
            return;
        }

        let chatMessages = document.getElementById("chat-messages");
        chatMessages.innerHTML += `<div class="message user-message"><strong>Bạn:</strong> Thêm sản phẩm Flashsale</div>`;
        chatMessages.scrollTop = chatMessages.scrollHeight;

        chatMessages.innerHTML += `<div class="message bot-message typing" id="typing-indicator"><strong>Bot:</strong> <span class="dots">...</span></div>`;
        chatMessages.scrollTop = chatMessages.scrollHeight;

        fetch("ajax_handler.php", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-Requested-With": "XMLHttpRequest" },
            body: JSON.stringify({ 
                query: "flashsale",
                user_id: userId,
                exclude_ids: excludeIds
            })
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            document.getElementById("typing-indicator").remove();
            if (data.response) {
                let modifiedResponse = data.response;
                if (data.response.includes('Danh sách sản phẩm đang Flashsale')) {
                    const productIds = extractProductIds(data.response);
                    modifiedResponse += `<div class="more-flashsale" data-exclude-ids="${[...excludeIds, ...productIds].join(',')}">Thêm sản phẩm Flashsale</div>`;
                }
                chatMessages.innerHTML += `<div class="message bot-message"><strong>Bot:</strong> ${DOMPurify.sanitize(modifiedResponse, purifyConfig)}</div>`;
                chatMessages.scrollTop = chatMessages.scrollHeight;
            } else {
                throw new Error("Phản hồi không chứa response");
            }
        })
        .catch(error => {
            console.error("Lỗi gửi tin nhắn:", error);
            document.getElementById("typing-indicator").remove();
            chatMessages.innerHTML += `<div class="message bot-message"><strong>Bot:</strong> Có lỗi xảy ra: ${error.message}. Vui lòng thử lại!</div>`;
            chatMessages.scrollTop = chatMessages.scrollHeight;
        });
    }

    function extractProductIds(html) {
        const regex = /index\.php\?product_id=(\d+)/g;
        const productIds = [];
        let match;
        while ((match = regex.exec(html)) !== null) {
            productIds.push(parseInt(match[1]));
        }
        return productIds;
    }

    function loadChatHistory() {
        if (!userId) return;

        fetch("ajax_handler.php", {
            method: "POST",
            headers: { "Content-Type": "application/json", "X-Requested-With": "XMLHttpRequest" },
            body: JSON.stringify({ 
                action: "load_history",
                user_id: userId
            })
        })
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            let chatMessages = document.getElementById("chat-messages");
            if (data.history && Array.isArray(data.history)) {
                data.history.forEach(chat => {
                    chatMessages.innerHTML += `<div class="message user-message"><strong>Bạn:</strong> ${DOMPurify.sanitize(chat.user_message)}</div>`;
                    chatMessages.innerHTML += `<div class="message bot-message"><strong>Bot:</strong> ${DOMPurify.sanitize(chat.bot_reply, purifyConfig)}</div>`;
                });
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }
        })
        .catch(error => {
            console.error("Lỗi tải lịch sử tin nhắn:", error);
        });
    }

    function toggleChat() {
        let chatBox = document.getElementById("chat-box");
        let chatMessages = document.getElementById("chat-messages");
        if (chatBox.style.display === "none" || chatBox.style.display === "") {
            chatBox.style.display = "flex";
            chatBox.style.opacity = "0";
            setTimeout(() => {
                chatBox.style.opacity = "1";
                chatBox.style.transform = "translateY(0)";
                chatMessages.innerHTML = "";
                loadChatHistory();
                chatMessages.innerHTML += `<div class="message bot-message"><strong>Bot:</strong> Chào bạn! Mình là bot hỗ trợ mua sắm. Dưới đây là vài gợi ý sản phẩm nổi bật và các lựa chọn hỗ trợ.<br><div class="option-list"><div class="option-item" data-option="Cách đặt hàng">Cách đặt hàng ></div><div class="option-item" data-option="Mã giảm giá">Mã giảm giá ></div><div class="option-item" data-option="Liên hệ">Liên hệ (Zalo 0835512896) ></div><div class="option-item" data-option="Flashsale">Flashsale ></div></div></div>`;
                chatMessages.scrollTop = chatMessages.scrollHeight;
            }, 10);
        } else {
            chatBox.style.opacity = "0";
            chatBox.style.transform = "translateY(20px)";
            setTimeout(() => {
                chatBox.style.display = "none";
                chatMessages.innerHTML = "";
            }, 300);
        }
    }
    </script>
</body>
</html>