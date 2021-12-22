<?php

namespace joshmoody\Mock\Entities;

class Person
{
	public $guid;
	public $unique_hash;

	/**
	 * @var FullName
	 */
	public $name;

	public $company;

	/**
	 * @var Address
	 */
	public $address;

	/**
	 * @var Address
	 */
	public $address2;

	/**
	 * @var Internet
	 */
	public $internet;

	/**
	 * @var Phone
	 */
	public $phone;

	public $ssn;

	/**
	 * @var DriverLicense
	 */
	public $dln;

	/**
	 * @var CreditCard
	 */
	public $credit_card;

	/**
	 * @var BankAccount
	 */
	public $bank_account;

	public $dob;
}
