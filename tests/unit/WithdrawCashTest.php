<?php

use app\Atm;
use app\AtmService;
use app\Bill;
use app\Bills;
use app\LargeBillsWithdrawMethod;

class WithdrawCashTest extends \Codeception\Test\Unit
{
	/**
	* @var \UnitTester
	*/
	protected $tester;

	// tests
	public function testRealRublesVariations() {
		$atm = new Atm(50, 100, 200, 500, 1000, 2000, 5000);
		
		$bills = new Bills();
		// добавляем разное количество разных купюр
		$this->addBillsToPile($bills, 50, 15);
		$this->addBillsToPile($bills, 100, 10);
		$this->addBillsToPile($bills, 200, 15);
		$this->addBillsToPile($bills, 500, 20);
		$this->addBillsToPile($bills, 1000, 10);
		$this->addBillsToPile($bills, 2000, 20);
		$this->addBillsToPile($bills, 5000, 5);
		$rejected_bills = $atm->addBillsAndRejectIncorrect($bills);
		
		$this->tester->assertTrue($rejected_bills->isEmpty());
		
		$this->tester->assertEquals($atm->balance(), 89750);
		
		// пробуем разложить суммы
		$large_bills_withdraw_method = new LargeBillsWithdrawMethod();
		
		// делимые вариации
		$divided_value_bills = AtmService::divide(clone $atm, 30500, $large_bills_withdraw_method);
		$this->tester->assertTrue(!$divided_value_bills->isEmpty(), "Делимая вариация (1)");
		
		$this->tester->assertEquals($atm->balance(), 89750);
		
		$divided_value_bills = AtmService::divide(clone $atm, 75550, $large_bills_withdraw_method);
		$this->tester->assertTrue(!$divided_value_bills->isEmpty(), "Делимая вариация (2)");
		
		$this->tester->assertEquals($atm->balance(), 89750);
		
		$divided_value_bills = AtmService::divide(clone $atm, 15550, $large_bills_withdraw_method);
		$this->tester->assertTrue(!$divided_value_bills->isEmpty(), "Делимая вариация (3)");
		
		// неделимая вариация
		$divided_value_bills = AtmService::divide(clone $atm, 50130, $large_bills_withdraw_method);
		$this->tester->assertTrue($divided_value_bills->isEmpty(), "Неделимая вариация");
	}
	//--------------------------------------------------
	
	public function testTrickyVariations() {
		$atm = new Atm(30, 50, 100);
		
		$bills = new Bills();
		// добавляем количество купюр
		$this->addBillsToPile($bills, 100, 5);
		$this->addBillsToPile($bills, 50, 5);
		$this->addBillsToPile($bills, 30, 5);
		$atm->addBillsAndRejectIncorrect($bills);
		
		// пробуем разложить суммы
		$large_bills_withdraw_method = new LargeBillsWithdrawMethod();
		
		$divided_value_bills = AtmService::divide(clone $atm, 170, $large_bills_withdraw_method);
		$this->tester->assertTrue(!$divided_value_bills->isEmpty(), 'Делимая вариация (1)');
	}
	//--------------------------------------------------
	
	private function addBillsToPile(Bills $bills, int $bill_value, int $bill_count) {
		for ($i = 0; $i < $bill_count; $i++) {
			$bills->add(new Bill($bill_value));
		}
	}
	//--------------------------------------------------
}