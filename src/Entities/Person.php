<?php

namespace joshmoody\Mock\Entities;

class Person
{
	public $guid;
	public $unique_hash;

	/**
	 * @var \joshmoody\Mock\Entities\Name
	 */
	public $name;

	public $company;

	/**
	 * @var \joshmoody\Mock\Entities\Address
	 */
	public $address;

	/**
	 * @var \joshmoody\Mock\Entities\Address
	 */
	public $address2;

	/**
	 * @var \joshmoody\Mock\Entities\Internet
	 */
	public $internet;

	/**
	 * @var \joshmoody\Mock\Entities\Phone
	 */
	public $phone;

	public $ssn;

	/**
	 * @var \joshmoody\Mock\Entities\DriverLicense
	 */
	public $dln;

	/**
	 * @var \joshmoody\Mock\Entities\CreditCard
	 */
	public $credit_card;

	/**
	 * @var \joshmoody\Mock\Entities\BankAccount
	 */
	public $bank_account;
}
