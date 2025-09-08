<div class="card mb-4">
    <div class="card-header pb-0">
        <div class="d-flex justify-content-between flex-column flex-sm-row">
            <div class="card-title">
                <h5 class="text-nowrap mb-0 fw-bold">{{ $letter->reference_number }}</h5>
                <small class="text-black">
                    {{ $letter->type == 'incoming' ? 'Dari: ' . $letter->from : 'Untuk: ' . $letter->to }} |
                    <span class="text-secondary">{{ __('model.letter.agenda_number') }}:</span> {{ $letter->agenda_number }}
                </small>
            </div>
            <div class="card-title d-flex flex-row align-items-center">
                {{-- Indikator Status --}}
                <span class="badge rounded-pill bg-label-{{ $letter->getStatusColorClass() }} me-3">{{ $letter->status ?? 'Baru' }}</span>

                <div class="d-inline-block mx-2 text-end text-black">
                    <small class="d-block text-secondary">{{ __('model.letter.letter_date') }}</small>
                    {{ $letter->formatted_letter_date }}
                </div>

                {{-- Tombol Aksi Dropdown --}}
                <div class="dropdown d-inline-block">
                    <button class="btn p-0" type="button" id="dropdown-{{ $letter->type }}-{{ $letter->id }}"
                            data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="bx bx-dots-vertical-rounded"></i>
                    </button>
                    {{-- Dropdown untuk Surat Masuk --}}
                    @if($letter->type == 'incoming')
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdown-{{ $letter->type }}-{{ $letter->id }}">
                        <a class="dropdown-item" href="{{ route('transaction.incoming.show', $letter) }}"><i class="bx bx-show-alt me-1"></i> Lihat Detail</a>
                        <a class="dropdown-item" href="{{ route('transaction.disposition.index', $letter) }}"><i class="bx bx-transfer-alt me-1"></i> Disposisi</a>
                        <a class="dropdown-item" href="{{ route('transaction.incoming.edit', $letter) }}"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('transaction.incoming.destroy', $letter) }}" class="d-inline" method="post">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Apakah Anda yakin?')">
                                <i class="bx bx-trash-alt me-1"></i> Hapus
                            </button>
                        </form>
                    </div>
                    {{-- Dropdown untuk Surat Keluar --}}
                    @else
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdown-{{ $letter->type }}-{{ $letter->id }}">
                        <a class="dropdown-item" href="{{ route('transaction.outgoing.show', $letter) }}"><i class="bx bx-show-alt me-1"></i> Lihat Detail</a>
                        <a class="dropdown-item" href="{{ route('transaction.outgoing.edit', $letter) }}"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('transaction.outgoing.destroy', $letter) }}" class="d-inline" method="post">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="dropdown-item text-danger" onclick="return confirm('Apakah Anda yakin?')">
                                <i class="bx bx-trash-alt me-1"></i> Hapus
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="card-body">
        <hr class="mt-0">
        <p>{{ $letter->description }}</p>

        @if($letter->status !== \App\Models\Letter::STATUS_SELESAI)
        <div class="card-footer d-flex justify-content-start align-items-center border-top pt-3">
            <small class="text-muted me-3">Ubah Status:</small>

            {{-- Tombol Status untuk Surat Masuk --}}
            @if($letter->type == 'incoming')
                <form action="{{ route('transaction.incoming.update_status', $letter) }}" method="POST" class="d-inline">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ \App\Models\Letter::STATUS_KABIRO }}">
                    <button type="submit" class="btn btn-icon btn-danger btn-sm" title="Proses di Ruangan Kepala Biro">
                        <i class="bx bx-user-pin"></i>
                    </button>
                </form>
                <form action="{{ route('transaction.incoming.update_status', $letter) }}" method="POST" class="d-inline mx-1">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ \App\Models\Letter::STATUS_KABAG }}">
                    <button type="submit" class="btn btn-icon btn-warning btn-sm" title="Proses di Ruangan Kepala Bagian">
                        <i class="bx bx-user"></i>
                    </button>
                </form>
                <form action="{{ route('transaction.incoming.update_status', $letter) }}" method="POST" class="d-inline">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ \App\Models\Letter::STATUS_SELESAI }}">
                    <button type="submit" class="btn btn-icon btn-success btn-sm" title="Selesaikan Surat">
                        <i class="bx bx-check-double"></i>
                    </button>
                </form>
            {{-- Tombol Status untuk Surat Keluar --}}
            @else
                <form action="{{ route('transaction.outgoing.update_status', $letter) }}" method="POST" class="d-inline">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ \App\Models\Letter::STATUS_KABAG }}">
                    <button type="submit" class="btn btn-icon btn-danger btn-sm" title="Proses di Ruangan Kepala Bagian">
                        <i class="bx bx-user"></i>
                    </button>
                </form>
                <form action="{{ route('transaction.outgoing.update_status', $letter) }}" method="POST" class="d-inline mx-1">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ \App\Models\Letter::STATUS_KABIRO }}">
                    <button type="submit" class="btn btn-icon btn-warning btn-sm" title="Proses di Ruangan Kepala Biro">
                        <i class="bx bx-user-pin"></i>
                    </button>
                </form>
                <form action="{{ route('transaction.outgoing.update_status', $letter) }}" method="POST" class="d-inline">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ \App\Models\Letter::STATUS_SELESAI }}">
                    <button type="submit" class="btn btn-icon btn-success btn-sm" title="Selesaikan Surat">
                        <i class="bx bx-check-double"></i>
                    </button>
                </form>
            @endif
        </div>
        @endif

    </div>
</div>
