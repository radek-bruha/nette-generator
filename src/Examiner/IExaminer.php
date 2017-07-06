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
	 * @retrun array of \Bruha\Generator\Utils\Object\Table objects
	 */
	public function getTables();
}