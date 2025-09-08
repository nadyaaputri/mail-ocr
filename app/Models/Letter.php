<?php

namespace App\Models;

use App\Enums\LetterType;
use App\Enums\Config as ConfigEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;


class Letter extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'reference_number',
        'agenda_number',
        'from',
        'to',
        'letter_date',
        'received_date',
        'description',
        'note',
        'type',
        'classification_code',
        'user_id',
        'status', // --- TAMBAHAN BARU 1 ---
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'letter_date' => 'date',
        'received_date' => 'date',
    ];

    protected $appends = [
        'formatted_letter_date',
        'formatted_received_date',
        'formatted_created_at',
        'formatted_updated_at',
    ];

    // --- BLOK YANG SUDAH DIPERBARUI ---

    // Definisikan semua kemungkinan status baru sebagai konstanta
    public const STATUS_BARU = 'Baru';
    public const STATUS_KABIRO = 'Di Ruangan Kepala Biro'; // Merah
    public const STATUS_KABAG = 'Di Ruangan Kepala Bagian'; // Kuning
    public const STATUS_SELESAI = 'Selesai'; // Hijau

    /**
     * Helper method untuk mendapatkan warna label Bootstrap 5
     * berdasarkan status surat.
     *
     * @return string
     */
    public function getStatusColorClass(): string
    {
        // Logika untuk SURAT KELUAR
        if ($this->type === 'outgoing') {
            return match ($this->status) {
                self::STATUS_SELESAI => 'success', // Hijau
                self::STATUS_KABIRO => 'warning', // Kuning
                self::STATUS_KABAG => 'danger',  // Merah
                default => 'secondary',
            };
        }

        // Logika default untuk SURAT MASUK
        return match ($this->status) {
            self::STATUS_SELESAI => 'success', // Hijau
            self::STATUS_KABAG => 'warning', // Kuning
            self::STATUS_KABIRO => 'danger',  // Merah
            default => 'secondary',
        };
    }


    public function getFormattedLetterDateAttribute(): string {
        return Carbon::parse($this->letter_date)->isoFormat('dddd, D MMMM YYYY');
    }

    public function getFormattedReceivedDateAttribute(): string {
        return Carbon::parse($this->received_date)->isoFormat('dddd, D MMMM YYYY');
    }

    public function getFormattedCreatedAtAttribute(): string {
        return $this->created_at->isoFormat('dddd, D MMMM YYYY, HH:mm:ss');
    }

    public function getFormattedUpdatedAtAttribute(): string {
        return $this->updated_at->isoFormat('dddd, D MMMM YYYY, HH:mm:ss');
    }

    // ... sisa kode Anda tetap sama ...
    public function scopeType($query, LetterType $type)
    {
        return $query->where('type', $type->type());
    }

    public function scopeIncoming($query)
    {
        return $this->scopeType($query, LetterType::INCOMING);
    }

    public function scopeOutgoing($query)
    {
        return $this->scopeType($query, LetterType::OUTGOING);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', now());
    }

    public function scopeYesterday($query)
    {
        return $query->whereDate('created_at', now()->addDays(-1));
    }

    public function scopeSearch($query, $search)
    {
        // Pastikan query hanya berjalan jika ada input pencarian
        return $query->when($search, function ($query, $find) {
            // Mengelompokkan semua 'orWhere' agar tidak mengganggu query lain
            return $query->where(function ($query) use ($find) {
                $query->where('reference_number', 'LIKE', '%' . $find . '%')
                    ->orWhere('agenda_number', 'LIKE', '%' . $find . '%')
                    ->orWhere('from', 'LIKE', '%' . $find . '%')
                    ->orWhere('to', 'LIKE', '%' . $find . '%')
                    ->orWhere('description', 'LIKE', '%' . $find . '%');
            });
        });
    }

    public function scopeRender($query, $search)
    {
        return $query
            ->with(['attachments', 'classification'])
            ->search($search)
            ->latest('letter_date')
            ->paginate(Config::getValueByCode(ConfigEnum::PAGE_SIZE))
            ->appends([
                'search' => $search,
            ]);
    }

    public function scopeAgenda($query, $since, $until, $filter)
    {
        return $query
            ->when($since && $until && $filter, function ($query, $condition) use ($since, $until, $filter) {
                return $query->whereBetween(DB::raw('DATE(' . $filter . ')'), [$since, $until]);
            });
    }

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo
     */
    public function classification(): BelongsTo
    {
        return $this->belongsTo(Classification::class, 'classification_code', 'code');
    }

    /**
     * @return HasMany
     */
    public function dispositions(): HasMany
    {
        return $this->hasMany(Disposition::class, 'letter_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'letter_id', 'id');
    }
}
