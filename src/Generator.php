<?php namespace Bruha\Generator;
use \Utils\CLI;
/**
 * Main application setup class
 * @author Radek Brůha
 * @version 1.0
 */
class Generator {
	private static $databaseConfigs = [];
	private static $showOnce = TRUE;
	private static $netteDirectory;
	private static $startTime;
	private static $settings;

	public function run() {
		static::$netteDirectory = realpath(__DIR__ . '/../../../../app');
		$netteContainer = $this->getNetteContainer();
		if (php_sapi_name() !== 'cli') {
			define('STDIN', fopen('php://stdin', 'r'));
			echo '<pre>';
		}
		CLI::write('Welcome to the Nette Framework CRUD generator 1.0@beta4.', 0, FALSE);
		static::$settings = (object)[
				'netteRoot' => realpath(static::$netteDirectory . '/..'),
				'netteConfig' => FALSE,
				'netteDatabase' => FALSE,
				'source' => \Utils\Constants::SOURCE_MYSQL_DISCOVERED,
				'tables' => [],
				'table' => FALSE,
				'target' => \Utils\Constants::TARGET_NETTE_DATABASE,
				'foreignKeys' => \Utils\Constants::FOREIGN_KEYS_TABLE,
				'module' => FALSE,
				'template' => realpath(__DIR__ . '/Templates/default'),
				'php' => '<?php',
				'entityManager' => NULL
		];
		$this->showSourceDialog();
		CLI::write('Verifying Nette configuration:', 0, TRUE, TRUE, TRUE);
		static::$settings->netteDatabase = $this->getDatabaseConnectionParameters($netteContainer);
		static::$settings->netteConfig = \Nette\Neon\Neon::decode(\Bruha\Generator\Utils\File::read(static::$netteDirectory . '/config/config.neon'));
		if ($this->checkExtensionsConfiguration()) {
			CLI::write('New extensions were installed.', 0, TRUE, TRUE, TRUE);
			CLI::write('Application needs to be restarted for loading them.', 1);
			CLI::write("Write 'php -f index.php' for start with new loaded extensions.", 2);
			exit;
		}
		$this->showTablesDialog($this->processSourceDialog($netteContainer));
		$this->showTargetDialog();
		$this->showForeignKeysDialog();
		$this->showModuleDialog();
		$this->chooseTemplatesDialog();
		static::$startTime = microtime(TRUE);
		$this->generate();
	}

	/** Show source choosing dialog */
	private function showSourceDialog() {
		CLI::write('Choose source for CRUD building:', 0, TRUE, TRUE, TRUE);
		CLI::write('Press 1 for MySQL InnoDB tables (with foreign keys support).', 1);
		CLI::write('Press 2 for MySQL MyISAM tables (without foreign keys support).', 1);
		CLI::write('Press 3 for Doctrine2 entities.', 1);
		CLI::write('Your choice: ', 1, TRUE, FALSE);
		$input = CLI::read(TRUE, [1, 2, 3], function($input) {
				CLI::write("Uknown choice '$input'. Try it again, please.", 2);
				CLI::write('Your choice: ', 1, TRUE, FALSE);
			});
		$input ? static::$settings->source = $input : CLI::write('Pressed Enter, using MySQL InnoDB tables (with foreign keys support).', 2);
	}

	/** Process source choosing dialog */
	private function processSourceDialog($netteContainer) {
		CLI::write('Connecting to database:', 1, TRUE, FALSE, TRUE);
		try {
			$database = static::$settings->netteDatabase;
			$database = new \PDO("mysql:dbname=$database->database;host=$database->hostname", $database->username, $database->password, NULL);
			CLI::write('CONNECTED', 0, FALSE);
		} catch (\Exception $e) {
			CLI::write('NOT CONNECTED', 0, FALSE);
			CLI::write($e->getMessage(), 2);
		}
		if (static::$settings->source === \Utils\Constants::SOURCE_DOCTRINE2) {			
			try {
				CLI::write('Creating MySQL InnoDB engine tables:', 2, TRUE, FALSE);
				static::$settings->entityManager = $netteContainer->getByType('\Kdyby\Doctrine\EntityManager');
				$this->buildFromEntities();
				CLI::write('SUCCESS', 0, FALSE);
			} catch (\Exception $e) {
				CLI::write('ERROR', 0, FALSE);
				CLI::write($e->getMessage(), 3);
			}
		}
		CLI::write('Getting list of database tables:', 2);
		return (new \Bruha\Generator\Examiner\MysqlExaminer($database, static::$settings))->getTables();
	}

	/** Show tables choosing dialog */
	private function showTablesDialog($originalTables) {
		foreach ($originalTables as $table) CLI::write($table->name, 3);
		CLI::write('Choose tables for CRUD building:', 0, TRUE, TRUE, TRUE);
		CLI::write('Press Enter for all found tables.', 1);
		CLI::write('Write table names separated by comma for only few of them.', 1);
		CLI::write('Your choice: ', 1, TRUE, FALSE);
		$input = CLI::read();
		$tables = $input ? array_intersect(array_map(function($table) {
					return $table->name;
				}, $originalTables), array_map('trim', explode(',', $input))) : array_map(function($table) {
				return $table->name;
			}, $originalTables);
		if (!$input) CLI::write('Pressed Enter, using all found tables.', 2);
		if (!$tables) {
			$tables = array_map(function($table) {
				return $table->name;
			}, $originalTables);
			CLI::write('No tables with given names were found, using all of them.', 2);
		}
		CLI::write('Verifying tables statuses:', 1, TRUE, TRUE, TRUE);
		foreach ($tables as $table) {
			if ($originalTables[$table]->state === \Utils\Constants::TABLE_STATE_OK) {
				static::$settings->tables[$table] = $originalTables[$table];
				CLI::write("$table: GOOD", 2);
			} else {
				CLI::write("$table: BAD", 2);
				CLI::write('Table doesnt\'t have any primary key!', 3);
			}
		}
	}

	/** Show target choosing dialog */
	private function showTargetDialog() {
		CLI::write('Choose target for CRUD building:', 0, TRUE, TRUE, TRUE);
		CLI::write('Press 1 for Nette\Database models.', 1);
		CLI::write('Press 2 for Doctrine2 models.', 1);
		CLI::write('Your choice: ', 1, TRUE, FALSE);
		$input = CLI::read(TRUE, [1, 2], function($input) {
				CLI::write("Uknown choice '$input'. Try it again, please.", 2);
				CLI::write('Your choice: ', 1, TRUE, FALSE);
			});
		$input ? static::$settings->target = $input : CLI::write('Pressed Enter, using Nette\Database models.', 2);
	}

	/** Show foreign keys choosing dialog */
	private function showForeignKeysDialog() {
		CLI::write('Choose foreign keys chooser for CRUD building:', 0, TRUE, TRUE, TRUE);
		CLI::write('Press 1 for open full table in new window.', 1);
		CLI::write('Press 2 for selectbox with search.', 1);
		CLI::write('Your choice: ', 1, TRUE, FALSE);
		$input = CLI::read(TRUE, [1, 2], function($input) {
				CLI::write("Uknown choice '$input'. Try it again, please.", 2);
				CLI::write('Your choice: ', 1, TRUE, FALSE);
			});
		$input ? static::$settings->foreignKeys = $input : CLI::write('Pressed Enter, using open full table in new window.', 2);
	}

	/** Show module choosing dialog */
	private function showModuleDialog() {
		CLI::write('Do you want to build into module?', 0, TRUE, TRUE, TRUE);
		CLI::write('Press Enter for NO.', 1);
		CLI::write('Write module name for YES.', 1);
		CLI::write('Your choice: ', 1, TRUE, FALSE);
		$input = CLI::read();
		$input ? static::$settings->module = ucfirst($input) : CLI::write('Pressed Enter, using no module.', 2);
	}

	/** Show template choosing dialog */
	private function chooseTemplatesDialog() {
		CLI::write('What templates do you want to use?', 0, TRUE, TRUE, TRUE);
		CLI::write('Press Enter for default ones.', 1);
		CLI::write('Write templates name for specific ones.', 1);
		CLI::write('Getting list of available templates:', 1);
		$templates = ['Default'];
		foreach (\Nette\Utils\Finder::findFiles('TemplateLoader.php')->from(static::$settings->netteRoot) as $file) {
			foreach (\Bruha\Generator\Utils\File::getClassesFromPHPFile($file) as $class) {
				$templates[realpath($file) . '-' . mt_rand(1000, 9999)] = str_replace('Template', '', $class);
			}
		}
		foreach ($templates as $template) CLI::write($template, 2);
		CLI::write('Your choice: ', 1, TRUE, FALSE);
		$input = CLI::read(FALSE, $templates, function($input) {
				CLI::write("Uknown choice '$input'. Try it again, please.", 2);
				CLI::write('Your choice: ', 1, TRUE, FALSE);
			});
		if (!$input || strtolower($input) === 'default') {
			CLI::write('Pressed Enter, using Default template.', 2);
			return;
		}
		static::$settings->template = dirname(mb_substr(array_search($input, $templates), 0, -5)) . "\\$input";
	}

	private function generate() {
		try {
			CLI::write('Generating application:', 0, TRUE, FALSE, TRUE);
			(new Builder\Builder(static::$settings))->build();
			for ($i = 0; $i <= 99; $i++) {
				CLI::write("\r => Generating application: $i% completed", TRUE, FALSE, FALSE);
				usleep(25000);
			}
			CLI::write("\r => Generating application: SUCCESS      ", 0, FALSE);
			CLI::write('Cleaning Nette Cache:', 0, TRUE, FALSE, TRUE);
			\Nette\Utils\FileSystem::delete(static::$settings->netteRoot . '/temp/cache');
			CLI::write('SUCCESS', 0, FALSE);
			CLI::write('Application successfully built in ' . number_format(microtime(TRUE) - static::$startTime - 2, 2, '.', ' ') . ' seconds.', 0, TRUE, TRUE, TRUE);
		} catch (\Doctrine\ORM\Mapping\MappingException $e) {
			CLI::write('ERROR', 0, FALSE);
			CLI::write($e->getMessage(), TRUE, 1);
		} catch (\Exception $e) {
			CLI::write('ERROR', 0, FALSE);
			CLI::write($e->getMessage(), TRUE, 1);
		}
	}

	/**
	 * Load Nette, Doctrine2, project classes and return Nette container
	 * @return \SystemContainer Nette 2.2.X container
	 */
	private function getNetteContainer() {
		$netteContainer = require static::$netteDirectory . '/bootstrap.php';
		$loader = new \Nette\Loaders\RobotLoader;
		$loader->setCacheStorage(new \Nette\Caching\Storages\DevNullStorage);
		$loader->addDirectory(__DIR__);
		$loader->addDirectory(__DIR__ . '/../../../doctrine/annotations/lib');
		$loader->addDirectory(__DIR__ . '/../../../doctrine/cache/lib');
		$loader->addDirectory(__DIR__ . '/../../../doctrine/common/lib');
		$loader->addDirectory(__DIR__ . '/../../../doctrine/dbal/lib');
		$loader->addDirectory(__DIR__ . '/../../../doctrine/inflector/lib');
		$loader->addDirectory(__DIR__ . '/../../../doctrine/lexer/lib');
		$loader->addDirectory(__DIR__ . '/../../../doctrine/orm/lib');
		$loader->register();
		return $netteContainer;
	}

	/**
	 * Return database connection prameters
	 * @param \SystemContainer $netteContainer
	 * @return \stdClass Connection parameters
	 */
	private function getDatabaseConnectionParameters($netteContainer) {
		try {
			$databaseConnection = $netteContainer->getByType('\Nette\Database\Connection');
			$databaseConnection->connect();
			$databaseReflectionProperty = (new \ReflectionClass('\Nette\Database\Connection'))->getProperty('params');
			$databaseReflectionProperty->setAccessible(TRUE);
			$database = $databaseReflectionProperty->getValue($databaseConnection);
			CLI::write('Verifying database configuration:', 1, TRUE, FALSE);
			CLI::write('SUCCESS', 0, FALSE);
			preg_match('~=(.*);~', $database[0], $hostname);
			return (object)['hostname' => $hostname[1],
					'username' => $database[1],
					'password' => $database[2],
					'database' => mb_substr($database[0], mb_strpos($database[0], ';dbname=') + 8)
			];
		} catch (\PDOException $e) {
			$database = $e->getTrace()[0]['args'];
			preg_match('~=(.*);~', $database[0], $hostname);
			$database = (object)['hostname' => $hostname[1],
					'username' => $database[1],
					'password' => $database[2],
					'database' => mb_substr($database[0], mb_strpos($database[0], ';dbname=') + 8)
			];
			return $this->repairDatabaseConfiguration($database);
		}
	}

	/**
	 * Repair database configuration
	 * @param \stdClass $database Connection parameters
	 * @return \stdClass Connection parameters
	 */
	private function repairDatabaseConfiguration($database) {
		CLI::write('Verifying database configuration:', 1, TRUE, FALSE, TRUE);
		$this->checkDatabaseConfiguration($database, function($code, $message, $database) {
			CLI::write('ERROR', 0, FALSE);
			switch ((int)$code) {
				case 2002: CLI::write("Database server '$database->hostname' not found.", 2);
					break;
				case 1044: CLI::write("Access denied for user '$database->username' to database '$database->database'.", 2);
					break;
				case 1045: CLI::write("Access denied for user '$database->username'@'$database->password'.", 2);
					break;
				case 1049: CLI::write("Unknown database '$database->database'.", 2);
					break;
				default: CLI::write($message);
					break;
			}
			CLI::write('Insert right database configuration now:', 2);
			CLI::write('Database hostname: ', 3, TRUE, FALSE);
			$hostname = CLI::read();
			CLI::write('Database username: ', 3, TRUE, FALSE);
			$username = CLI::read();
			CLI::write('Database password: ', 3, TRUE, FALSE);
			$password = CLI::read();
			CLI::write('Database name: ', 3, TRUE, FALSE);
			$database = CLI::read();
			static::$databaseConfigs[] = (object)['hostname' => $hostname, 'username' => $username, 'password' => $password, 'database' => $database];
			$this->repairDatabaseConfiguration(static::$databaseConfigs[count(static::$databaseConfigs) - 1]);
		});
		if (static::$showOnce) {
			static::$showOnce = !static::$showOnce;
			CLI::write('SUCCESS', 0, FALSE);
		}
		$database = end(static::$databaseConfigs);
		$config = \Nette\Neon\Neon::decode(\Bruha\Generator\Utils\File::read(static::$settings->netteRoot . '/app/config/config.neon'));
		$config['nette']['database']['default']['dsn'] = "mysql:host=$database->hostname;dbname=$database->database";
		$config['nette']['database']['default']['user'] = $database->username;
		$config['nette']['database']['default']['password'] = $database->password;
		$config['nette']['database']['default']['reflection'] = 'discovered';
		$config['doctrine']['host'] = $database->hostname;
		$config['doctrine']['user'] = $database->username;
		$config['doctrine']['password'] = $database->password;
		$config['doctrine']['dbname'] = $database->database;
		\Nette\Utils\FileSystem::write(static::$settings->netteRoot . '/app/config/config.neon', preg_replace('~(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+~', "\n", (new \Nette\Neon\Encoder)->encode($config, \Nette\Neon\Encoder::BLOCK)));
		return $database;
	}

	/**
	 * Check database configuration
	 * @param \stdClass $database Connection parameters
	 * @param \Closure $callback Return error code, message and current connection parameters when error occurred
	 */
	private function checkDatabaseConfiguration($database, \Closure $callback = NULL) {
		try {
			new \PDO("mysql:host=$database->hostname;dbname=$database->database", $database->username, $database->password);
		} catch (\PDOException $e) {
			$database = $e->getTrace()[0]['args'];
			preg_match('~=(.*);~', $database[0], $hostname);
			$database = (object)['hostname' => $hostname[1],
					'username' => $database[1],
					'password' => $database[2],
					'database' => mb_substr($database[0], mb_strpos($database[0], ';dbname=') + 8)];
			$callback($e->getCode(), $e->getMessage(), $database);
		}
	}

	/** Build MySQL database InnoDB tables from Doctrine2 entities */
	public function buildFromEntities($usedEntities = []) {
		$destination = static::$settings->netteRoot . '/app/' . (static::$settings->module ? static::$settings->module . 'Module' : '') . '/models/Entities';
		foreach (\Nette\Utils\Finder::findFiles('*.php')->exclude('*.*~')->exclude($usedEntities)->from($destination) as $entity) {
			$metadata[] = static::$settings->entityManager->getClassMetadata('\Kdyby\Doctrine\\' . mb_substr(basename($entity), 0, -4));
		}
		try {
			if (isset($metadata)) (new \Doctrine\ORM\Tools\SchemaTool(static::$settings->entityManager))->createSchema($metadata);
		} catch (\Doctrine\ORM\Tools\ToolsException $e) {
			preg_match("~'(.*)'~", $e->getPrevious()->getPrevious()->getPrevious()->getMessage(), $table);
			if ($e->getPrevious()->getPrevious()->getPrevious()->errorInfo[1] !== 1050) throw $e->getPrevious()->getPrevious()->getPrevious();
			$this->buildFromEntities(array_merge($usedEntities, [implode('', array_map(function($value) {
							return ucfirst($value);
						}, explode('_', $table[1]))) . '.php']));
		}
	}

	/** Check Nette extensions configuration */
	public function checkExtensionsConfiguration() {
		$newInstall = FALSE;
		CLI::write('Verifying extensions configuration:', 1);
		$config = (new \Nette\Neon\Decoder())->decode(\Bruha\Generator\Utils\File::read(static::$settings->netteRoot . '/app/config/config.neon'));
		CLI::write('Kdyby\Translation:', 2, TRUE, FALSE);
		if (!isset($config['extensions']['translation'])) {
			$config['extensions']['translation'] = 'Kdyby\Translation\DI\TranslationExtension';
			CLI::write('NOT INSTALLED', 0, FALSE);
			CLI::write('Installing: SUCCESS', 3);
			$newInstall = TRUE;
		} else CLI::write('INSTALLED', 0, FALSE);
		CLI::write('Kdyby\Replicator:', 2, TRUE, FALSE);
		if (!isset($config['extensions']['replicator'])) {
			$config['extensions']['replicator'] = 'Kdyby\Replicator\DI\ReplicatorExtension';
			CLI::write('NOT INSTALLED', 0, FALSE);
			CLI::write('Installing: SUCCESS', 3);
			$newInstall = TRUE;
		} else CLI::write('INSTALLED', 0, FALSE);
		CLI::write('Kdyby\Annotations:', 2, TRUE, FALSE);
		if (!isset($config['extensions']['annotations'])) {
			$config['extensions']['annotations'] = 'Kdyby\Annotations\DI\AnnotationsExtension';
			CLI::write('NOT INSTALLED', 0, FALSE);
			CLI::write('Installing: SUCCESS', 3);
			$newInstall = TRUE;
		} else CLI::write('INSTALLED', 0, FALSE);
		CLI::write('Kdyby\Console:', 2, TRUE, FALSE);
		if (!isset($config['extensions']['console'])) {
			$config['extensions']['console'] = 'Kdyby\Console\DI\ConsoleExtension';
			CLI::write('NOT INSTALLED', 0, FALSE);
			CLI::write('Installing: SUCCESS', 3);
			$newInstall = TRUE;
		} else CLI::write('INSTALLED', 0, FALSE);
		CLI::write('Kdyby\Events:', 2, TRUE, FALSE);
		if (!isset($config['extensions']['events'])) {
			$config['extensions']['events'] = 'Kdyby\Events\DI\EventsExtension';
			CLI::write('NOT INSTALLED', 0, FALSE);
			CLI::write('Installing: SUCCESS', 3);
			$newInstall = TRUE;
		} else CLI::write('INSTALLED', 0, FALSE);
		CLI::write('Kdyby\Doctrine:', 2, TRUE, FALSE);
		if (!isset($config['extensions']['doctrine'])) {
			$config['extensions']['doctrine'] = 'Kdyby\Doctrine\DI\OrmExtension';
			$database = static::$settings->netteDatabase;
			$config['doctrine'] = ['host' => $database->hostname, 'user' => $database->username, 'password' => $database->password, 'dbname' => $database->database, 'metadata' => ['App' => '%appDir%'], 'dql' => ['string' => ['CONCAT_WS' => 'DoctrineExtensions\Query\Mysql\ConcatWs']]];
			CLI::write('NOT INSTALLED', 0, FALSE);
			CLI::write('Installing: SUCCESS', 3);
			$newInstall = TRUE;
		} else CLI::write('INSTALLED', 0, FALSE);
		\Nette\Utils\FileSystem::write(static::$settings->netteRoot . '/app/config/config.neon', preg_replace('~(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+~', "\n", (new \Nette\Neon\Encoder)->encode($config, \Nette\Neon\Encoder::BLOCK)));
		return $newInstall;
	}
}