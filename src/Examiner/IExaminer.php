<?php namespace Bruha\Generator\Examiner;
/**
 * Database Examiner Interface
 * @author Radek Brůha
 * @version 1.0
 */
interface IExaminer {
	/**
	 * @param \PDO $database
	 * @param \stdClass $settings
	 */
	public function __construct(\PDO $database, \stdClass $settings);

	/**
	 * Gets list of database tables
	 * @retrun array
	 */
	public function getTables();

	/**
	 * Gets list of table columns
	 * @param string $table Table name
	 * @return array
	 */
	public function getColumns($table);

	/**
	 * Gets list of column keys
	 * @param string $table Table name
	 * @param string $column Column name
	 * @return array
	 */
	public function getColumnKeys($table, $column);

	/**
	 * Gets list of column foreign keys
	 * @param type $table Table name
	 * @param type $column Column name
	 * @return array
	 */
	public function getColumnForeignKeys($table, $column);
}