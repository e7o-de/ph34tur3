<?php
namespace Ph34tur3;

abstract class ClassLoader
{
	static $classCache;
	static $parseRegexFunctions;
	
	static public function __static()
	{
		self::$classCache = array();
		self::$parseRegexFunctions = '/function(&nbsp;|\w)+<\/span><span style="color: ' . ini_get('highlight.default') . '">([a-z0-9_]+)/is';
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
			// Don't modify (interfaces etc.)
			return $code;
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
		
		$additionalBody = array();
		
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
					$extendCode = self::getClassSource($extend);
					// ToDo: use/namespace etc.
					// It's a workaround for now ;)
					$extendCode = substr($extendCode, strpos($extendCode, '{') + 1);
					$extendCode = substr($extendCode, 0, strrpos($extendCode, '}'));
					$extend = str_replace('\\', '__', $extend);
					$additionalBody[$extend] = $extendCode;
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
		
		$originalCode = substr($code, $posClassHeadEnd + 1);
		
		$allFunctionsParents = array();
		foreach ($additionalBody as $body) {
			$allFunctionsParents = array_merge(
				self::getFunctions($body),
				$allFunctionsParents
			);
		}
		$allFunctionsParents = array_unique($allFunctionsParents);
		$allFunctionsSelf = self::getFunctions($originalCode);
		
		foreach ($allFunctionsSelf as $func) {
			if (in_array($func, $allFunctionsParents)) {
				foreach ($additionalBody as $extend => &$body) {
					$body = preg_replace('/function\s+' . $func . '/i', 'function ' . $extend . '__' . $func, $body);
				}
				unset($body);
			}
		}
		
		$code = substr($code, 0, $posClass) . $newHead . '{' . implode("\n", $additionalBody) . $originalCode;
		return $code;
	}
	
	private function getFunctions($classCode)
	{
		if (strlen($classCode) == 0) return array();
		$code = highlight_string('<?php class _tmp {' . $classCode, true);
		preg_match_all(self::$parseRegexFunctions, $code, $functions);
		return $functions[2];
	}
} ClassLoader::__static();