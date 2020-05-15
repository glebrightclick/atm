<?php


namespace app;

use InvalidArgumentException;

/**
 * Class AtmCassette
 * @package app
 *
 * внутри банкомата купюры разделены на секции (кассеты) для хранения (разный размер / другие характеристики)
 * каждая кассета - это несколько (либо 0) купюр
 */
class AtmCassette
{
	/** @var Bills */
	private $bills;
	/** @var int - номинал кассеты - купюры только такого номинала могут храниться в кассете */
	private $value;
	
	public function __construct(int $value) {
		$this->value = $value;
		$this->bills = new Bills();
	}
	//--------------------------------------------------
	
	/**
	 * @return int
	 *
	 * возвращает номинал кассеты
	 */
	public function value(): int
	{
		return $this->value;
	}
	//--------------------------------------------------
	
	/**
	 * @return int
	 *
	 * возвращает количество купюр внутри
	 */
	public function billsCount() : int {
		return $this->bills->count();
	}
	//--------------------------------------------------
	
	/**
	 * @param Bill $bill - купюра, добавляемая в кассету
	 *
	 * @return bool
	 * @throws InvalidArgumentException
	 *
	 * добавляемая купюра в кассету
	 */
	public function addBill(Bill $bill) : bool {
		if ($this->value() !== $bill->value()) {
			throw new InvalidArgumentException("Запрещено добавлять купюру номиналом " . $bill->value() . " в кассету с установленным значением " . $this->value());
		}
		
		if ($this->bills->add($bill)) return true;
		else return false;
	}
	//--------------------------------------------------
	
	/**
	 * @return Bill
	 * @throws EmptyCollectionException
	 *
	 * убираем одну купюр из кассеты и возвращаем её для дальнейших махинаций (манипуляций)
	 */
	public function removeBill() : Bill {
		if ($this->bills->isEmpty()) {
			throw new EmptyCollectionException("В кассете с номиналом " . $this->value() . " нет купюр к выдаче");
		}
		
		/** @var Bill $bill */
		foreach ($this->bills as $bill)
			if ($this->bills->remove($bill)) return $bill;
	}
	//--------------------------------------------------
	
	/**
	 * @return int
	 *
	 * возвращает баланс кассеты
	 */
	public function balance() : int {
		$balance = 0;
		
		if (!$this->bills->isEmpty())
			$balance = $this->bills->count() * $this->value();
		
		return $balance;
	}
	//--------------------------------------------------
	
	/**
	 * прототип объекта должен клонировать композицию внутренних объектов
	 */
	public function __clone() {
		$this->bills = clone $this->bills;
		$this->value = $this->value;
	}
	//--------------------------------------------------
}