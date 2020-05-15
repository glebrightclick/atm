<?php


namespace app;


/**
 * Class Bill
 * @package app
 *
 * купюра
 */
class Bill
{
	/** @var int номинал */
	private $value;
	//--------------------------------------------------
	
	/**
	 * Bill constructor.
	 * @param int $value
	 *
	 * указываем номинал в конструкторе
	 */
	public function __construct(int $value) {
		$this->value = $value;
	}
	//--------------------------------------------------
	
	/**
	 * @return int
	 */
	public function value(): int {
		return $this->value;
	}
	//--------------------------------------------------
}