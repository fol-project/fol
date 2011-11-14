<?php
namespace Fol;

class Database extends \PDO {
	public $settings;



	/**
	 * public function __construct (array $settings)
	 *
	 * Returns none
	 */
	public function __construct ($settings) {
		$this->settings = $settings;

		if (!in_array($settings['driver'], parent::getAvailableDrivers())) {
			return exception('This database driver is not supported');
		}

		if ($this->settings['driver'] === 'mysql') {
			$dsn = 'mysql:host='.$this->settings['host'].';dbname='.$this->settings['database'];
			$options = array(parent::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'");
		}

		try {
			parent::__construct($dsn, $this->settings['user'], $this->settings['password'], $options);
		} catch (\PDOException $e) {
			return exception('Connection failed: '.$e->getMessage());
		}

		$this->setAttribute(parent::ATTR_DEFAULT_FETCH_MODE, parent::FETCH_ASSOC);
	}



	/**
	 * function generateSelectQuery (array $select)
	 *
	 * Generates a SELECT query string
	 * Returns string/false
	 */
	public function generateSelectQuery ($select) {
		$q_fields = $q_tables = $q_where = $q_join = array();

		foreach ($select['data'] as $table => $fields) {
			if (is_int($table)) {
				$q_fields[] = $fields;
				continue;
			}

			list($real_name, $new_name) = $this->name($table);

			$q_fields = array_merge($this->fields($fields, $new_name ?: $real_name), $q_fields);

			$q_tables[] = $new_name ? ('`'.$real_name.'` AS `'.$new_name.'`') : ('`'.$real_name.'`');
		}

		if (!$q_fields) {
			return false;
		}

		$query = 'SELECT '.implode(', ', $q_fields).' FROM ('.implode(', ', $q_tables).')';

		if ($select['where'] && ($where = $this->where($select['where']))) {
			$query .= ' WHERE '.$where;
		}

		$query .= $this->group($select['group']);
		$query .= $this->order($select['order']);
		$query .= $this->limit($select['offset'], $select['limit']);

		return $query;
	}




	/**
	 * public function generateInsertQuery (array $data)
	 *
	 * Generates a INSERT query string
	 * Returns false/string
	 */
	public function generateInsertQuery ($data) {
		if (!$data['data'] || !$data['table']) {
			return false;
		}

		if (!isNumericalArray($data['data'])) {
			$data['data'] = array($data['data']);
		}

		$fields = $q_values = array();

		foreach ($data['data'] as $row) {
			$fields = array_merge($fields, array_keys($row));
		}

		$fields = array_unique($fields);
		$q_fields = '(`'.implode('`, `', $fields).'`)';

		foreach ($data['data'] as $row) {
			$value_row = array();

			foreach ($fields as $field) {
				$value_row[] = $this->quote($row[$field]);
			}

			$q_values[] = '('.implode(', ', $value_row).')';
		}

		$q_values = implode(', ', $q_values);

		return 'INSERT INTO `'.$data['table'].'` '.$q_fields.' VALUES '.$q_values.';';
	}



	/**
	 * public function generateUpdateQuery (array $data)
	 *
	 * Generates an UPDATE query string
	 * Returns string
	 */
	public function generateUpdateQuery ($data) {
		if (!$data['data'] || !$data['table']) {
			return false;
		}

		$query = 'UPDATE `'.$data['table'].'` SET ';

		foreach ($data['data'] as $field => $value) {
			if (substr($field, -1) === '&') {
				$query .= '`'.trim(substr($field, 0, -1)).'` = '.$value.', ';
			} else {
				$query .= '`'.$field.'` = '.$this->quote($value).', ';
			}
		}

		$query = substr($query, 0, -2);

		if ($data['where'] && ($where = $this->where($data['where']))) {
			$query .= ' WHERE '.$where;
		}

		$query .= $this->order($data['order']);
		$query .= $this->limit($data['offset'], $data['limit']);

		return $query;
	}



	/**
	 * public function generateDeleteQuery (array $data)
	 *
	 * Generates a DELETE query string
	 * Returns string
	 */
	public function generateDeleteQuery ($data) {
		if (!$data['table']) {
			return false;
		}

		if (!$data['where'] && !$data['limit']) {
			return 'TRUNCATE `'.$data['table'].'`;';
		}

		$query = 'DELETE FROM `'.$data['table'].'`';

		if ($select['where'] && ($where = $this->where($select['where']))) {
			$query .= ' WHERE '.$where;
		}

		$query .= $this->order($select['order']);
		$query .= $this->limit($select['offset'], $select['limit']);

		return $this->affectedRows();
	}



	/**
	 * public function getScheme ()
	 *
	 * Generate a database scheme array
	 * Returns array
	 */
	public function getScheme () {
		$scheme = array();

		$statement = $this->query('SHOW TABLES');
		$statement->execute();

		foreach ($statement->fetchAll(parent::FETCH_COLUMN) as $table) {
			$describe = $this->query('DESCRIBE `'.$table.'`;');
			$describe->execute();
			$scheme[$table]['columns'] = $describe->fetchAll();

			$indexes = $this->query('SHOW INDEX FROM `'.$table.'`;');
			$indexes->execute();
			$indexes_list = array();

			foreach ($indexes->fetchAll() as $index) {
				if (!isset($indexes_list[$index['Key_name']])) {
					$indexes_list[$index['Key_name']] = array(
						'Key_name' => $index['Key_name'],
						'Non_unique' => $index['Non_unique'],
						'Index_type' => $index['Index_type'],
						'Columns' => array(
							$index['Seq_in_index'] => array(
								'Column_name' => $index['Column_name'],
								'Sub_part' => $index['Sub_part']
							)
						)
					);

					continue;
				}

				$indexes_list[$index['Key_name']]['columns'][$index['Seq_in_index']] = array(
					'Column_name' => $index['Column_name'],
					'Sub_part' => $index['Sub_part']
				);
			}

			$scheme[$table]['indexes'] = $indexes_list;
		}

		return $scheme;
	}



	/**
	 * public function generateUpdateSchemeQuery ($new_scheme)
	 *
	 * Generate a scheme query string
	 * Returns array
	 */
	public function generateUpdateSchemeQuery ($new_scheme) {
		$query = array();

		$old_scheme = $this->getScheme();

		$merged_scheme = array_unique(array_merge(array_keys($old_scheme), array_keys($new_scheme)));

		foreach ($merged_scheme as $table) {
			if (!$old_scheme[$table]) {
				echo $table.'ola';
				$columns = array();

				foreach ($new_scheme[$table]['columns'] as $column) {
					$columns[] = '`'.$column['Field'].'` '.$this->columnScheme($column);
				}

				$query[] = 'CREATE TABLE `'.$table.'` ('.implode(', ', $columns).') ENGINE=MyISAM DEFAULT CHARSET=utf8;';
				continue;
			}

			if (!$new_scheme[$table]) {
				$query[] = 'DROP TABLE `'.$table.'`;';
				continue;
			}

			$merged_columns = array_unique(array_merge(array_keys($old_scheme[$table]['columns']), array_keys($new_scheme[$table]['columns'])));

			foreach ($merged_columns as $column) {
				if (!$old_scheme[$table]['columns'][$column]) {
					$query[] = 'ALTER TABLE `'.$table.'` ADD `'.$column.'` '.$this->columnScheme($new_scheme[$table]['columns'][$column]);
					continue;
				}

				if (!$new_scheme[$table]['columns'][$column]) {
					$query[] = 'ALTER TABLE `'.$table.'` DROP `'.$column.'`';
					continue;
				}

				if (array_diff_assoc($new_scheme[$table]['columns'][$column], $old_scheme[$table]['columns'][$column])) {
					$query[] = 'ALTER TABLE `'.$table.'` MODIFY `'.$column.'` '.$this->columnScheme($new_scheme[$table]['columns'][$column]);
				}
			}

			$merged_indexes = array_unique(array_merge(array_keys($old_scheme[$table]['indexes']), array_keys($new_scheme[$table]['indexes'])));

			foreach ($merged_indexes as $index) {
				if (!$old_scheme[$table]['indexes'][$index]) {
					if ($index['Key_name'] === 'PRIMARY') {
						$query[] = 'ALTER TABLE `'.$table.'` ADD PRIMARY KEY `'.$index['Key_name'].'` (`'.$index['Columns'][1]['Column_name'].'`)';
						continue;
					}

					$columns = array();

					foreach ($index['Columns'] as $column) {
						$columns[] = '`'.$column['Column_name'].'`'.($colum['Sub_part'] ? ' ('.$colum['Sub_part'].')' : '');
					}

					if ($index['Non_unique'] === 1) {
						$query[] = 'ALTER TABLE `'.$table.'` ADD INDEX `'.$index['Key_name'].'` ('.implode(',', $columns).')';
						continue;
					}
					
					$query[] = 'ALTER TABLE `'.$table.'` ADD UNIQUE `'.$index['Key_name'].'` ('.implode(',', $columns).')';
					continue;
				}

				if (!$new_scheme[$table]['indexes'][$index]) {
					if ($index['Key_name'] === 'PRIMARY') {
						$query[] = 'ALTER TABLE `'.$table.'` DROP PRIMARY KEY `'.$index['Key_name'].'` (`'.$index['Columns'][1]['Column_name'].'`)';
						continue;
					}

					$query[] = 'ALTER TABLE `'.$table.'` DROP INDEX `'.$index['Key_name'].'`';
				}
			}
		}

		return $query;
	}



	/**
	 * private function columnScheme (array $settings)
	 *
	 * Creates a definition column
	 * Returns string
	 */
	private function columnScheme ($settings) {
		$query = $settings['Type'];

		$query .= ($settings['Null'] === 'YES') ? ' NULL' : ' NOT NULL';

		if ($settings['Extra'] === 'auto_increment') {
			$query .= ' AUTO_INCREMENT PRIMARY KEY';
		} else if ($settings['default']) {
			$query .= ' default "'.$settings['default'].'"';
		}

		return $query;
	}



	/**
	 * private function fields (array $fields, string $table)
	 *
	 * Creates the array with select fields
	 * Returns array
	 */
	private function fields ($fields, $table) {
		$q_fields = array();

		foreach ($fields as $field) {
			list($real, $new) = $this->name($field);

			if ($new) {
				$q_fields[] = '`'.$table.'`.`'.$real.'` AS `'.$new.'`';
			} else {
				$q_fields[] = '`'.$table.'`.`'.$real.'`';
			}
		}

		return $q_fields;
	}



	/**
	 * private function where (array $where)
	 *
	 * Create a WHERE string
	 * Returns string
	 */
	private function where ($where, $join = 'AND') {
		$q = '';

		foreach ($where as $key => $value) {
			if (is_int($key)) {
				if (is_array($value)) {
					$q .= ' ('.trim($this->where($value, ($join === 'AND') ? 'OR' : 'AND')).') '.$join;
					continue;
				} else {
					$q .= ' '.$value.' '.$join;
				}

				continue;
			}

			preg_match('/^([0-9]+ )?([^\s]+)\s?([^&]*)(&)?$/', $key, $matches);

			$field = trim($matches[2]);
			$operator = trim($matches[3]);
			$literal = $matches[4];

			if (empty($field)) {
				continue;
			}

			if (strpos($field, '.')) {
				$field = '`'.str_replace('.', '`.`', $field).'`';
			} else {
				$field = '`'.$field.'`';
			}

			if (!$literal) {
				$value = $this->quote($value);
			}

			switch ($operator) {
				case '':
				case '=':
				case 'IN':
					if (is_array($value)) {
						if (count($value) > 1) {
							$q .= ' '.$field.' IN ('.implode(',', array_unique($value)).')';
						} else {
							$q .= ' '.$field.' = '.current($value);
						}
					} else {
						$q .= ' '.$field.' = '.$value;
					}
					break;

				case '!=':
				case 'NOT':
					if (is_array($value)) {
						if (count($value) > 1) {
							$q .= ' '.$field.' NOT IN ('.implode(',', array_unique($value)).')';
						} else {
							$q .= ' '.$field.' != '.current($value);
						}
					} else {
						$q .= ' '.$field.' != '.$value;
					}
					break;

				case 'BETWEEN':
					$q .= ' '.$field.' BETWEEN '.$value[0].' AND '.$value[1];
					break;

				case '>':
				case '>=':
				case '<':
				case '<=':
				case 'LIKE':
				case 'NOT LIKE':
				case 'REGEXP':
					$q .= ' '.$field.' '.$operator.' '.$value;
					break;

				case 'IS NULL':
					$q .= ' '.$field.' IS NULL';
					break;
			}

			$q .= ' '.$join;
		}

		$q = trim($q);

		return substr($q, 0, strrpos($q, ' '));
	}



	/**
	 * private function group (array/string $group)
	 *
	 * Creates the GROUP string
	 * Returns string
	 */
	private function group ($group) {
		if ($group) {
			$group = (array)$group;

			foreach ($group as &$group_value) {
				$group_value = preg_replace('/^([^\.]+)\.([^\s]+)/', '`$1`.`$2`', $group_value);
			}

			return ' GROUP BY '.implode(', ', $group);
		}

		return '';
	}



	/**
	 * private function order (array/string $order)
	 *
	 * Creates the ORDER string
	 * Returns string
	 */
	private function order ($order) {
		if ($order) {
			$order = (array)$order;

			foreach ($order as &$order_value) {
				$order_value = preg_replace('/^([^\.]+)\.([^\s]+)/', '`$1`.`$2`', $order_value);
			}

			return ' ORDER BY '.implode(', ', $order);
		}

		return '';
	}



	/**
	 * private function limit (int $offset, int $limit)
	 *
	 * Creates the LIMIT string
	 * Returns string
	 */
	private function limit ($offset, $limit) {
		if ($limit) {
			if ($offset) {
				return ' LIMIT '.intval($offset).', '.intval($limit);
			}

			return ' LIMIT '.intval($limit);
		}

		return '';
	}



	/**
	 * private function name (string $name)
	 *
	 * Extract the real and new name
	 * Returns false/array
	 */
	private function name ($name) {
		if (strpos($table, '[')) {
			preg_match_all('/[\w-]+/', $name, $matches);

			return $matches[0];
		}

		return array($name);
	}



	/**
	 * public function quote (string/int/array $values)
	 *
	 * Add quotes and escapes the special characters in a string
	 * Returns mixed
	 */
	public function quote ($values) {
		if (is_int($values)) {
			return $values;
		}

		if (is_array($values)) {
			foreach ($values as $key => $value) {
				$values[$key] = $this->quote($value);
			}

			return $values;
		}

		return parent::quote(str_replace(array("\r\n", "\r"), "\n", trim($values)));
	}
}
?>