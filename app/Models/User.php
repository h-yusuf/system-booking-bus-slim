<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * User
 */
class User extends Model
{
	protected $table = 'users';

	public $timestamps = false;

	protected $fillable = [
		'email',
		'name',
		'username',
		'password',
		'registered',
		'roles_mask',
		// 'status',
		'nik',
		'no_hp',
		'jenis_kelamin',
	];
}
