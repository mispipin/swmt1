<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TestRegistration extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'school',
        'class_name',
        'name',
        'birth_date',
        'address',
        'teacher_class_id',
        'orang_benar',
        'urutan_benar',
        'orang_salah',
        'urutan_salah',
        'total_poin',
        'tested_at',
        'progress_current_section',
        'progress_current_slide',
        'progress_ui_stage',
        'progress_picked_order',
        'progress_section_results',
        'progress_sections',
        'progress_updated_at',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'tested_at' => 'datetime',
            'progress_picked_order' => 'array',
            'progress_section_results' => 'array',
            'progress_sections' => 'array',
            'progress_updated_at' => 'datetime',
        ];
    }

    public function teacherClass(): BelongsTo
    {
        return $this->belongsTo(TeacherClass::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
