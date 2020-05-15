<?php


namespace app;

/**
 * Class Bills
 * @package app
 *
 * структура для хранения нескольких купюр
 */
class Bills extends Collection
{
	protected $type = Bill::class;
	//--------------------------------------------------
	
	public function __clone() {
		if (!$this->isEmpty()) {
			$temporary_bills = new Bills();
			
			/** @var Bill $bill */
			foreach ($this as $bill) {
				$temporary_bills->add(clone $bill);
			}
			
			$this->collection = [];
			
			/** @var Bill $bill */
			foreach ($temporary_bills as $bill) {
				$this->add(clone $bill);
			}
		}
	}
	//--------------------------------------------------
}