@extends('layout.layout')

@section('title', 'My Documents')

@section('nav')
    @include('components.navbar')
@endsection

@section('content')
    <div class="max-w-5xl mx-auto py-8 px-4">
        <h2 class="text-2xl font-bold mb-6">My Documents</h2>

        <div id="documentsContainer" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <!-- Documents cards will be injected here -->
        </div>
    </div>
@endsection

@section('scripts')

    @vite('resources/js/home/documents.js')
@endsection
