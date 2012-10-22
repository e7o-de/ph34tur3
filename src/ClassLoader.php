<?php
namespace Ph34tur3;

abstract class ClassLoader
{
	static $classCache;
	
	static public function __static()
	{
		self::$classCache = array();
	}
	
	static public function loadClass($class)
	{
		$classCode = self::getClassSource($class);
		if ($classCode == null) {
			return;
		}
		self::loadFromCode($classCode);
		self::callStaticConstructor($class);
	}
	
	static private function loadFromCode($code)
	{
		eval('?>' . $code);
	}
	
	static private function getClassSource($class)
	{
		if (isset(self::$classCache[$class])) {
			return self::$classCache[$class];
		}
		$filename = getcwd() . '/' . str_replace('\\', '/', $class) . '.php';
		if (!file_exists($filename)) {
			// Unable to find class
			return null;
		}
		$code = file_get_contents($filename);
		$code = self::injectPh34tur35($code);
		
		return self::$classCache[$class] = $code;
	}
	
	static private function callStaticConstructor($class)
	{
		$staticConstructor = $class . '::__static';
		if (is_callable($staticConstructor)) {
			call_user_func($staticConstructor);
		}
	}
	
	static private function injectPh34tur35($code)
	{
		$posClass = strpos($code, "\nclass");
		if ($posClass === false) {
			$posClass = strpos($code, ' class');
		}
		if ($posClass === false) {
			return null;
		}
		
		$posNamespace = strpos($code, 'namespace ');
		if ($posNamespace !== false) {
			$posNamespace += 10;
			$namespace = trim(substr($code, $posNamespace, strpos($code, ';', $posNamespace) - $posNamespace));
			if (substr($namespace, -1, 1) != '\\') {
				$namespace .= '\\';
			}
		} else {
			$namespace = '';
		}
		
		$posClassHeadEnd = strpos($code, '{', $posClass + 1);
		
		$additionalBody = '';
		
		$oldHead = substr($code, $posClass, $posClassHeadEnd - $posClass);
		
		$comment1 = strpos($oldHead, '//');
		$comment2 = strpos($oldHead, '#');
		if ($comment1 !== false || $comment2 !== false) {
			if ($comment1 === false) {
				$stripPos = $comment2;
			} else if ($comment2 === false) {
				$stripPos = $comment1;
			} else {
				$stripPos = min($comment1, $comment2);
			}
			$oldHead = substr($oldHead, 0, $stripPos);
		}
		
		$posExtends = strpos($oldHead, 'extends');
		$posImplements = strpos($oldHead, 'implements');
		
		if ($posImplements !== false) {
			$implements = substr($oldHead, $posImplements + 11);
			$oldHead = substr($oldHead, 0, $posImplements);
		} else {
			$implements = null;
		}
		
		if ($posExtends !== false) {
			$extends = substr($oldHead, $posExtends + 8);
			$oldHead = substr($oldHead, 0, $posExtends);
			
			if (strpos($extends, ',') !== false) {
				$extends = explode(',', $extends);
				foreach ($extends as $extend) {
					$extend = trim($extend);
					if ($extend[0] != '\\') {
						$extend = $namespace . $extend;
					}
					$extend = self::getClassSource($extend);
					// ToDo: use/namespace etc.
					// It's a workaround for now ;)
					$extend = substr($extend, strpos($extend, '{') + 1);
					$extend = substr($extend, 0, strrpos($extend, '}'));
					$additionalBody .= $extend;
				}
				$extends = '\Ph34tur3\Object';
			}
		} else {
			$extends = '\Ph34tur3\Object';
		}
		
		$newHead = $oldHead . ' extends ' . $extends;
		if ($implements != null) {
			$newHead .= ' implements ' . $implements;
		}
		
		$code = substr($code, 0, $posClass) . $newHead . '{' . $additionalBody . substr($code, $posClassHeadEnd + 1);
		return $code;
	}
} ClassLoader::__static();