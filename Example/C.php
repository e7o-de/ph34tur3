<?php
namespace Example;

class C extends A, B implements D
{
	public function getA()
	{
		return 'I\'m not A ;)';
	}
	
	public function getB()
	{
		return 'I\'m not B ;)';
	}
	
	public static function __static()
	{
		echo '[__static called]';
	}
	
	public function giveMeIntegers(int $a)
	{
		echo 'Yeah!';
	}
	
	public function checkParents()
	{
		$ct = 0;
		if ($this instanceof A) $ct++;
		if ($this instanceof B) $ct++;
		if ($this instanceof C) $ct++;
		if ($this instanceof D) $ct++;
		echo $ct;
	}
}