<?php


namespace app;

/**
 * Class AtmCassettes
 * @package app
 *
 * коллекция кассет банкомата
 */
class AtmCassettes extends Collection
{
	protected $type = AtmCassette::class;

	public function offsetGet($offset) : AtmCassette {
		return parent::offsetGet($offset);
	}
	//--------------------------------------------------
	
	public function isEmpty() : bool {
		return parent::isEmpty();
	}
	//--------------------------------------------------
	
	public function __clone() {
		if (!$this->isEmpty()) {
			$temporary_cassettes = new AtmCassettes();
			
			/** @var AtmCassette $atm_cassette */
			foreach ($this as $atm_cassette) {
				$temporary_cassettes->offsetSet($atm_cassette->value(), clone $atm_cassette);
			}
			
			$this->collection = [];
			
			/** @var AtmCassette $atm_cassette */
			foreach ($temporary_cassettes as $atm_cassette) {
				$this->offsetSet($atm_cassette->value(), clone $atm_cassette);
			}
		}
	}
	//--------------------------------------------------
}