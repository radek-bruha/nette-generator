<?php namespace Bruha\Generator\Examiner;
/**
 * MySQL Database Examiner
 * @author Radek Brůha
 * @version 1.0
 */
class MysqlExaminer implements IExaminer {
	/** @var \PDO */
	private $database;
	/** @var array */
	private $tables = [];
	/** @var \stdClass */
	private $settings;

	/**
	 * @param \PDO $database
	 * @param \stdClass $settings
	 */
	public function __construct(\PDO $database, \stdClass $settings) {
		$this->database = $database;
		$this->settings = $settings;
	}

	/**
	 * Gets list of database tables
	 * @retrun array
	 */
	public function getTables() {
		foreach($this->database->query('SHOW TABLE STATUS;')->fetchAll(\PDO::FETCH_NUM) as $t) $this->tables[$t[0]] = new \Bruha\Generator\Utils\Object\Table($t[0], $t[17] ?: NULL);
		foreach($this->tables as $t) $t->columns = $this->getColumns($t->name);
		foreach($this->tables as $t) {
			$hasPrimaryKey = FALSE;
			foreach($t->columns as $column) foreach($column->keys as $key) {
				if($key instanceof \Bruha\Generator\Utils\Object\Key\PrimaryKey) $hasPrimaryKey = TRUE;
				if($key instanceof \Bruha\Generator\Utils\Object\Key\ForeignKey) $key->value = $this->getColumnForeignKeyValue($key);
			}
			$t->state = $hasPrimaryKey ? \Utils\Constants::TABLE_STATE_OK : \Utils\Constants::TABLE_STATE_ERROR_NO_PRIMARY_KEY;
		}
		return $this->tables;
	}

	/**
	 * Gets list of table columns
	 * @param string $table Table name
	 * @return array
	 */
	public function getColumns($table) {
		$columns = [];
		foreach($this->database->query("SHOW FULL COLUMNS FROM $table;")->fetchAll(\PDO::FETCH_NUM) as $column) {
			list($name, $type, , $null, , $default, $extra, , $comment) = $column;
			$columns[] = new \Bruha\Generator\Utils\Object\Column($name, $this->getColumnType($type), $null !== 'NO', $this->getColumnKeys($table, $name), $default !== NULL ? $default : FALSE, $extra, $comment);
		}
		return $columns;
	}

	/**
	 * Gets list of column keys
	 * @param string $table Table name
	 * @param string $column Column name
	 * @return array
	 */
	public function getColumnKeys($table, $column) {
		$keys = [];
		foreach($this->database->query("SHOW INDEX FROM $table WHERE Column_name = '$column';")->fetchAll(\PDO::FETCH_NUM) as $key) {
			list(, , $name, $unique) = $key;
			if($name === 'PRIMARY') $keys[] = new \Bruha\Generator\Utils\Object\Key\PrimaryKey;
			if((int)$unique === 0) $keys[] = new \Bruha\Generator\Utils\Object\Key\UniqueKey;
			if((int)$unique === 1) $keys[] = new \Bruha\Generator\Utils\Object\Key\IndexKey;
		}
		foreach($this->getColumnForeignKeys($table, $column) as $foreignKey) $keys[] = $foreignKey;
		return $keys;
	}

	/**
	 * Gets list of column foreign keys
	 * @param type $table Table name
	 * @param type $column Column name
	 * @return array
	 */
	public function getColumnForeignKeys($table, $column) {
		$keys = [];
		if(in_array($this->settings->source, [\Utils\Constants::SOURCE_MYSQL_DISCOVERED, \Utils\Constants::SOURCE_DOCTRINE2], TRUE)) {
			foreach($this->database->query("SELECT REFERENCED_TABLE_NAME AS 'table', REFERENCED_COLUMN_NAME AS 'column' FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND REFERENCED_TABLE_NAME IS NOT NULL AND TABLE_NAME = '$table' AND COLUMN_NAME = '$column';")->fetchAll(\PDO::FETCH_OBJ) as $key) {
				if($this->tables[$key->table]) $keys[] = new \Bruha\Generator\Utils\Object\Key\ForeignKey($this->tables[$key->table], $key->column);
			}
		} else if($this->settings->source === \Utils\Constants::SOURCE_MYSQL_CONVENTIONAL) {
			if(($position = mb_strrpos($column, '_')) !== FALSE && (int)$this->database->query("SELECT COUNT(*) count FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '" . ($table = mb_substr($column, 0, $position)) . "' AND COLUMN_NAME = '" . ($column = mb_substr($column, $position + 1)) . "';")->fetch(\PDO::FETCH_OBJ)->count === 1) {
				if($this->tables[$table]) $keys[] = new \Bruha\Generator\Utils\Object\Key\ForeignKey($this->tables[$table], $column);
			}
		}
		return $keys;
	}

	/**
	 * Gets column type
	 * @param string $rawType Column type
	 * @return \Bruha\Generator\Utils\Object\Type
	 */
	private function getColumnType($rawType) {
		$type = new \Bruha\Generator\Utils\Object\Type;
		$type->length = NULL;
		$type->extra = NULL;
		if(($position = mb_strpos($rawType, '(')) !== FALSE) {
			$type->name = mb_substr($rawType, 0, $position);
			preg_match('~\d+~', $rawType, $matches);
			if(isset($matches[0])) $type->length = (int)$matches[0];
			if(mb_strpos($rawType, 'unsigned') !== FALSE) $type->extra = 'unsigned';
			if(mb_strpos($rawType, 'zerofill') !== FALSE) $type->extra = 'unsigned zerofill';
		} else $type->name = $rawType;
		if($type->name === 'tinyint' && $type->length === 1) {
			$type->name = 'boolean';
			$type->length = 1;
		}
		if($type->name === 'enum' || $type->name === 'set') {
			$type->extra = explode(',', str_replace(['enum(', 'set(', "'", ')'], '', $rawType));
			$type->length = count($type->extra);
		}
		return $type;
	}

	/**
	 * Gets column name which is shown instead of foreign key
	 * @param \Bruha\Generator\Utils\Object\Key\ForeignKey $key Foreign key
	 * @return string
	 */
	private function getColumnForeignKeyValue(\Bruha\Generator\Utils\Object\Key\ForeignKey $key) {
		foreach($key->table->columns as $column) {
			if(in_array($column->name, ['name', 'title'], TRUE)) return $column->name;
			if(in_array($column->type->name, ['varchar', 'char'], TRUE)) return $column->name;
		}
		return count($key->table->columns) >= 2 ? $key->table->columns[1]->name : $key->table->columns[0]->name;
	}
}