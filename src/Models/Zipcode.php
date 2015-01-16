<?php

namespace joshmoody\Mock\Models;

use Illuminate\Database\Eloquent\Model;

class Zipcode extends Model
{
	public $connection = 'mock-data';
	public $timestamps = false;
}
