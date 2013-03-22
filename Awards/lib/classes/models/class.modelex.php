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
			$this->_Log = LoggerPlugin::GetLogger(get_called_class());
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

	protected function GetPrimaryKeyValue($FormPostValues) {
		return GetValue($this->PrimaryKey, $FormPostValues, false);
	}

	protected function PrimaryKeyExists($PrimaryKeyValue) {
		return ($PrimaryKeyValue !== false);
	}

	/**
   *  Takes a set of form data ($Form->_PostValues), validates them, and
   * inserts or updates them to the datatabase.
   *
   * @param array $FormPostValues An associative array of $Field => $Value pairs that represent data posted
   * from the form in the $_POST or $_GET collection.
   * @param array $Settings If a custom model needs special settings in order to perform a save, they
   * would be passed in using this variable as an associative array.
   * @return unknown
   */
  public function Save($FormPostValues, $Settings = false) {
    // Define the primary key in this model's table.
    $this->DefineSchema();

    // See if a primary key value was posted and decide how to save
    $PrimaryKeyValue = $this->GetPrimaryKeyValue($FormPostValues);

		// If Primary Key does not exist, then it's not valid and an INSERT has to
		// be performed
    $Insert = empty($PrimaryKeyValue) || !$this->PrimaryKeyExists($PrimaryKeyValue);

		// Add special fields, such as DateInserted, DateUpdated, etc. if they are
		// not already populated
    if($Insert) {
      $this->AddInsertFields($FormPostValues);
    }
		else {
      $this->AddUpdateFields($FormPostValues);
    }

    // Validate the form posted values
    if(!$this->Validate($FormPostValues, $Insert) === true) {
			return false;
		}

		$this->Database->BeginTransaction();
		try {
      $Fields = $this->Validation->ValidationFields();
			// Don't try to insert or update the primary key
      $Fields = RemoveKeyFromArray($Fields, $this->PrimaryKey);
      if($Insert === false) {
        $this->Update($Fields, array($this->PrimaryKey => $PrimaryKeyValue));
      }
			else {
        $PrimaryKeyValue = $this->Insert($Fields);
      }
	    return $PrimaryKeyValue;
		}
		catch(Exception $e) {
			$this->Database->RollbackTransaction();
			$this->Log()->error(sprintf(T('Exception occurred while writing to database. Error: %s. Data (JSON): %s.'),
																$e->getMessage(),
																json_encode($Fields)));
			return false;
		}
  }

	public function __construct($TableName) {
		parent::__construct($TableName);
	}
}
