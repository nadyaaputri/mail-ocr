@extends('layout.main')

@section('content')
    <x-breadcrumb
        :values="['Pencarian']">
    </x-breadcrumb>

    <div class="card">
        <div class="card-header">
            <h5 class="card-title mb-0">
                Hasil Pencarian untuk: <span class="text-primary">"{{ $query }}"</span>
            </h5>
        </div>
        <div class="card-body">
            @if($results->isEmpty())
                <div class="text-center">
                    <i class="bx bx-search-alt bx-lg text-muted"></i>
                    <p class="mt-2">Tidak ada hasil yang ditemukan.</p>
                </div>
            @else
                <p>{{ $results->total() }} hasil ditemukan.</p>

                @foreach($results as $letter)
                    {{-- Kita bisa menggunakan kembali komponen letter-card! --}}
                    <x-letter-card :letter="$letter" />
                @endforeach

                {{-- Link untuk halaman selanjutnya --}}
                <div class="mt-4">
                    {!! $results->appends(['query' => $query])->links() !!}
                </div>

            @endif
        </div>
    </div>

@endsection
