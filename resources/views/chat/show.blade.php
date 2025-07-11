@extends("layouts.app")
@push('styles')
    <link href="{{ asset('css/visits.css') }}" rel="stylesheet">
    <link href="{{ asset('css/chat.css') }}" rel="stylesheet">
@endpush
@section("content")
<div class="container">
        <main class="chat-container">

            <div class="sidebar">
                <div class="search-container">
                    <input type="text" class="search-input" placeholder="بحث...">
                    <i class="fas fa-search"></i>
                </div>

                <div class="sidebar-title">المحادثات</div>
                <div class="chat-list" id="chat-conversations-list">

                    <p style="text-align: center; padding: 20px;">جاري تحميل المحادثات...</p>
                </div>
            </div>

            <!-- Chat Main (المحادثة الفعلية) -->
            <div class="chat-main">
                <div class="chat-header">
                    <a href="{{ route('chat.index') }}" class="back-button"><i class="fas fa-arrow-right"></i></a>
                    <div class="chat-user">{{ $student->fullname ?? 'طالب غير معروف' }}</div>
                    <div class="chat-date">{{ \Carbon\Carbon::now()->format('Y/m/d') }}</div>
                </div>

                <div class="chat-messages" id="chat-messages">

                    <p style="text-align: center; padding: 20px;">جاري تحميل الرسائل...</p>
                </div>

                <form class="chat-input-container" id="chat-form">
                    <textarea class="chat-input" id="message-input" placeholder="اكتب رسالة ..."></textarea>
                    <button type="submit" class="send-button">&#10148;</button>
                </form>
            </div>
        </main>
    </div>
<script>



        document.addEventListener('DOMContentLoaded', function() {
            const chatConversationsList = document.getElementById('chat-conversations-list');

            f
                    chatConversationsList.innerHTML = '';
                    if (data.conversations && data.conversations.length > 0) {
                        data.conversations.forEach(conversation => {
                            const chatItem = document.createElement('a');
                            chatItem.href = `/chat/${conversation.student_id}`; // رابط لصفحة المحادثة الفردية
                            chatItem.className = 'chat-item' + ({{ $student->student_id ?? 'null' }} == conversation.student_id ? ' active' : '');
                            chatItem.innerHTML = `
                                <div class="chat-name">${conversation.student_fullname}</div>
                                <div class="chat-preview">${conversation.latest_message}</div>
                            `;
                            chatConversationsList.appendChild(chatItem);
                        });
                    } else {
                        chatConversationsList.innerHTML = '<p style="text-align: center; padding: 20px;">لا توجد محادثات نشطة.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching conversations for sidebar:', error);
                    chatConversationsList.innerHTML = '<p style="color: red; text-align: center; padding: 20px;">خطأ في تحميل المحادثات الجانبية.</p>';
                });

            fetchConversationsSidebar(); // جلب المحادثات للشريط الجانبي عند التحميل

            // وظيفة البحث في الشريط الجانبي
            const searchInput = document.querySelector('.search-input');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const chatItems = chatConversationsList.querySelectorAll('.chat-item');
                chatItems.forEach(item => {
                    const chatName = item.querySelector('.chat-name').textContent.toLowerCase();
                    if (chatName.includes(searchTerm)) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });


            // Logic for the main chat window (messages and input)
            const studentId = {{ $student->student_id ?? 'null' }};
            const chatMessagesDiv = document.getElementById('chat-messages');
            const chatForm = document.getElementById('chat-form');
            const messageInput = document.getElementById('message-input');

            if (studentId === null) {
                chatMessagesDiv.innerHTML = '<p style="text-align: center; padding: 20px;">الرجاء اختيار طالب لعرض المحادثة.</p>';
                return; // لا تكمل إذا لم يكن هناك studentId
            }

            function fetchMessages() {
                if (!JWT_TOKEN) {
                    chatMessagesDiv.innerHTML = '<p style="color: red; text-align: center;">خطأ: توكن المصادقة غير موجود.</p>';
                    console.error('JWT Token is missing. Cannot fetch messages.');
                    return;
                }

                fetch(`/api/librarian/chat/${studentId}`, {
                    headers: {
                        'Authorization': `Bearer ${JWT_TOKEN}`,
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        if (response.status === 401) {
                            chatMessagesDiv.innerHTML = '<p style="color: red; text-align: center;">غير مصرح لك بالوصول.</p>';
                        } else if (response.status === 404) {
                             chatMessagesDiv.innerHTML = '<p style="color: red; text-align: center;">الطالب غير موجود أو لا توجد محادثة.</p>';
                        }
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    chatMessagesDiv.innerHTML = '';
                    if (data.messages && data.messages.length > 0) {
                        data.messages.forEach(msg => {
                            const msgDiv = document.createElement('div');
                            // تحديد فئة الرسالة بناءً على المرسل (الأمينة هي 'admin' في CSS، الطالب هو 'user')
                            msgDiv.className = 'message ' + (msg.sender_type === 'librarian' ? 'admin' : 'user');
                            msgDiv.innerHTML = `
                                <div class="message-content">${msg.message}</div>
                                <div class="message-time">${new Date(msg.date_time).toLocaleTimeString('ar-EG', { hour: '2-digit', minute: '2-digit', hour12: true })}</div>
                            `;
                            chatMessagesDiv.appendChild(msgDiv);
                        });
                        chatMessagesDiv.scrollTop = chatMessagesDiv.scrollHeight; // التمرير لأسفل
                    } else {
                        chatMessagesDiv.innerHTML = '<p style="text-align: center; padding: 20px;">لا توجد رسائل في هذه المحادثة بعد.</p>';
                    }
                })
                .catch(error => {
                    console.error('Error fetching messages:', error);
                    chatMessagesDiv.innerHTML = '<p style="color: red; text-align: center; padding: 20px;">حدث خطأ أثناء تحميل الرسائل.</p>';
                });
            }

            // get messeges
            fetchMessages();


            setInterval(fetchMessages, 3000);

            // send
            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const message = messageInput.value.trim();
                if (!message) return;

                if (!JWT_TOKEN) {
                    alert('خطأ: توكن المصادقة غير موجود. لا يمكن إرسال الرسالة.');
                    return;
                }

                fetch('/api/librarian/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${JWT_TOKEN}`,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        student_id: studentId,
                        message: message
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.status === 'success') {
                        messageInput.value = ''; // مسح حقل الإدخال
                        fetchMessages(); // إعادة تحميل الرسائل بعد الإرسال
                    } else {
                        console.error('Error sending message:', data.message);
                        alert('فشل إرسال الرسالة: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Network error sending message:', error);
                    alert('خطأ في الشبكة. لم يتم إرسال الرسالة.');
                });
            });

    </script>
@endsection


