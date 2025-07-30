<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Draft PDF Tersimpan</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f4f7f6; margin: 0; padding: 2rem; }
        .container { max-width: 900px; margin: auto; background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; }
        .date-group { border: 1px solid #dfe6e9; border-radius: 8px; margin-top: 1.5rem; overflow: hidden; }
        .date-group h2 { background-color: #3498db; color: white; padding: 1rem; margin: 0; font-size: 1.2rem; }
        .date-group ul { list-style-type: none; padding: 0; margin: 0; }
        .date-group li { padding: 1rem; border-bottom: 1px solid #ecf0f1; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .date-group li:last-child { border-bottom: none; }
        .file-name { font-weight: 500; color: #2c3e50; }
        .back-link { display: inline-block; margin-top: 1.5rem; background-color: #34495e; color: white; padding: 0.75rem 1.5rem; border-radius: 4px; text-decoration: none; transition: background-color 0.3s; }
        .back-link:hover { background-color: #2c3e50; }
        .alert-success { padding: 1rem; margin-bottom: 1rem; background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; border-radius: 4px; }
        .action-buttons { display: flex; gap: 0.5rem; }
        .action-buttons a { padding: 0.4rem 0.8rem; text-decoration: none; color: white; border-radius: 4px; font-size: 0.85em; text-align: center; transition: opacity 0.3s; }
        .action-buttons a:hover { opacity: 0.8; }
        .btn-preview { background-color: #3498db; }
        .btn-download { background-color: #27ae60; }
        .btn-delete { background-color: #e74c3c; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Riwayat Unduh PDF</h1>
        <hr>

        @if(session('success'))
            <div class="alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if($draftsByDate->isEmpty())
            <p>Belum ada draft PDF yang tersimpan.</p>
        @else
            @foreach($draftsByDate as $date => $drafts)
                <div class="date-group">
                    <h2>{{ $date }}</h2>
                    <ul>
                        @foreach($drafts as $draft)
                            <li>
                                <span class="file-name">{{ $draft->nama_file }}</span>
                                <div class="action-buttons">
                                    {{-- Link Preview tetap sama --}}
                                    <a href="{{ route('invoice.output', ['invoice' => $draft->invoice_id, 'action' => 'preview']) }}" target="_blank" class="btn-preview">Preview</a>
                                    
                                    {{-- MODIFIKASI: Link Download diubah ke rute baru --}}
                                    <a href="{{ route('invoices.drafts.redownload', $draft->invoice_id) }}" class="btn-download">Download</a>
                                    
                                    {{-- Link Delete tetap sama --}}
                                    <a href="{{ route('invoices.drafts.delete', $draft->id) }}" onclick="return confirm('Anda yakin ingin menghapus draft ini?')" class="btn-delete">Delete</a>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        @endif

        <a href="{{ route('invoice.create') }}" class="back-link">Kembali ke Buat Invoice</a>
    </div>
</body>
</html>
