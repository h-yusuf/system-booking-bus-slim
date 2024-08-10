<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tbusers extends Model
{
    /*disable Eloquent timestamps*/
    public $timestamps = false;

    /*database table name*/
    protected $table = 'tb_users';
    
    /*get primary key name as in DB*/
    protected $primaryKey = 'id';
    
    /*fillable fields*/
    protected $fillable = ['username','password','name','role','nik','no_hp','email','jenis_kelamin'];
    
   
}