<?php
namespace Example;

class C extends A, B implements D
{
	/*public function getA()
	{
		return 'I\'m not A ;)';
	}
	
	public function getB()
	{
		return 'I\'m not B ;)';
	}*/
	
	public static function __static()
	{
		echo '[__static called]';
	}
	
	public function giveMeIntegers(int $a)
	{
		echo 'Yeah!';
	}
}