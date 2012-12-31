<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Awards Model
 */

/**
 * This model is used to retrieve the data related to the Awards.
 */
class AwardsModel extends ModelEx {
	/**
	 * Defines the related database table name.
	 *
	 */
	public function __construct() {
		parent::__construct('Awards');

		$this->_SetAwardsValidationRules();
	}

	/**
	 * Set Validation Rules that apply when saving a new row in Cron Jobs History.
	 *
	 * @return void
	 */
	protected function _SetAwardsValidationRules() {
		$Validation = new Gdn_Validation();

		// Add extra rules below

		// Set additional Validation Rules here. Please note that formal validation
		// is done automatically by base Model Class, by retrieving Schema
		// Information.

		$this->Validation = $Validation;
	}

	/**
	 * Build SQL query to retrieve the list of Awards.
	 */
	protected function PrepareAwardsQuery() {
		$Query = $this->SQL
			->Select('VAAL.AwardID')
			->Select('VAAL.ClassID')
			->Select('VAAL.AwardName')
			->Select('VAAL.Description')
			->Select('VAAL.Recurring')
			->Select('VAAL.IsEnabled')
			->Select('VAAL.AwardImage')
			->Select('VAAL.RankPoints')
			->Select('VAAL.DateInserted')
			->Select('VAAL.DateUpdated')
			->Select('VAAL.RuleName')
			->Select('VAAL.RuleConfiguration')
			->Select('VAAL.AwardClassName')
			->Select('VAAL.BackgroundImageFile AS AwardClassBGImage')
			->From('v_awards_awardslist VAAL');
		return $Query;
	}

	/**
	 * Convenience method to returns a DataSet containing a list of all the
	 * configured Awards.
	 *
	 * @param Limit Limit the amount of rows to be returned.
	 * @param Offset Specifies from which rows the data should be returned. Used
	 * for pagination.
	 * @return A DataSet containing a list of the configured Awards.
	 *
	 * @see AwardsModel::GetWhere()
	 */
	public function Get($Limit = 1000, $Offset = 0) {
		return $this->GetWhere(NULL, $Limit, $Offset);
	}

	/**
	 * Returns a DataSet containing a list of the configured Awards.
	 *
	 * @param array WhereClauses An associative array of WHERE clauses. They should
	 * be passed as specified in Gdn_SQLDriver::Where() method.
	 * @param int Limit the amount of rows to be returned.
	 * @param int Offset Specifies from which rows the data should be returned. Used
	 * for pagination.
	 * @return A DataSet containing a list of the configured Awards.
	 *
	 * @see Gdn_SQLDriver::Where()
	 */
	public function GetWhere(array $WhereClauses, $Limit = 1000, $Offset = 0) {
		// Set default Limit and Offset, if invalid ones have been passed.
		$Limit = (is_numeric($Limit) && $Limit > 0) ? $Limit : 1000;
		$Offset = (is_numeric($Offset) && $Offset > 0) ? $Offset : 0;

		// Return the Jobs Started within the Date Range.
		$this->PrepareAwardsQuery();

		// Add additional WHERE clauses, if any has been passed
		if(!empty($WhereClauses)) {
			$this->SQL->Where($WhereClauses);
		}

		$Result = $this->SQL
			->OrderBy('VAAL.AwardName')
			->Limit($Limit, $Offset)
			->Get();

		return $Result;
	}

	/**
	 * Save an Awards into the database.
	 *
   * @param array $FormPostValues An associative array of $Field => $Value
   * pairs that represent data posted from the form in the $_POST or $_GET
   * collection.
   * @return The value of the Primary Key of the row that has been saved, or
   * False if the operation could not be completed successfully.
	 */
	public function Save(&$FormPostValues) {
		// Define the primary key in this model's table.
		$this->DefineSchema();

		// Validate posted data
		if(!$this->Validate($FormPostValues)) {
			return false;
		}

		$this->Database->BeginTransaction();

		try {
			// Get the Award ID posted via the form
			$AwardID = GetValue($this->PrimaryKey, $FormPostValues, false);

			// See if an Award with the same ID already exists, to decide if the action
			// should be an INSERT or an UPDATE
			$Insert = ($this->GetWhere(array('AwardID' => $AwardID))->FirstRow() === false);

			// Prepare all the validated fields to be passed to an INSERT/UPDATE query
			$Fields = &$this->Validation->ValidationFields();
			if($Insert) {
				$this->AddInsertFields($Fields);
				$Result = $this->Insert($Fields);
			}
			else {
				$this->AddUpdateFields($Fields);
				$this->Update($Fields, array($this->PrimaryKey => $AwardID));
				$Result = $AwardID;

				// TODO Delete the configuration of all Rules associated with the Award. New configuration will be saved later.
			}

			// TODO Save the configuration of all Rules associated with the Award

			$this->Database->CommitTransaction();
			return $Result;
		}
		catch(Exception $e) {
			$this->Database->RollbackTransaction();
			$this->Log->Error(sprintf(T('Exception occurred while saving Awards. Error: %s'),
																$e->getMessage()));
			return false;
		}
	}

	/**
	 * Deletes an Award and its Rule settings from the Awards and AwardRules
	 * tables.
	 *
	 * @param AwardID The ID of the Award to be deleted.
	 * @return AWARDS_OK if Award was deleted successfully, or a numeric error
	 * code if deletion failed.
	 */
	public function Delete($AwardID) {
		// TODO Delete the configuration of all Rules associated with the Award.

		$this->SQL->Delete('Awards', array('AwardID' => $AwardID,));
	}
}
