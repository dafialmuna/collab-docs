<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $document->title }} - AryanaDocs</title>
    
    <!-- Tailwind CSS CDN (Agar tampilan langsung rapi tanpa ribet Vite) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- CSS Custom -->
    <style>
        .paper-sheet {
            max-width: 210mm;
            margin: 1rem auto 2rem;
            background: white;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1), 0 2px 4px -1px rgba(0,0,0,0.06);
            min-height: 297mm;
            padding: clamp(16px, 4vw, 20mm);
            border-radius: 2px;
        }
        .ProseMirror {
            outline: none;
            cursor: text;
            min-height: 250mm;
        }
        .ProseMirror:focus {
            outline: none;
        }
        .collaboration-cursor__caret {
            border-left: 2px solid #000;
            border-right: 2px solid #000;
            margin-left: -1px;
            margin-right: -1px;
            pointer-events: none;
            position: relative;
        }
        .collaboration-cursor__label {
            border-radius: 3px 3px 3px 0;
            color: #fff;
            font-size: 12px;
            font-weight: 600;
            padding: 0.1rem 0.3rem;
            position: absolute;
            top: -1.4em;
            user-select: none;
        }
        .toolbar-btn.active { background-color: #e5e7eb; }
        @media (max-width: 640px) {
            .paper-sheet {
                margin: 0.75rem 0.5rem 1.5rem;
                min-height: 65vh;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Top Bar -->
    <div class="sticky top-0 bg-white/95 backdrop-blur border-b px-4 py-2 flex flex-wrap items-center justify-between z-20 gap-3">
        <div class="flex items-center gap-3 min-w-0">
            <div class="flex flex-col min-w-0">
                <div class="flex items-center gap-2">
                    <h1 id="documentTitle" contenteditable="true" class="text-lg font-semibold text-gray-800 outline-none min-w-0 truncate focus:ring-0" spellcheck="false">{{ $document->title }}</h1>
                    <button id="btnSaveTitle" class="hidden px-3 py-1 rounded bg-green-600 text-white text-xs hover:bg-green-700">Simpan</button>
                </div>
                <span id="titleHint" class="text-xs text-gray-500">Klik judul untuk edit, lalu tekan Simpan.</span>
            </div>
        </div>
        <div class="flex items-center gap-2 text-sm flex-wrap justify-end">
            <span id="connectionStatus" class="px-2 py-1 rounded-full bg-gray-100 text-gray-600 text-xs">🔴 Disconnected</span>
            <span id="onlineUsers" class="px-2 py-1 rounded-full bg-gray-100 text-gray-600 text-xs">🟡 Connecting...</span>
            <a href="{{ route('dashboard') }}" 
                class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                Back to Dashboard
            </a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="text-red-500 hover:text-red-700">Logout</button>
            </form>
        </div>
    </div>

    <!-- Share Panel -->
    <div class="px-4 pt-4">
        <div class="max-w-5xl mx-auto bg-white border border-gray-200 rounded-2xl shadow-sm p-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Share this document</span>
                    <span class="text-xs px-2 py-1 rounded-full bg-blue-50 text-blue-700">ID: {{ $document->id }}</span>
                </div>
                <p class="text-sm text-gray-500 mt-1">Bagikan link ini ke teman Anda. Mereka bisa login lalu buka link yang sama untuk mengetik bareng.</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2 w-full md:w-auto">
                <input id="shareLinkInput" type="text" readonly value="{{ route('documents.show', $document->id) }}" class="w-full md:w-[360px] px-3 py-2 border rounded-lg text-sm bg-gray-50 text-gray-700">
                <button id="btnCopyShareLink" type="button" class="px-4 py-2 rounded-lg bg-gray-900 text-white text-sm hover:bg-black transition">Copy Link</button>
            </div>
        </div>
    </div>

    <!-- Toolbar -->
    <div class="sticky top-[53px] bg-white border-b px-4 py-2 flex flex-wrap gap-1 z-10">
        <button id="btnBold" class="toolbar-btn px-3 py-1 rounded font-bold hover:bg-gray-100">B</button>
        <button id="btnItalic" class="toolbar-btn px-3 py-1 rounded italic hover:bg-gray-100">I</button>
        <button id="btnUnderline" class="toolbar-btn px-3 py-1 rounded underline hover:bg-gray-100">U</button>
        <div class="w-px h-6 bg-gray-300 mx-1"></div>
        <select id="fontSize" class="px-2 py-1 border rounded text-sm">
            <option value="14px">Small</option>
            <option value="16px" selected>Normal</option>
            <option value="20px">Large</option>
            <option value="24px">Extra Large</option>
        </select>
        <div class="w-px h-6 bg-gray-300 mx-1"></div>
        <button id="btnH1" class="toolbar-btn px-2 py-1 rounded font-bold hover:bg-gray-100">H1</button>
        <button id="btnH2" class="toolbar-btn px-2 py-1 rounded font-bold hover:bg-gray-100">H2</button>
        <div class="w-px h-6 bg-gray-300 mx-1"></div>
        <button id="btnSaveVersion" class="toolbar-btn px-2 py-1 rounded font-bold hover:bg-gray-100 text-green-600">💾 Save</button>
        <button id="btnHistory" class="toolbar-btn px-2 py-1 rounded font-bold hover:bg-gray-100 text-blue-600">🕒 History</button>
        <button id="btnActivity" class="toolbar-btn px-2 py-1 rounded font-bold hover:bg-gray-100 text-purple-600">📊 Activity</button>
    </div>

    <!-- Lembar Kertas -->
    <div class="paper-sheet">
        <div id="editor" class="prose prose-lg max-w-none"></div>
    </div>
    
    <div class="text-center text-xs text-gray-400 py-4 px-4">
        <span id="saveStatus">Draft belum diubah.</span>
    </div>

    <button id="btnActivityFixed" class="fixed bottom-6 right-6 z-40 bg-purple-600 text-white rounded-full p-3 shadow-lg hover:bg-purple-700">📊</button>

    <!-- Modal History -->
    <div id="historyModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl w-96 p-6">
            <h3 class="text-lg font-bold mb-4">Version History</h3>
            <div id="historyList" class="max-h-60 overflow-y-auto space-y-2">
                <p class="text-gray-500 text-center">Loading...</p>
            </div>
            <button id="btnCloseHistory" class="mt-4 w-full bg-gray-200 hover:bg-gray-300 py-2 rounded">Tutup</button>
        </div>
    </div>

    <!-- Activity Modal -->
    <div id="activityModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
        <div class="bg-white rounded-lg shadow-xl w-96 p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold">Activity Panel</h3>
                    <p class="text-sm text-gray-500">Auto track siapa edit, berapa kali, dan kapan terakhir.</p>
                </div>
                <span id="activityStatus" class="text-xs text-gray-500">Realtime</span>
            </div>
            <div id="activityList" class="max-h-72 overflow-y-auto space-y-3 text-sm"></div>
            <div class="mt-4 flex gap-2">
                <button id="btnRefreshActivity" class="flex-1 bg-blue-600 text-white rounded py-2 hover:bg-blue-700">Refresh</button>
                <button id="btnCloseActivity" class="flex-1 bg-gray-200 hover:bg-gray-300 rounded py-2">Tutup</button>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        window.aryanaDocsEditorData = {
            documentId: {{ $document->id }},
            userName: @json($user->name),
            userId: {{ auth()->id() }}
        };
    </script>
    @vite('resources/js/editor.js')
</body>
</html>
