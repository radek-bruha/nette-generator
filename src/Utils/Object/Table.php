<?php namespace Bruha\Generator\Utils\Object;
/**
 * Store database table structure information
 * @author Radek Brůha
 * @version 1.0
 */
class Table {
	public $name;
	public $sanitizedName;
	public $comment;
	public $columns;
	public $state;

	public function __construct($name = NULL, $comment = NULL, array $colums = [], $state = NULL) {
		$this->name = $name;
		$this->sanitizedName = implode('', array_map(function($value) {
			return ucfirst($value);
		}, explode('_', $name)));
		$this->comment = $comment;
		$this->columns = $colums;
		$this->state = $state;
	}
}