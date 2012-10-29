<?php
namespace Ph34tur3;

class Object
{
	public static $__instanceOfInfo = array();
	
	public function __call($func, $args)
	{
		if (property_exists($this, $func) && $this->$func instanceof \Closure) {
			return call_user_func_array($this->$func, $args);
		} else {
			trigger_error('Call to undefined method ' . $func);
		}
	}
	
	protected function __isInstanceOf($type1, $type2)
	{
		$type1 = end(explode('\\', get_class($type1)));
		$type2 = end(explode('\\', $type2));
		return $type1 == $type2;
	}
}