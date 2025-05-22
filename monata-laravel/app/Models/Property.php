<?php

namespace App\Models;

use App\Traits\AuditFields;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use AuditFields;

    protected $table = 'properties';
    protected $fillable = [
        'name'
    ];
}
