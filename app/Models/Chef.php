<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chef extends Model
{
    use HasFactory;
    protected $table = 'CHEFDEPARTEMENT';
    public const CREATED_AT = null;
    public const UPDATED_AT = null;
    public $timestamps = false;
    protected $primaryKey = 'id_chef';
    protected $guarded = []; 
}
