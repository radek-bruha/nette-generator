<?php namespace Bruha\Generator\Utils\Object;
/**
 * Store table column structure information
 * @author Radek Brůha
 * @version 1.0
 */
class Column {
	public $name;
	public $sanitizedName;
	public $type;
	public $nullable;
	public $keys;
	public $default;
	public $extra;
	public $comment;

	public function __construct($name = NULL, \Bruha\Generator\Utils\Object\Type $type = NULL, $nullable = NULL, array $keys = [], $default = NULL, $extra = NULL, $comment = NULL) {
		$this->name = $name;
		$this->sanitizedName = implode('', array_map(function($value) { return ucfirst($value); }, explode('_', $name)));
		$this->type = $type;
		$this->nullable = $nullable;
		$this->keys = $keys;
		$this->default = $default;
		$this->extra = $extra;
		$this->comment = $comment;
	}
}