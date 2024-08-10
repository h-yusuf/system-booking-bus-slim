<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bookings extends Model
{
    /*disable Eloquent timestamps*/
    public $timestamps = false;

    /*database table name*/
    protected $table = 'bookings';

    /*get primary key name as in DB*/
    protected $primaryKey = 'id';

    /*fillable fields*/
    protected $fillable = ['user_id', 'bus_id', 'seat_id', 'from_location', 'to_location', 'date', 'time_start', 'time_end', 'status'];


    public function userid()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function tbusers()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function busid()
    {
        return $this->belongsTo(Buses::class, 'bus_id');
    }

    public function buses()
    {
        return $this->belongsTo(Buses::class, 'bus_id');
    }

    public function payments()
    {
        return $this->hasOne(Payments::class, 'booking_id');
    }
}
