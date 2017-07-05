<?php require __DIR__ . '\src\Generator.php';
if (!function_exists('dump')) {
	/** @tracySkipLocation */
	function dump($var) {
		foreach (func_get_args() as $arg) Tracy\Debugger::dump($arg);
		return $var;
	}
}
if (!function_exists('barDump')) {
	/** @tracySkipLocation */
	function barDump($var, $title = NULL, array $options = NULL) {
		Tracy\Debugger::barDump($var, $title, $options);
	}
}
if (!function_exists('ajaxDump')) {
	/** @tracySkipLocation */
	function ajaxDump($var) {
		Tracy\FireLogger::log($var);
	}
}

if (!function_exists('timerDump')) {
	/** @tracySkipLocation */
	function timerDump($var = NULL, $title = NULL, $options = []) {
		Tracy\Debugger::barDump(Tracy\Debugger::timer($var), $title, $options);
	}
}
(new \Bruha\Generator\Generator)->run();