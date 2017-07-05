<?php namespace Bruha\Generator\Utils;
/**
 * File operation class
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
		if (is_file($source)) {
			if (($content = file_get_contents($source)) === FALSE) throw new \Exception("Cannot read file $source.");
			return $content;
		} else throw new \Exception("Cannot read file $source.");
	}

	/**
	 * Write content into file
	 * @param string $destination
	 * @param string $content
	 * @throws \FileException
	 * @static
	 */
	/* public static function write($destination, $content, $rewrite = TRUE, $neon = FALSE) {
		if (!$rewrite) return;
		if (!is_dir(dirname($destination))) if (!mkdir(dirname($destination), 0777, TRUE)) throw new \FileException("Cannot create path $destination.");
		if (!file_put_contents($destination, $neon ? str_replace('()', '', \Nette\Neon\Neon::encode($content, \Nette\Neon\Encoder::BLOCK)) : $content)) throw new \FileException("Cannot write file $destination.");
		return $content;	
	} */

	/**
	 * Get all files within directory
	 * @param string $source Source directory path
	 * @return array
	 * @static
	 */
	/* public static function getDirectoryFiles($source, $recursive = FALSE) {
		$files = [];
		if ($recursive) {
			foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS)) as $path => $file) $files[] = $path;
		} else foreach (new \DirectoryIterator($source) as $file) if ($file->isFile()) $files[] = $file->getFilename();
		return $files;
	} */

	/**
	 * Get all PHP classes within file
	 * @param type $source
	 * @return array
	 * @static
	 */
	public static function getClassesFromPHPFile($source) {
		$classes = [];
		$tokens = token_get_all(File::read($source));
		for ($i = 2; $i < count($tokens); $i++) if ($tokens[$i - 2][0] == T_CLASS && $tokens[$i - 1][0] == T_WHITESPACE && $tokens[$i][0] == T_STRING) $classes[] = $tokens[$i][1];
		return $classes;
	}

	/**
	 * Copy all files and directories within directory except excluded directories to another directory
	 * @param string $source Source directory path
	 * @param string $destination Destination directory path
	 * @param array $exceptDirectories Directories which will not be copied
	 * @param \Closure $callback
	 * @throws \FileException
	 * @static
	 * 
	 */
	/* 
	public static function copyDirectory($source, $destination, array $exceptDirectories = [], \Closure $callback = NULL) {
		if (!is_dir($destination)) if (!mkdir($destination, 0777, TRUE)) throw new \FileException("Cannot create directory $destination.");
		foreach ($iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($source, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST) as $item) {
			if (!in_array(str_replace("$source\\", '', $item->isDir() ? $item : dirname($item)), $exceptDirectories)) {
				$path = $destination . DIRECTORY_SEPARATOR . $iterator->getSubPathName();
				if ($item->isDir()) {
					if (!is_dir($path)) if (!mkdir($path)) throw new \FileException("Cannot create directory $path.");
				} else if (!copy($item, $path)) throw new \FileException("Cannot create file $path.");
				$callback(realpath($path));
			}
		}
	} */

	/**
	 * Remove directory with all directories and files within
	 * @param string $path Target directory path
	 * @throws \Exception
	 * @static
	 */
	/*public static function removeDirectory($path) {
		$path = realpath($path) !== FALSE ? realpath($path) : $path;
		if (is_dir($path)) {
			foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST) as $file) {
				if ($file->isDir()) {
					if (!rmdir($file->getRealPath())) throw new \Exception('    => Cannot remove directory ' . $file->getRealPath() . '.');
				} else if (!unlink($file->getRealPath())) throw new \Exception('    => Cannot remove file ' . $file->getRealPath() . '.');
			}
			if (!rmdir($path)) throw new \Exception("    => Cannot remove directory $path.");
		}
	}*/
}