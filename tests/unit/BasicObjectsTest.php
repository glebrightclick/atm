<?php

use app\Atm;
use app\Bill;
use app\Bills;
use app\EmptyCollectionException;

class BasicObjectsTest extends \Codeception\Test\Unit
{
	/**
	* @var \UnitTester
	*/
	protected $tester;

	// tests
	public function testEmptyAtmBalance() {
		$empty_atm = new Atm(100, 200, 500, 1000, 2000);
		$this->tester->assertEquals($empty_atm->balance(), 0);
	}
	//--------------------------------------------------
	
	public function testNonEmptyAtmWithSimpleBillsInside() {
		$atm = new Atm(50, 100, 200, 500, 1000, 2000, 5000);
		
		$bills = new Bills();
		$bills->add(new Bill(100));
		$bills->add(new Bill(1000));
		$rejected_bills = $atm->addBillsAndRejectIncorrect($bills);
		
		$this->tester->assertTrue($rejected_bills->count() === 0, "Банкомат не принял корректные купюры");
		$this->tester->assertEquals($atm->balance(), 1100);
	}
	//--------------------------------------------------
	
	public function testAddIncorrectBill() {
		$atm = new Atm(100);
		
		$bills = new Bills();
		$bills->add(new Bill(200));
		
		$rejected_bills = $atm->addBillsAndRejectIncorrect($bills);
		
		$this->tester->assertTrue($rejected_bills->count() === 1, "Банкомат позволил добавить некорректную купюру");
	}
	//--------------------------------------------------
	
	public function testAddEmptyBillsToAtm() {
		$atm = new Atm(100, 200);
		
		$bills = new Bills();
		$rejected_bills = $atm->addBillsAndRejectIncorrect($bills);
		
		$this->tester->assertTrue($rejected_bills->count() === 0, "Банкомат вернул купюры, хотя не должен был");
	}
	//--------------------------------------------------
	
	public function testGetBillFromAtm() {
		$atm = new Atm(100);
		
		$bills = new Bills();
		$bills->add(new Bill(100));
		$atm->addBillsAndRejectIncorrect($bills);
		
		try {
			$removed_bills = $atm->removeBills(100, 1);
		} catch (EmptyCollectionException $e) {
			$this->tester->assertFalse(true, "В банкомате не оказалось нужное количество купюр");
		}
		
		$this->tester->assertTrue($removed_bills->count() === 1, "Банкомат вернул некорректное число купюр");
	}
	//--------------------------------------------------
	
	public function testGetIncorrectAmountOfBillsFromAtm() {
		$atm = new Atm(100, 200, 500);
		
		$bills = new Bills();
		$bills->add(new Bill(100));
		$bills->add(new Bill(200));
		$bills->add(new Bill(200));
		$bills->add(new Bill(200));
		$bills->add(new Bill(500));
		$bills->add(new Bill(500));
		$rejected_bills = $atm->addBillsAndRejectIncorrect($bills);
		
		$this->tester->assertTrue($rejected_bills->count() === 0, "Банкомат вернул купюры, хотя не должен был");
		
		try {
			$atm->removeBills(500, 3);
		} catch (InvalidArgumentException $exception) {
			// банкомат, действительно, должен выбросить исключение
			return true;
		} catch (EmptyCollectionException $e) {
			$this->tester->assertFalse(true, "Банкомат выкинул исключение, хотя не должен был");
		}
		
		$this->tester->assertFalse(true, "Банкомат позволил забрать некорректное количество купюр");
		return false;
	}
	//--------------------------------------------------
}