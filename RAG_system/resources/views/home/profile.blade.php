@extends('layout.layout')
@section('title', 'Profile Page')
@section('nav')
    @include('components.navbar')
@endsection
@section('content')
    <div
        class="max-w-2xl mx-auto p-6 bg-gradient-to-br from-gray-50 to-blue-50/30 rounded-2xl shadow-xl border border-gray-100">
        <h2 class="text-3xl font-bold mb-8 text-gray-800 text-center pb-4 border-b border-gray-200">ملف التعريف</h2>

        <div id="profileInfo"
            class="space-y-6 bg-white/90 backdrop-blur-sm p-8 rounded-xl shadow-lg border border-gray-200 transition-all duration-300 hover:shadow-xl">
            <div class="flex items-center justify-center py-4">
                <div class="animate-pulse flex space-x-4">
                    <div class="rounded-full bg-gradient-to-r from-blue-200 to-blue-100 h-12 w-12"></div>
                    <div class="flex-1 space-y-3">
                        <div class="h-4 bg-gradient-to-r from-blue-100 to-blue-50 rounded w-3/4 mx-auto"></div>
                        <div class="h-3 bg-gradient-to-r from-blue-50 to-gray-100 rounded w-1/2 mx-auto"></div>
                    </div>
                </div>
            </div>
            <p class="text-gray-600 text-center italic">جارٍ تحميل بيانات الملف...</p>
        </div>

        <form id="updateProfileForm" class="mt-10 space-y-6 bg-white/80 p-8 rounded-xl shadow-lg border border-gray-200">
            <h3 class="text-2xl font-semibold text-gray-800 pb-3 border-b border-gray-100 flex items-center gap-3">
                <span class="p-2 bg-blue-100 rounded-lg">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M5 16h.01">
                        </path>
                    </svg>
                </span>
                إعدادات الذاكرة المؤقتة
            </h3>

            <div
                class="flex items-center justify-between p-5 bg-gradient-to-r from-gray-50 to-blue-50/50 rounded-xl border border-gray-200 hover:border-blue-300 transition-all duration-300 group">
                <div class="flex items-center gap-4">
                    <div
                        class="p-3 bg-gradient-to-br from-blue-100 to-white rounded-lg shadow-sm group-hover:shadow-md transition-shadow">
                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M3 5a2 2 0 012-2h10a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V5zm11 1H6v8l4-2 4 2V6z"
                                clip-rule="evenodd"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg font-medium text-gray-800">تفعيل الذاكرة المؤقتة</p>
                        <p class="text-sm text-gray-600 mt-1">تخزين المحادثات والتفضيلات لتحسين تجربتك</p>
                    </div>
                </div>

                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" id="memory_enabled" name="memory_enabled" class="sr-only peer">
                    <div
                        class="w-14 h-7 bg-gradient-to-r from-gray-300 to-gray-200 peer-focus:outline-none rounded-full peer peer-checked:bg-gradient-to-r peer-checked:from-blue-500 peer-checked:to-blue-600 shadow-inner transition-all duration-500">
                        <div
                            class="absolute top-0.5 left-0.5 bg-white border border-gray-300 rounded-full h-6 w-6 transition-all duration-500 peer-checked:translate-x-7 peer-checked:border-blue-500 shadow-md">
                        </div>
                    </div>
                </label>
            </div>

            <button type="submit"
                class="w-full py-3.5 px-6 bg-gradient-to-r from-blue-600 to-blue-500 text-white font-semibold rounded-xl shadow-lg hover:shadow-xl hover:from-blue-700 hover:to-blue-600 active:scale-[0.98] transition-all duration-300 transform flex items-center justify-center gap-3 group">
                <svg class="w-5 h-5 transition-transform group-hover:scale-110" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
                حفظ التغييرات
            </button>
        </form>

        <div id="updateMessage"
            class="mt-6 p-4 rounded-xl border-l-4 border-blue-500 bg-gradient-to-r from-blue-50 to-white shadow-sm transition-all duration-500 opacity-0 transform -translate-y-2 min-h-[3rem]">
        </div>
    </div>
@endsection

@section('scripts')
    @vite('resources/js/home/profile.js')
@endsection
