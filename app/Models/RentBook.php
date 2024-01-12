<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RentBook extends Model
{
    use HasFactory;

    public function Book()
    {
        return $this->belongsTo(Book::class);
    }
    public function User()
    {
        return $this->belongsTo(User::class);
    }
}
