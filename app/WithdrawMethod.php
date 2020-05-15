<?php


namespace app;

/**
 * Interface WithdrawMethod
 * @package app
 *
 * интерфейс метода деления денежных средств
 */
interface WithdrawMethod
{
	/**
	 * @param Atm $atm - банкомат
	 * @param int $value - сумма к изъятию
	 * @return Bills - коллекция купюр к изъятию
	 * @throws InvalidValueToWithdrawException - ошибка невозможности разделить
	 *
	 * метод получения денежных средств из банкомата
	 */
	public function getBills(Atm $atm, int $value) : Bills;
}