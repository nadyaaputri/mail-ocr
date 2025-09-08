@extends('layout.main')

@section('content')
    <x-breadcrumb
        :values="[__('menu.transaction.menu'), __('menu.transaction.incoming_letter')]">
        <a href="{{ route('transaction.incoming.create') }}" class="btn btn-primary">{{ __('menu.general.create') }}</a>
    </x-breadcrumb>

    {{-- === FORMULIR PENCARIAN DITAMBAHKAN DI SINI === --}}
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('transaction.incoming.index') }}">
                <div class="input-group">
                    <input
                        type="text"
                        name="search"
                        class="form-control"
                        placeholder="Cari berdasarkan no. referensi, no. agenda, atau pengirim..."
                        value="{{ $search ?? '' }}"
                    >
                    <button class="btn btn-primary" type="submit">
                        <i class="bx bx-search"></i> Cari
                    </button>
                </div>
            </form>
        </div>
    </div>
    {{-- === AKHIR DARI FORMULIR PENCARIAN === --}}

    @foreach($data as $letter)
        <x-letter-card
            :letter="$letter"
        />
    @endforeach

    {!! $data->appends(['search' => $search])->links() !!}
@endsection

