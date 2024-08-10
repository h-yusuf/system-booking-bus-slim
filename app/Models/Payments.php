<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payments extends Model
{
    /*disable Eloquent timestamps*/
    public $timestamps = false;

    /*database table name*/
    protected $table = 'payments';

    /*get primary key name as in DB*/
    protected $primaryKey = 'id';

    /*fillable fields*/
    protected $fillable = ['booking_id', 'amount', 'status', 'link'];


    public function bookingid()
    {
        return $this->belongsTo(Bookings::class, 'booking_id');
    }

    public function bookings()
    {
        return $this->belongsTo(Bookings::class, 'booking_id');
    }
}
