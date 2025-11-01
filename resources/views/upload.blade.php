<!DOCTYPE html>
<html>
<head>
    <title>Upload Surat untuk OCR</title>
</head>
<body>
<h2>Upload Scan Surat</h2>

@if(session('ocr_results'))
    <h3>Hasil OCR:</h3>
    <pre>
            @foreach(session('ocr_results') as $line)
            {{ $line }}<br>
        @endforeach
        </pre>
    <hr>
@endif

<form action="{{ route('surat.upload.process') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="file_surat" required>
    <button type="submit">Upload dan Proses OCR</button>
</form>
</body>
</html>
