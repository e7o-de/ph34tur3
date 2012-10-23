<?php
namespace Ph34tur3;

class Object
{
	public function __call($func, $args)
	{
		if (property_exists($this, $func) && $this->$func instanceof \Closure) {
			return call_user_func_array($this->$func, $args);
		} else {
			trigger_error('Call to undefined method ' . $func);
		}
	}
}