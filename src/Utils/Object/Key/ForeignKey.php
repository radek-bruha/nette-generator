<?php namespace Bruha\Generator\Utils\Object\Key;
/**
 * Store table column foreign key information
 * @author Radek Brůha
 * @version 1.0
 */
class ForeignKey {
	/** @var \Bruha\Generator\Utils\Object\Table */
	public $table;
	public $key;
	public $value;

	function __construct(\Bruha\Generator\Utils\Object\Table $table = NULL, $key = NULL, $value = NULL) {
		$this->table = $table;
		$this->key = $key;
		$this->value = $value;
	}
}