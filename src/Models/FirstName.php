<?php

namespace joshmoody\Mock\Models;

use Illuminate\Database\Eloquent\Model;

class FirstName extends Model
{
	public $connection = 'mock-data';
	public $timestamps = false;
}
