@extends('layout.layout')

@section('title', 'Register Page')

@section('content')
    <div class="min-h-screen w-full flex items-center justify-center bg-gray-100 p-4">
        <div
            class="max-w-md w-full bg-gradient-to-br from-white to-gray-50 p-8 rounded-2xl shadow-xl border border-gray-100">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">إنشاء حساب جديد</h2>

            <form id="registerForm" class="space-y-5">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">الاسم الكامل</label>
                    <input type="text" id="name" name="name"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition outline-none"
                        required placeholder="أدخل اسمك الكامل" />
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">البريد الإلكتروني</label>
                    <input type="email" id="email" name="email"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition outline-none"
                        required placeholder="أدخل بريدك الإلكتروني" />
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">كلمة المرور</label>
                    <input type="password" id="password" name="password"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition outline-none"
                        required minlength="8" placeholder="أدخل كلمة مرور قوية" />
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">تأكيد كلمة
                        المرور</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition outline-none"
                        required placeholder="أعد إدخال كلمة المرور" />
                </div>
                <button type="submit"
                    class="w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white font-semibold py-3 rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition duration-200">
                    إنشاء الحساب
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('login') }}"
                    class="inline-block text-blue-600 hover:text-blue-800 font-medium transition">
                    ← لديك حساب؟ سجّل الدخول
                </a>
            </div>

            <div id="registerMessage" class="mt-4 text-center text-red-500 text-sm min-h-[1.5rem]"></div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const API_KEY = 'sk_test_9fA3KxP2QmL7ZC8R4WbE1N6YHVDuT0J';

        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const data = {
                name: document.getElementById('name').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                password_confirmation: document.getElementById('password_confirmation').value
            };

            const messageEl = document.getElementById('registerMessage');
            messageEl.textContent = '';
            messageEl.className = "mt-4 text-center text-red-500 text-sm min-h-[1.5rem]";

            if (data.password !== data.password_confirmation) {
                messageEl.textContent = "كلمتا المرور غير متطابقتين";
                return;
            }

            try {
                const res = await fetch('/api/v1/users/register', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'x-api-key': API_KEY
                    },
                    body: JSON.stringify(data)
                });

                const json = await res.json();

                if (res.ok) {
                    localStorage.setItem('token', json.data.token);
                    messageEl.textContent = "تم إنشاء الحساب بنجاح!...";
                    messageEl.className = "mt-4 text-center text-green-500 text-sm min-h-[1.5rem]";
                    setTimeout(() => {
                        window.location.href = "{{ route('home') }}";
                    }, 1000);
                } else {
                    if (json.error_code === 'TOO_MANY_REQUESTS') {
                        messageEl.textContent = json.message || "تم تجاوز عدد المحاولات.";
                    } else if (json.errors) {
                        const firstError = Object.values(json.errors)[0]?.[0] || "حدث خطأ في التسجيل";
                        messageEl.textContent = firstError;
                    } else {
                        messageEl.textContent = json.message || "فشل إنشاء الحساب";
                    }
                }
            } catch (err) {
                console.error(err);
                messageEl.textContent = "فشل الاتصال بالخادم";
            }
        });
    </script>
@endsection
