<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $connection = 'legacy';

    protected $table = 'departments';
    
    protected $fillable = [
        'department',
        'head_of',
        'phone_number',
        'email',
        'total_employee'
    ];
}
