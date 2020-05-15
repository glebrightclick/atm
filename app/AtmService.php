<?php


namespace app;


class AtmService
{
	public static function divide(Atm $atm, int $value, WithdrawMethod $withdraw_method) : Bills {
		// по-умолчанию ничего не выдаём
		$bills = new Bills();
		
		try {
			$bills = $withdraw_method->getBills($atm, $value);
		}
		catch (InvalidValueToWithdrawException $exception) {
			// невозможно выдать заявленную сумму
		}
		
		return $bills;
	}
	//--------------------------------------------------
}