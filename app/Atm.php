<?php


namespace app;


use InvalidArgumentException;

class Atm
{
	/** @var AtmCassettes */
	private $cassettes;
	//--------------------------------------------------
	
	/**
	 * Atm constructor.
	 * @param int[] $available_values - доступные в банкомате купюры к выдаче
	 */
	public function __construct(int ...$available_values) {
		// по-умолчанию, банкомат пустой
		$this->cassettes = new AtmCassettes();
		
		// добавляем кассеты относительно предзаданных номиналов
		foreach ($available_values as $bill_value) {
			$this->cassettes->offsetSet($bill_value, new AtmCassette($bill_value));
		}
	}
	//--------------------------------------------------
	
	/**
	 * @return AtmCassettes
	 *
	 * возвращает кассеты с купюрами
	 */
	public function cassettes() : AtmCassettes {
		return clone $this->cassettes;
	}
	//--------------------------------------------------
	
	/**
	 * @param Bills $bill_collection
	 *
	 * загружает купюры в банкомат
	 * @return Bills $rejected_bills - отклоненные купюры
	 */
	public function addBillsAndRejectIncorrect(Bills $bill_collection) : Bills {
		// создаём массив отклоненных купюр по номиналу
		$rejected_bills = new Bills();
		
		try {
			/** @var Bill $bill */
			foreach ($bill_collection as $bill) {
				if ($this->cassettes->offsetExists($bill->value()))
					$this->cassettes->offsetGet($bill->value())->addBill($bill);
				else
					$rejected_bills->add($bill);
			}
		}
		catch (InvalidArgumentException $system_exception) {
			// TODO: обработка системной ошибки добавления купюры
		}
		
		return $rejected_bills;
	}
	//--------------------------------------------------
	
	/**
	 * @param int $bill_value - номинал купюр к изъятию
	 * @param int $bills_count - количество купюр к изъятию
	 * @return Bills - коллекция купюр
	 * @throws EmptyCollectionException|InvalidArgumentException
	 */
	public function removeBills(int $bill_value, int $bills_count) : Bills {
		$issued_bills = new Bills();
		
		if (!$this->cassettes->offsetExists($bill_value))
			throw new InvalidArgumentException("В банкомате нет кассет с номиналом " . $bill_value);
		
		if ($this->cassettes->offsetGet($bill_value)->billsCount() < $bills_count)
			throw new InvalidArgumentException("В кассете с номиналом " . $bill_value . " недостаточно купюр к выдаче");
		
		for ($counter = 0; $counter < $bills_count; $counter++) {
			$issued_bills->add($this->cassettes->offsetGet($bill_value)->removeBill());
		}
		
		return $issued_bills;
	}
	//--------------------------------------------------
	
	/**
	 * @return int
	 *
	 * возвращает баланс банкомата
	 */
	public function balance() : int {
		$balance = 0;
		
		if (!$this->cassettes->isEmpty()) {
			/** @var AtmCassette $cassette */
			foreach ($this->cassettes as $cassette)
				$balance += $cassette->balance();
		}
		
		return $balance;
	}
	//--------------------------------------------------
	
	public function __clone()
	{
		// клонируем кассеты так, чтобы данные не затёрлись
		$this->cassettes = clone $this->cassettes;
	}
	//--------------------------------------------------
}