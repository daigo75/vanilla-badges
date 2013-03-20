<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Extends base Gdn_Model by adding a Logger to it. This way, plugins that want
 * to use logging capabilities won't have to instantiate the Logger every time.
 */
class ModelEx extends Gdn_Model {
	// @var Logger The Logger used by the class.
	private $_Log;

	/**
	 * Returns the instance of the Logger used by the class.
	 *
	 * @param Logger An instance of the Logger.
	 */
	protected function Log() {
		if(empty($this->_Log)) {
			$this->_Log = LoggerPlugin::GetLogger();
		}

		return $this->_Log;
	}

	/**
	 * Takes an array of ORDER BY clauses and adds them to the class instance's
	 * SQL object.
	 *
	 * @param array OrderByClauses An array of Order By clauses. Each clause can
	 * contain just the field name, or the field and the sort direction (e.g.
	 * "SomeField ASC").
	 */
	protected function SetOrderBy(array $OrderByClauses) {
		foreach($OrderByClauses as $OrderBy) {
			$OrderByParts = array_filter(explode(' ', $OrderBy));

			/* An order by must contain at most two elements: a field name and the
			 * sort direction. If anything else is found, the clause is considered not
			 * valid and, therefore, ignored.
			 */
			if(count($OrderByParts) > 2) {
				$ErrorMsg = sprintf(T('Invalid ORDER BY clause received: "%s". Ignoring clause.'),
														$OrderBy);
				$this->Log()->error($ErrorMsg);
				continue;
			}

			// Add the ORDER BY clause to the SQL
			$Field = array_shift($OrderByParts);
			$Direction = array_shift($OrderByParts);

			$this->SQL->OrderBy($Field, $Direction);
		}
	}

	public function __construct($TableName) {
		parent::__construct($TableName);
	}
}
