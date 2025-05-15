<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneratedFile extends Model
{
    use HasFactory;

    protected $table = 'generated_files';

    protected $fillable = [
        'name',
        'file_path',
        'size',
        'status',
        'description'
    ];
}
