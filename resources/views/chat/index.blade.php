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
                    <!-- سيتم ملء هذا الجزء ديناميكيًا بواسطة JavaScript -->
                    <p style="text-align: center; padding: 20px;">جاري تحميل المحادثات...</p>
                </div>
            </div>

            <div class="chat-main">
                <!-- هذا الجزء سيعرض رسالة ترحيبية أو يطلب من الأمينة اختيار محادثة -->
                <p>الرجاء اختيار محادثة من القائمة على اليمين للبدء.</p>
                <p>مرحبًا بك، {{  Auth::user()->name  }}!</p>
            </div>
        </main>
    </div>

    <script>



        document.addEventListener('DOMContentLoaded', function() {
            const chatConversationsList = document.getElementById('chat-conversations-list');



                    if (data.conversations && data.conversations.length > 0) {
                        data.conversations.forEach(conversation => {
                            const chatItem = document.createElement('a');
                            chatItem.href = `/chat/${conversation.student_id}`; // for each student
                            chatItem.className = 'chat-item';
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
                    console.error('Error fetching conversations:', error);
                    chatConversationsList.innerHTML = '<p style="color: red; text-align: center; padding: 20px;">حدث خطأ أثناء تحميل المحادثات.</p>';
                });


            fetchConversations();


            const searchInput = document.querySelector('.search-input');
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                const chatItems = chatConversationsList.querySelectorAll('.chat-item');
                chatItems.forEach(item => {
                    const chatName = item.querySelector('.chat-name').textContent.toLowerCase();
                    if (chatName.includes(searchTerm)) {
                        item.style.display = ''; //
                    } else {
                        item.style.display = 'none'; //
                    }
                });
            });

    </script>
@endsection


