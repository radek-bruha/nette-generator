<?php namespace Bruha\Generator\Utils;
/**
 * Class with additional file operations
 * @author Radek Brůha
 * @version 1.0
 */
class File {
	/**
	 * Read content of file
	 * @param string $source File source
	 * @return string File content
	 * @throws \Exception
	 * @static
	 */
	public static function read($source) {
		if(is_file($source)) {
			if(($content = file_get_contents($source)) === FALSE) throw new \Exception("Cannot read file $source.");
			return $content;
		} else throw new \Exception("Cannot read file $source.");
	}

	/**
	 * Get all PHP classes within file
	 * @param type $source
	 * @return array
	 * @static
	 */
	public static function getClassesFromPHPFile($source) {
		$classes = [];
		$tokens = token_get_all(File::read($source));
		for($i = 2; $i < count($tokens); $i++) if($tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING) $classes[] = $tokens[$i][1];
		return $classes;
	}
}