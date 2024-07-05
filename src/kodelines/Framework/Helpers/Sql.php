<?php

declare(strict_types=1);

namespace Kodelines\Helpers;

use PDO;
use PDOException;
use Kodelines\Log;
use Kodelines\Error;
use Kodelines\Tools\Str;
use Kodelines\Tools\Number;
use Kodelines\Helpers\Cache;


class Sql
{

	/**
	 * Contiene connessione sql per istanza s
	 */
	private $connection;

	/**
	 * Contiene nome database 
	 */
	private $database;

	/**
	 * Contiene tabelle già parsate da alcune funzioni
	 */
	private $tableCache = [];

	/**
	 * Se settato a true non filtra gli auto increment dagli insert e li inserisce ugualmente
	 */
	public $bulk = false;


	/**
	 * Il costruttore crea connessione
	 *
	 * @param  mixed $server
	 * @param  mixed $dbname
	 * @param  mixed $user
	 * @param  mixed $password
	 * @return void
	 */
	public function __construct(string $server, string $dbname, string $user, string $password)
	{

		try {

			$this->connection = new PDO('mysql:host=' . $server . ';dbname=' . $dbname . ';charset=utf8', $user, $password);

			$this->database = $dbname;

			
			//Questo per fare in modo di sovrascrivere la classe pdo exception per i log custom
			$this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			//Con la cache attiva genero una mappa del database in un array in cache per i controlli vari in modo da non rifare query
			if(config('app','cache') && !$this->tableCache = Cache::getInstance()->getArray($this->database . '_tables')) {

				$this->tableCache = [];

				foreach($this->getArray("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $this->database . "' ") as $tables) {

					$this->tableCache[$tables['TABLE_NAME']] = $this->getColumns($tables['TABLE_NAME']);

				}

				Cache::getInstance()->setArray($this->tableCache,$this->database . '_tables');

			}
		
		} catch (PDOException $e) {
			
			throw new Error('Database Connection Error: ' . $e->getMessage());

		}
	}


	/**
	 * Run a simple Sql Query, if skip errors is true the system does non set a error
	 *
	 * @return bool
	 */
	public function query(string $sql): bool
	{

		try {

			$stmt = $this->connection->prepare($sql);

			//Save Query Log
			new Log('sql', $sql);

			return $stmt->execute();

		} catch (PDOException $e) {

			throw new Error($e->getMessage());

		}
	}



	/**
	 * Inserisce o rimpiazza una coppia chiavi -> valori in una tabella database
	 *
	 * @param  string $table
	 * @param  array $values
	 * @param  string $mode  Può essere INSERT, REPLACE O INSERT IGNORE 
	 * @return bool
	 */
	public function insert(string $table, array $values, string $mode = 'INSERT'): int|bool
	{

		//Parse and check values
		$values = $this->checkData($table, $values);

		/*** snarg the field names from the first array member ***/
		$fieldnames = array_keys($values);

		$sql = $mode . " INTO $table ";
		/*** set the field names ***/
		$fields = '(' . implode(', ', $fieldnames) . ')';
		/*** set the placeholders ***/
		$bound = '(:' . implode(', :', $fieldnames) . ')';
		/*** put the query together ***/
		$sql .= $fields . ' VALUES ' . $bound;

		try {

			/*** prepare and execute ***/
			$stmt = $this->connection->prepare($sql);

			foreach ($values as $key => $val) {
				$stmt->bindValue(':' . $key, $val);
			}

			//Save Query Log
			new Log('sql', $sql, $values);

			if(!$stmt->execute()) {
				return false;
			}

			if($id = $this->lastInsertId()) {
				return $id;
			}

			return true;

		} catch (PDOException $e) {

			throw new Error($e->getMessage());
		}
	}

	/**
	 * Ritorna ultimo id inserito
	 *
	 * @return integer|boolean
	 */
	public function lastInsertId(): int|bool {

		if($id = $this->connection->lastInsertId()) {
			return id($id);
		}

		return false;
	}


	/**
	 * Genera query di update per un id e un singolo valore stile UPDATE $table SET $fieldname = $value WHERE $pk = $id
	 *
	 * @param  string $table 		Tabella
	 * @param  string $fieldname		Nome campo
	 * @param  mixed  $value			Valore
	 * @param  string $pk			Nome Identificativo
	 * @param  mixed  $id			Valore identificativo
	 * @return bool
	 */
	public function update(string $table, string $fieldname, mixed $value, string $pk, mixed $id): bool
	{

		try {

			$sql = "UPDATE `$table` SET `$fieldname`= :value WHERE `$pk` = :id";

			$stmt = $this->connection->prepare($sql);

			$stmt->bindParam(':id', $id, PDO::PARAM_INT);

			$stmt->bindValue(':value', $this->checkField($table, $fieldname, $value));

			//Save Query Log
			new Log('sql', $sql, ['id' => $id]);

			return $stmt->execute();

		} catch (PDOException $e) {

			throw new Error($e->getMessage());
		}
	}


	/**
	 * Genera query di update per un id e valori multipli stile UPDATE $table SET $fieldname = $value, $field2 = $value2 WHERE $pk = $id
	 *
	 * @param  string $table 		Tabella
	 * @param  array  $values		Array nomecampo => valore 
	 * @param  string $pk			Nome Identificativo
	 * @param  mixed  $id			Valore Identificativo
	 * @return bool
	 */
	public function updateArray(string $table, array $values, mixed $pk, mixed $id = null): bool
	{


		try {

			//Parse and check values
			$values = $this->checkData($table, $values);

			$sql = "UPDATE `$table` SET ";

			$counter = 0;

			foreach ($values as $field => $value) {

				if ($counter > 0) {
					$sql .= ',';
				}

				$sql .= $field . " = :" . $field;

				$counter++;
			}

			if ($counter == 0) {
				return false;
			}

			$counter = 0;
			//TODO: sta roba va gestita meglio cosi è grezzo e provare a vedere se è possibile usare qualche libreria slim
			if(is_array($pk) && $id == null) {

				$sql .= " WHERE ";

				foreach ($pk as $field => $value) {

					if ($counter > 0) {
						$sql .= ' AND ';
					}
	
					$sql .= $field . " = :" . $field;
	
					$counter++;
				}

				/*** prepare and execute ***/
				$stmt = $this->connection->prepare($sql);

				foreach ($pk as $field => $value) {
					$stmt->bindValue(':' . $field, $value);
				}


			} else {

				$sql .= " WHERE `$pk` = :id";

				/*** prepare and execute ***/
				$stmt = $this->connection->prepare($sql);

				if(is_integer($id)) {
					$stmt->bindParam(':id', $id, PDO::PARAM_INT);
				} else {
					$stmt->bindParam(':id', $id);
				}
	
				
			}
	

			foreach ($values as $field => $value) {
				$stmt->bindValue(':' . $field, $value);
			}

			//Save Query Log
			new Log('sql', $sql, $values);

			return $stmt->execute();

		} catch (PDOException $e) {

			throw new Error($e->getMessage());
		}
	}


	/**
	 * Cancella riga in base a id o altro campo
	 *
	 * @param  string $table 		Tabella
	 * @param  string $fieldname	Nome Identificativo
	 * @param  mixed  $id			Valore Identificativo
	 * @return bool
	 */
	public function delete(string $table, string $fieldname, mixed $id): bool
	{

		try {

			$sql = "DELETE FROM `$table` WHERE `$fieldname` = :id";

			$stmt = $this->connection->prepare($sql);

			$stmt->bindParam(':id', $id, PDO::PARAM_INT);

			//Save Query Log
			new Log('sql', $sql, ['id' => $id]);

			return $stmt->execute();
		} catch (PDOException $e) {

			throw new Error($e->getMessage());
		}
	}



	/**
	 * Recupera riga database e ritorna array, se in modalità strict e non ritorna una sola riga ma anche 2 o 3 da errore, altrimenti importa solo che trovi almeno una riga
	 *
	 * @param  string $query
	 * @param  bool   $strict
	 * @return bool|array 
	 */
	public function getRow(string $query, bool $strict = true): bool|array
	{

		try {

			//Save Query Log
			new Log('sql', $query);

			$stmt = $this->connection->prepare($query);

			if(!$stmt->execute()) {

				//Save Query Log
				new Log('errors', 'Wrong SQL Query: ' . $query);

				return false;
			}


			//Conto righe e ritorno false in base a modalità strict o no
			if ($stmt->rowCount() == 0 || ($strict == true && $stmt->rowCount() <> 1)) {

				return false;
			}

			$array = $stmt->fetchAll(PDO::FETCH_ASSOC);

			return end($array);

		} catch (PDOException $e) {

			throw new Error($e->getMessage());
		}
	}

	/**
	 * Ritorna un singolo valore da getRow
	 *
	 * @param string $query
	 * @return mixed
	 */
	public function getValue(string $query): mixed {

		if(!$row = $this->getRow($query)) {
			return false;
		}

		return end($row);

	}

	/**
	 * Recupera varie righe database er ritorna array
	 *
	 * @param  string $query
	 * @param  bool   $strict
	 * @return array
	 */
	public function getArray($query): array
	{
	
		try {

			//Save Query Log
			new Log('sql', $query);

			$stmt = $this->connection->prepare($query);

			if(!$stmt->execute()) {

				//Save Query Log
				new Log('errors', 'Wrong SQL Query: ' . $query);

				return false;
			}

			if($stmt->rowCount() == 0) {
				return array();
			}

			return $stmt->fetchAll(PDO::FETCH_ASSOC);

		} catch (PDOException $e) {

			throw new Error($e->getMessage());

		}
	}



	/**
	 * Recupera opzioni campi enum e ritorna array
	 *
	 * @param  string $table
	 * @param  string $field
	 * @return array
	 */
	public function getEnum(string $table, string $field): array
	{

		if (!$array = $this->getRow("SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '" . $this->database . "' AND TABLE_NAME = " . $this->connection->quote($table) . " AND COLUMN_NAME = " . $this->connection->quote($field))) {
			return array();
		}

		$enumList = explode(",", str_replace("'", "", mb_substr($array['COLUMN_TYPE'], 5, (mb_strlen($array['COLUMN_TYPE']) - 6))));

		foreach ($enumList as $value) {
			$return[] = $value;
		}

		return $return;
	}

	/**
	 * Ritorna tipo per campo tabella
	 *
	 * @param  mixed $table
	 * @return array
	 */
	public function getFieldsTypes(string $table): array
	{

		//Get table values and put it on cache
		if (!isset($this->tableCache[$table])) {
			$this->tableCache[$table] = $this->getColumns($table);
		}

		$rows = [];
		
		foreach($this->tableCache[$table] as $row) {

			$rows[$row['name']] = $row['type'];
		}

		return $rows;
	
	}


	/**
	 * Ritorna array di campi colonna di una tabella
	 *
	 * @param  mixed $table
	 * @return array
	 */
	public function getColumns(string $table): array
	{

		if (!empty($this->tableCache[$table])) {
			return $this->tableCache[$table];
		}

		$rows = array();

		foreach($this->getArray("SHOW COLUMNS FROM " . $table) as $row) {
		
			$rows[$row['Field']] =  array('name' => $row['Field'], 'default' => $row['Default'], 'type' => $this->getType($row['Type']));

			//Auto increment colums are not indexed on db to manipulate
			if (isset($row['Extra']) && $row['Extra'] == 'auto_increment') {
				$rows[$row['Field']]['auto_increment'] = true;
			}

		}

		return $rows;
	}



	/**
	 * Ritorna di che formato è un nome campo tabella per inserire valori 
	 *
	 * @param  string $type
	 * @return string
	 */
	public function getType(string $type): string
	{

		$type = mb_strtolower($type);


		if (Str::startsWith($type, 'int') || Str::startsWith($type, 'smallint') || Str::startsWith($type, 'mediumint') || Str::startsWith($type, 'bigint') || Str::startsWith($type, 'bit')) {
			return 'integer';
		}

		if (Str::startsWith($type, 'tinyint')) {
			return 'tinyint';
		}

		if (Str::startsWith($type, 'float')) {
			return 'float';
		}

		if (Str::startsWith($type, 'decimal')) {
			return 'decimal';
		}

		if (Str::startsWith($type, 'json')) {
			return 'json';
		}

		if (Str::startsWith($type, 'timestamp')) {
			return 'timestamp';
		}

		if (Str::startsWith($type, 'enum')) {
			return 'enum';
		}

		if (Str::startsWith($type, 'set')) {
			return 'set';
		}

		if (Str::startsWith($type, 'date')) {
			return 'date';
		}


		return 'string';
	}


	/**
	 * Controlla un valore di inserimento in base al timpo e fa cast del valore
	 *
	 * @param  string $value
	 * @param  string $type
	 * @return mixed
	 */
	public function checkType(mixed $value, string $type): mixed
	{

		//convert null string value to NULL
		if ($value === 'NULL') {
			$value = NULL;
		}

		//Timestamp check
		if ($type == 'timestamp') {

			if (empty($value)) {
				return NULL;
			}

			if ($value == 'now' || $value == 'NOW()') {
				return date("Y-m-d H:i:s");
			}
		}

		//Date Check
		if ($type == 'date') {

			if (empty($value)) {
				return NULL;
			}

			if ($value == 'now' || $value == 'NOW()') {
				return date("Y-m-d");
			}
		}

		//String check
		if ($type == 'string') {

			if (empty($value)) {
				return NULL;
			}
		}

		//Integer Check
		if ($type == 'integer') {

			if ($value === NULL || $value === null || $value === '') {
				return NULL;
			}

			return $value;
		}

		//Integer Check (il tiny int è usato per switch o checkbox)
		if ($type == 'tinyint') {

			if (empty($value)) {
				return 0;
			}

			return 1;
		}

		//String check
		if ($type == 'enum' || $type == 'set') {

			if (empty($value)) {
				return NULL;
			}
		}

		// Check float or decimal convert comma in dot to prevent error
		if ($type == 'float' || $type == 'decimal') {

			if(is_string($value)) {
				$value = str_replace(",", ".", $value);
			} elseif(!empty($value)) {
				$value = Number::format($value);
			}
	

			return floatval($value);
		}

		//Default return the clean value
		return $value;
	}


	/**
	 * Controlla valori prima di inserire in una tabella, pulisce i valori che non sono presenti e converte i tipi
	 * 
	 * @param  string $value
	 * @param  string $type
	 * @return array
	 */
	public function checkData(string $table, array $values): array
	{

		$data = array();

		//Get table values and put it on cache TODO: potrebbe essere creato mega json di mappatura campi del db al posto di rifare sempre le query
		if (!isset($this->tableCache[$table])) {
			$this->tableCache[$table] = $this->getColumns($table);
		}

		//Parse columns
		foreach ($this->tableCache[$table] as $field) {

			//Auto increment fields are inserted only on bulk mode
			if(!empty($field['auto_increment']) && !$this->bulk){
				continue;
			}

			if($field['type'] == 'integer' && Str::startsWith($field['name'],'id')) {
				
				if(isset($values[$field['name']])) {

					if(empty($values[$field['name']]) || $values[$field['name']] == '0') {
						
						$data[$field['name']] = NULL;

					} else {

						$data[$field['name']] = id($values[$field['name']]);
					}

					continue;


				}

				
			}

			//Prezzo float lo formatto di default
			if((Str::startsWith($field['name'],'price_') || Str::startsWith($field['name'],'total_')) && ($field['type'] == 'float' || $field['type'] == 'decimal')) {

				if(isset($data[$field['name']]) && $data[$field['name']] !== NULL) {
					$data[$field['name']] = Price::format($data[$field['name']]);
				}
				
			}

			//Date ins and update are system reserved
			if(($field['name'] == 'date_ins'  || $field['name'] == 'date_update') && !$this->bulk){
				continue;
			}

			//Tinyint is type checkbox, if not set the value is 0, required _edit field on tpl
			if ($field['type'] == 'tinyint' && isset($values[$field['name'] . '_edit'])) {
				if (!isset($values[$field['name']])) {
					$data[$field['name']] = 0;
				} else {
					$data[$field['name']] = 1;
				}

				continue;
			}

			//Set must be converted if is array
			if (($field['type'] == 'set' && array_key_exists($field['name'] . '_edit', $values)) && !$this->bulk){

				if (isset($values[$field['name']]) && is_array($values[$field['name']]) && !empty($values[$field['name']])) {
					$data[$field['name']] = implode(',', array_keys($values[$field['name']]));
				} else {
					$data[$field['name']] = NULL;
				}

				continue;
			}


			//Other types are converted directly
			if (array_key_exists($field['name'], $values)) {

				//Check if data is array
				if (is_array($values[$field['name']])) {
					continue;
				}

				$data[$field['name']] = $this->checkType($values[$field['name']], $field['type']);
			}
		}



		return $data;
	}



	/**
	 * Stessa funzione di checkfield ma controlla campo singolo
	 *
	 * @param  string $table
	 * @param  string $field
	 * @param  mixed $value
	 * @return mixed
	 */
	public function checkField(string $table, string $field, mixed $value): mixed
	{

		//Get table values and put it on cache
		if (!isset($this->tableCache[$table])) {
			$this->tableCache[$table] = $this->getColumns($table);
		}

		if (!isset($this->tableCache[$table][$field])) {
			return false;
		}

		return $this->checkType($value, $this->tableCache[$table][$field]['type']);
	}


	/**
	 * Modifica opzioni di un campo enum
	 *
	 * @param  mixed $table
	 * @param  mixed $field
	 * @param  mixed $options	Opzioni formato ['opzione1','opzione2']
	 * @param  mixed $type
	 * @return mixed
	 */
	public function editEnum(string $table, string $field, array $options = [], string $type = 'ENUM'): mixed
	{

		if (empty($options)) {
			return false;
		}

		$array = array();

		foreach ($options as $value) {
			$array[] = $this->connection->quote($value);
		}

		$options = implode(',', $array);

		return $this->query("ALTER TABLE `" . $table . "` CHANGE COLUMN `" . $field . "` `" . $field . "` " . $type . "(" . $options . ") NULL DEFAULT NULL COLLATE 'utf8mb4_unicode_ci'");
	}

		
	/**
	 * Controlla se una tabella esiste
	 *
	 * @param  mixed $table
	 * @return bool
	 */
	public function tableExists(string $table) : bool 
	{

		//Senza cache fa la query
		if(empty($this->tableCache[$table])) {

			//Se c'è cache attiva non ricontrolla tabelle esistenti, altrimenti fa un get row
			if(config('app','cache')) {
				return false;
			}

			if(!$this->getRow("SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '" . $this->database . "' AND TABLE_NAME = " . $this->connection->quote($table))) {
				return false;
			}

			//Setto comunque tabella in cache provvisoria
			$this->tableCache[$table] = $this->getColumns($table);
		}

		return true;
	}


	/**
	 * Controlla se campo di una tabella esiste
	 *
	 * @param  mixed $table
	 * @return bool
	 */
	public function fieldExists(string $table, string $field) : bool 
	{
	
		//Get table values and put it on cache
		if (!isset($this->tableCache[$table])) {
			$this->tableCache[$table] = $this->getColumns($table);
		}

		if (!isset($this->tableCache[$table][$field])) {
			return false;
		}

		return true;
	}


	
		
	/**
	 * Mette quote su valore per query
	 *
	 * @param  mixed $value
	 * @return mixed
	 */
	public function encode(mixed $value): mixed {

		if(!is_string($value)) {
			return $value;
		}

		return $this->connection->quote($value);
	}
}

?>