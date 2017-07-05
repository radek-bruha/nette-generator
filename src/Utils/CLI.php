<?php namespace Utils;
/**
 * CLI operation class
 * @author Radek Brůha
 * @version 1.0
 */
class CLI {

	/**
	 * Read content from CLI
	 * @param bool $asInt
	 * @param array $options
	 * @param \Closure $callback
	 * @return string|integer|FALSE
	 * @static
	 */
	public static function read($asInt = FALSE, array $options = [], \Closure $callback = NULL) {
		if (!empty($options)) {
			while ($input = trim(fgets(STDIN))) {
				if (!in_array($input, $options)) {
					$callback($input);
				} else break;
			}
		} else $input = trim(fgets(STDIN));
		return $input ? ($asInt ? (int)$input : $input) : FALSE;
	}

	/**
	 * Write content into CLI
	 * @param string $string
	 * @param integer $indent
	 * @param bool $arrow
	 * @param bool $endNewLine
	 * @param bool $startNewLine
	 * @static
	 */
	public static function write($string, $indent = 0, $arrow = TRUE, $endNewLine = TRUE, $startNewLine = FALSE) {
		echo ($startNewLine ? PHP_EOL : '') . str_repeat(' ', $indent === 0 ? 1 : $indent * 3) . ($arrow ? '=> ' : '') . $string . ($endNewLine ? PHP_EOL : '');
	}
}