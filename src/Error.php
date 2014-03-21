<?php
namespace Ph34tur3;

class Error
{
	public static function errorHandler($errno, $errstr, $errfile, $errline, $errcontext)
	{
		if (error_reporting() & $errno) {
			if ($errno == E_RECOVERABLE_ERROR) {
				// Scalar type hinting
				// Implementation inspired by comments on
				// http://www.php.net/manual/en/language.oop5.typehinting.php
				$regexp = '/Argument (\d)+ passed to (.+) must be an instance of (?<hint>.+), (?<given>.+) given/i';
				if (preg_match($regexp, $errstr, $match)) {
					$given = @end(explode('\\', $match['given']));
					$hint = @end(explode('\\', $match['hint']));
					if ($hint == 'int') {
						$hint = 'integer';
					}
					if ($hint == $given) {
						return true;
					}
				}
			}
			return false;
		}
	}
}

set_error_handler(function($errno, $errstr, $errfile, $errline, $errcontext) {
	return Error::errorHandler($errno, $errstr, $errfile, $errline, $errcontext);
});
