@extends('layout.layout')

@section('title', 'Login Page')

@section('content')
    <div class="min-h-screen w-full flex items-center justify-center bg-gray-100 p-4">
        <div
            class="max-w-md w-full bg-gradient-to-br from-white to-gray-50 p-8 rounded-2xl shadow-xl border border-gray-100">
            <h2 class="text-2xl font-bold text-center text-gray-800 mb-6">مرحباً بك</h2>

            <form id="loginForm" class="space-y-5">
                @csrf
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
                        required placeholder="أدخل كلمة المرور" />
                </div>
                <button type="submit"
                    class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold py-3 rounded-lg shadow-md hover:shadow-lg transform hover:-translate-y-0.5 transition duration-200">
                    تسجيل الدخول
                </button>
            </form>

            <div class="mt-6 text-center">
                <a href="{{ route('register') }}"
                    class="inline-block text-blue-600 hover:text-blue-800 font-medium transition">
                    ← إنشاء حساب جديد
                </a>
            </div>

            <div id="loginMessage" class="mt-4 text-center text-red-500 text-sm min-h-[1.5rem]"></div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        const API_KEY = "{{ env('API_KEY') }}";
        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

        document
            .getElementById("loginForm")
            .addEventListener("submit", async function(e) {
                e.preventDefault();

                const email = document.getElementById("email").value;
                const password = document.getElementById("password").value;
                const loginMessage = document.getElementById("loginMessage");

                loginMessage.innerHTML = "";
                loginMessage.classList.remove("text-red-500", "text-green-500");

                try {
                    const res = await fetch("/api/v1/users/login", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json",
                            Accept: "application/json",
                            "x-api-key": API_KEY,
                            "X-CSRF-TOKEN": CSRF_TOKEN,
                        },
                        body: JSON.stringify({
                            email,
                            password,
                        }),
                    });

                    const json = await res.json();

                    if (res.ok) {
                        localStorage.setItem("token", json.data.token);
                        localStorage.setItem("userId", json.data.user.id);
                        loginMessage.textContent = "تم تسجيل الدخول بنجاح!";
                        loginMessage.classList.add("text-green-500");
                        setTimeout(() => {
                            window.location.href = "{{ route('home') }}";
                        }, 500);
                    } else {
                        let errorMessage = "";

                        if (json.errors) {
                            const errorList = Object.values(json.errors).flat();
                            errorMessage = errorList.join("<br>");
                        } else if (json.message) {
                            errorMessage = json.message;
                        } else {
                            errorMessage = "فشل تسجيل الدخول. يرجى المحاولة لاحقًا.";
                        }

                        loginMessage.innerHTML = errorMessage;
                        loginMessage.classList.add("text-red-500");
                    }
                } catch (err) {
                    console.error(err);
                    loginMessage.textContent = "حدث خطأ في الاتصال بالخادم";
                    loginMessage.classList.add("text-red-500");
                }
            });
    </script>
@endsection
