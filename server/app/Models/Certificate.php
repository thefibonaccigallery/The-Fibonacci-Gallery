<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Certificate extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'uuid',
        'certificate_number',
        'user_id',
        'course_id',
        'enrollment_id',
        'recipient_name',
        'course_title',
        'issued_at',
        'email_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id'       => 'integer',
            'course_id'     => 'integer',
            'enrollment_id' => 'integer',
            'issued_at'     => 'datetime',
            'email_sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Generate a unique certificate verification code (e.g., TFG-2026-X9K2M4P7)
     */
    public static function generateCertificateNumber(): string
    {
        $year = date('Y');
        do {
            $randomCode = strtoupper(Str::random(8));
            $number = "TFG-{$year}-{$randomCode}";
        } while (static::where('certificate_number', $number)->exists());

        return $number;
    }
}
