<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - AryanaDocs</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">
    <div class="max-w-4xl mx-auto py-10 px-4">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">My Documents</h1>
                <p class="text-gray-500 mt-1">Welcome back, {{ auth()->user()->name }}!</p>
            </div>
            <div class="flex gap-3 flex-wrap justify-end">
                <form action="{{ route('documents.join') }}" method="POST" class="bg-white p-3 rounded-xl shadow flex gap-2 items-center">
                    @csrf
                    <input
                        type="text"
                        name="document_ref"
                        value="{{ old('document_ref') }}"
                        placeholder="Tempel ID atau link dokumen"
                        class="w-64 px-3 py-2 border rounded-lg text-sm"
                    >
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm">
                        Join
                    </button>
                </form>
                <form action="{{ route('documents.store') }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg shadow flex items-center gap-2 transition">
                        <span class="text-xl">+</span> New Document
                    </button>
                </form>
            </div>
        </div>

        @if($errors->has('document_ref'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
                {{ $errors->first('document_ref') }}
            </div>
        @endif

        <div class="mb-6 bg-white rounded-xl shadow p-5">
            <h2 class="text-lg font-semibold text-gray-800">Cara sharing</h2>
            <p class="text-sm text-gray-500 mt-1">Buka dokumen, lalu salin link dari address bar. Teman yang login ke akun lain tinggal paste link itu di kolom Join di atas.</p>
        </div>

        <div class="grid gap-4">
            @forelse($documents as $doc)
                <div class="bg-white p-5 rounded-xl shadow hover:shadow-md transition flex justify-between items-center gap-4 group">
                    <a href="{{ route('documents.show', $doc->id) }}" class="flex-1 min-w-0">
                        <h3 class="font-semibold text-lg text-gray-800 group-hover:text-blue-600">{{ $doc->title }}</h3>
                        <p class="text-sm text-gray-500 mt-1">Edited {{ $doc->updated_at->diffForHumans() }}</p>
                        <p class="text-xs text-gray-400 mt-1">Document ID: {{ $doc->id }}</p>
                    </a>
                    <button
                        type="button"
                        class="copy-link-btn text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-2 rounded-lg"
                        data-link="{{ route('documents.show', $doc->id) }}"
                    >
                        Copy Link
                    </button>
                    <a href="{{ route('documents.show', $doc->id) }}" class="text-gray-400 group-hover:text-blue-500">→</a>
                </div>
            @empty
                <div class="text-center py-12 bg-white rounded-xl shadow">
                    <p class="text-gray-500 text-lg">Belum ada dokumen.</p>
                    <p class="text-gray-400 text-sm mt-2">Klik "New Document" untuk mulai menulis!</p>
                </div>
            @endforelse
        </div>
    </div>

    <script>
        document.querySelectorAll('.copy-link-btn').forEach((button) => {
            button.addEventListener('click', async () => {
                const link = button.dataset.link;
                try {
                    await navigator.clipboard.writeText(link);
                    const original = button.textContent;
                    button.textContent = 'Copied';
                    setTimeout(() => button.textContent = original, 1200);
                } catch (error) {
                    window.prompt('Copy link ini:', link);
                }
            });
        });
    </script>
</body>
</html>