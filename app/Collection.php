<?php


namespace app;


use ArrayAccess;
use ArrayIterator;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;

/**
 * Class Collection
 * @package app
 *
 * стандартная коллекция объектов
 */
abstract class Collection implements Countable, IteratorAggregate, ArrayAccess
{
	protected $type;
	protected $collection = [];
	//--------------------------------------------------
	
	private function validateType($object)
	{
		if (!($object instanceof $this->type))
			throw new InvalidArgumentException("Объект типа " . get_class($object) . " не может быть добавлен в коллекцию объектов типа " . $this->type);
	}
	//--------------------------------------------------
	
	public function add($object) : Collection {
		$this->offsetSet(null, $object);
		
		return $this;
	}
	//--------------------------------------------------
	
	public function remove($object) : Collection {
		$offset = array_search($object, $this->collection, true);
		
		if ($offset !== false)
			$this->offsetUnset($offset);
		
		return $this;
	}
	//--------------------------------------------------
	
	public function isEmpty() : bool {
		return empty($this->collection);
	}
	//--------------------------------------------------
	
	public function getIterator() : ArrayIterator {
		return new ArrayIterator($this->collection);
	}
	//--------------------------------------------------
	
	public function offsetExists($offset) : bool {
		return isset($this->collection[$offset]);
	}
	//--------------------------------------------------
	
	public function offsetGet($offset) {
		if (!$this->offsetExists($offset))
			return null;
		
		return $this->collection[$offset];
	}
	//--------------------------------------------------
	
	public function offsetSet($offset, $object)
	{
		$this->validateType($object);
		
		if ($offset === null)
			$offset = $this->isEmpty() ? 0 : max(array_keys($this->collection)) + 1;
		
		$this->collection[$offset] = $object;
	}
	//--------------------------------------------------
	
	public function offsetUnset($offset)
	{
		unset($this->collection[$offset]);
	}
	//--------------------------------------------------
	
	public function count()
	{
		return sizeof($this->collection);
	}
	//--------------------------------------------------
}