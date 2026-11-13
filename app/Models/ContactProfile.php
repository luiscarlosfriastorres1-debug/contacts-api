<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactProfile extends Model
{
    protected $fillable = ['contact_id', 'c1', 'c2', 'c3'];

    public function contact()
    {
        return $this->belongsTo(Contact::class);
    }
}

