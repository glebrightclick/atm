<?php


namespace app;

/**
 * Class LargeBillsWithdrawMethod
 * @package app
 *
 * метод деления купюр "только крупные":
 * заключается в том, что мы должны получить набор купюр с максимально-возможным номиналом, например:
 * 1. 7500 рублей разбиваем на 5000, 2000 и 500 рублей
 * 2. 700 рублей разбиваем на 500 и 200 рублей
 * 3. 400 рублей в банкомате, где есть только лишь 1 купюра в 200 рублей, разбиваем на 200, 100 и 100
 */
class LargeBillsWithdrawMethod implements WithdrawMethod
{
	/**
	 * @param Atm $atm
	 * @param int $value
	 * @return Bills
	 * @throws InvalidValueToWithdrawException
	 *
	 * @see WithdrawMethod::getBills()
	 */
	public function getBills(Atm $atm, int $value): Bills
	{
		$output_bills = new Bills();
		
		// для удобства алгоритма переведем в массив
		$bills_count_by_value = [];
		
		/** @var AtmCassette $cassette */
		foreach ($atm->cassettes() as $cassette)
			$bills_count_by_value[$cassette->value()] = $cassette->billsCount();
		
		krsort($bills_count_by_value);
		
		var_dump($bills_count_by_value);
		
		// алгоритм следующий:
		// мы должны стараться идти от максимальных купюр к минимальным и остановится на первом попавшемся максимальном варианте
		// но, мы не можем брать первый максимальный номинал купюры и просто вычитать из суммы к выдаче максимальное количество купюр с этим номиналом
		// ведь в банкомате могут лежать купюры с номиналом 30 рублей (х** его знает этот ЦБ)
		// и тогда при алгоритме описанном выше число 170 никак не раскладывается, но, на самом деле оно раскладывается на 50 + 30 * 4
		if ($value_divided_by_bills = $this->divideValueByBills($value, $bills_count_by_value)) {
			// если найдены значения, то заполняем выходной массив купюрами из банкомата
			foreach ($value_divided_by_bills as $bill_value => $bill_count) {
				try {
					$temporary_bills = $atm->removeBills($bill_value, $bill_count);
					/** @var Bill $bill */
					foreach ($temporary_bills as $bill) {
						$output_bills->add($bill);
					}
				}
				catch (EmptyCollectionException $exception) {
					// если вдруг в кассете выбираемого номинала не оказалось денежных средств
					// то клиенту выдаём пустой массив
					$output_bills = new Bills();
					break;
				}
			}
		}
		// если, по какой-то причине невозможно разделить, то выкидываем исключение
		if ($output_bills->isEmpty())
			throw new InvalidValueToWithdrawException();
		
		return $output_bills;
	}
	//--------------------------------------------------
	
	/**
	 * @param int $value - значение, которое нужно разделить
	 * @param array $bills_count_by_value - массив вида [номинал купюры => количество купюр]
	 * @return array - массив вида [номинал купюры => количество купюр]
	 *
	 * алгоритм деления значения по максимальным купюрам
	 */
	private function divideValueByBills(int $value, array $bills_count_by_value) : array {
		// структура $bills_count_by_value - ключ = номинал, значение = количеству купюр
		// в цикле по этому массиву производим вычет номинала из $value пока значение номинала не будет превышать $value
		// после этого переходим на следующий номинал
		// если, в конце, $value !== 0, значит разложить не удалось - убираем одну наибольшую купюру и вызываем алгоритм еще раз
		
		// временная переменная для того, чтобы не потерять изначальное значение
		$input_value = $value;
		$value_divided_by_bills = [];
		
		foreach ($bills_count_by_value as $bill_value => $bill_count) {
			$value_divided_by_bills[$bill_value] = 0;
			
			for ($i = 0; $i < $bill_count; $i++) {
				if ($value - $bill_value >= 0) {
					//
					$value -= $bill_value;
					// увеличиваем значение исходящего массива
					$value_divided_by_bills[$bill_value]++;
					
					// если значение разложилось, то не за чем продолжать алгоритм - мы нашли самый оптимальный
					if ($value === 0) break 2;
				}
				else continue 2;
			}
		}
		
		// если значение удалось разложить, то выходим с результатом
		if ($value === 0) {
			return $value_divided_by_bills;
		}
		
		// если разложить не удалось, то убираем из пачки максимальновозможный номинал (!)
		// это значит, что, если у нас, в пачке, есть 100-рублевая купюра и 2 * 50-рублевых купюры, то, для алгоритма, будет лучше
		// если уберутся минимальные купюры, так как номинал у них одинаковый
		$bills_count_by_value = $this->removeMaximumValue($bills_count_by_value);
		
		// если после того, как максимальная купюра убрана в массиве $bills_count_by_value не осталось значений, то разложить не удалось никаким образом
		if (array_sum($bills_count_by_value) === 0) return [];
		
		// вызываем алгоритм без максимальной купюры
		return $this->divideValueByBills($input_value, $bills_count_by_value);
	}
	//--------------------------------------------------
	
	/**
	 * @param array $bills_count_by_value - массив вида [номинал купюр => количество купюр]
	 * @return array - обработанный массив
	 *
	 * алгоритм вычета номинала максимального номинала купюр из массива, начиная с минимальных номиналов
	 */
	private function removeMaximumValue(array $bills_count_by_value) : array {
		$max_denomination = null;
		// находим максимальный номинал купюр
		foreach ($bills_count_by_value as $bill_value => $bill_count) {
			if ($bill_count > 0) {
				$max_denomination = $bill_value;
				break;
			}
		}
		
		// проходимся по массиву номиналов в обратном порядке
		$bills_count_by_value_reverted = $bills_count_by_value;
		ksort($bills_count_by_value_reverted);
		
		foreach ($bills_count_by_value_reverted as $bill_value => $bill_count) {
			// раскладываем, только, если максимальный номинал делится без остатка на номинал рассматриваемой купюры
			if ($max_denomination % $bill_value === 0) {
				$division = $max_denomination / $bill_value;
				// если результат деления меньше либо равен оставшимся доступным купюрам, то вычитаем из кол-ва купюр результат этого деления
				if ($division <= $bill_count) {
					$bills_count_by_value[$bill_value] -= $max_denomination / $bill_value;
					break;
				}
			}
		}
		// гарантировано, массив $bills_count_by_value остался без значения максимальной купюры
		// даже если никакие другие купюры не делятся, избавимся от максимальной купюры
		return $bills_count_by_value;
	}
	//--------------------------------------------------
}