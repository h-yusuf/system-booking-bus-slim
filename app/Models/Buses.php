<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Buses extends Model
{
    /*disable Eloquent timestamps*/
    public $timestamps = false;

    /*database table name*/
    protected $table = 'buses';

    /*get primary key name as in DB*/
    protected $primaryKey = 'id';

    /*fillable fields*/
    protected $fillable = ['kode_bus', 'plat_bus', 'jumlah_kursi', 'seat_booked'];
}
