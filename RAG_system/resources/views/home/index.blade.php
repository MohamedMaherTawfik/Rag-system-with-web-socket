@extends('layout.layout')

@section('title', 'Chat with PDF')

@section('nav')
    @include('components.navbar')
@endsection

@section('content')
    <div style="max-width: 800px; margin: 20px auto; padding: 20px; font-family: Arial, sans-serif;">
        <h2 style="text-align: center; font-size: 28px; margin-bottom: 30px; color: #333;">Chat with Your PDF</h2>

        <form id="chatForm" enctype="multipart/form-data"
            style="margin: 20px 0; display: flex; flex-direction: column; gap: 15px;">
            <input type="file" name="file" accept=".pdf" required
                style="padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 16px;">
            <input type="text" name="prompt" placeholder="Ask a question about the PDF..." required
                style="padding: 12px; border: 1px solid #ccc; border-radius: 6px; font-size: 16px;">
            <button type="submit"
                style="padding: 12px 24px; background-color: #10b981; color: white; border: none; border-radius: 6px; font-size: 16px; cursor: pointer; transition: background 0.3s;"
                onmouseover="this.style.backgroundColor='#059669'" onmouseout="this.style.backgroundColor='#10b981'">
                Send & Analyze PDF
            </button>
        </form>

        <div id="messages"
            style="font-family: monospace; white-space: pre-wrap; min-height: 200px; border: 1px solid #ccc; padding: 15px;
               background: #f9f9f9; border-radius: 8px; margin-top: 20px; line-height: 1.5;">
        </div>
    </div>

@endsection

@section('scripts')
    @vite('resources/js/home/home.js')
@endsection
