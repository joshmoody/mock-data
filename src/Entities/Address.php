<?php

namespace joshmoody\Mock\Entities;

class Address
{
	public $line_1;
	public $line_2;
	public $city;
	public $zip;
	public $county;

	/**
	 * @var \joshmoody\Mock\Entities\State
	 */
	public $state;
}
