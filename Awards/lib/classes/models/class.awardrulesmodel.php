<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Award Rules Model
 */

/**
 * This model is used to retrieve the data related to the Rules associated
 * with the Award Rules.
 */
class AwardRulesModel extends ModelEx {
	/**
	 * Defines the related database table name.
	 *
	 */
	public function __construct() {
		parent::__construct('AwardRules');

		$this->_SetAwardRulesValidationRules();
	}

	/**
	 * Set Validation Rules that apply when saving a new row in Cron Jobs History.
	 *
	 * @return void
	 */
	protected function _SetAwardRulesValidationRules() {
		$Validation = new Gdn_Validation();

		// Add extra rules below

		// Set additional Validation Rules here. Please note that formal validation
		// is done automatically by base Model Class, by retrieving Schema
		// Information.

		$this->Validation = $Validation;
	}

	/**
	 * Build SQL query to retrieve the list of Award Rules.
	 */
	protected function PrepareAwardRulesQuery() {
		$Query = $this->SQL
			->Select('AR.AwardID')
			->Select('AR.RuleClass')
			->Select('AR.RuleIsEnabled')
			->Select('AR.RuleConfiguration')
			->Select('AR.DateInserted')
			->Select('AR.InsertUserID')
			->Select('AR.DateUpdated')
			->Select('AR.UpdateUserID')
			->From('AwardRules AR');
		return $Query;
	}

	/**
	 * Convenience method to returns a DataSet containing a list of all the
	 * configured Award Rules.
	 *
	 * @param int AwardID The ID of the Award for which to retrieve the Rules.
	 * @param int Limit Limits the amount of rows to be returned.
	 * @param int Offset Specifies from which rows the data should be returned. Used
	 * for pagination.
	 * @return DataSet A DataSet containing a list of the configured Award Rules.
	 *
	 * @see AwardRulesModel::GetWhere()
	 */
	public function GetForAward($AwardID, $Limit = 1000, $Offset = 0) {
		return $this->GetWhere(array('AwardID' => $AwardID), $Limit, $Offset);
	}

	/**
	 * Returns a DataSet containing a list of the configured Award Rules.
	 *
	 * @param array WhereClauses An associative array of WHERE clauses. They should
	 * be passed as specified in Gdn_SQLDriver::Where() method.
	 * @param int Limit Limit the amount of rows to be returned.
	 * @param int Offset Specifies from which rows the data should be returned. Used
	 * for pagination.
	 * @return DataSet A DataSet containing a list of the configured Award Rules.
	 *
	 * @see Gdn_SQLDriver::Where()
	 */
	public function GetWhere(array $WhereClauses, $Limit = 1000, $Offset = 0) {
		// Set default Limit and Offset, if invalid ones have been passed.
		$Limit = (is_numeric($Limit) && $Limit > 0) ? $Limit : 1000;
		$Offset = (is_numeric($Offset) && $Offset > 0) ? $Offset : 0;

		// Return the Jobs Started within the Date Range.
		$this->PrepareAwardRulesQuery();

		// Add additional WHERE clauses, if any has been passed
		if(!empty($WhereClauses)) {
			$this->SQL->Where($WhereClauses);
		}

		$Result = $this->SQL
			->OrderBy('AR.RuleClass')
			->Limit($Limit, $Offset)
			->Get();

		return $Result;
	}

	/**
	 * Save an Award Rule into the database.
	 *
   * @param array $FormPostValues An associative array of $Field => $Value
   * pairs that represent data posted from the form in the $_POST or $_GET
   * collection.
   * @return array The value of the Primary Key of the row that has been saved,
   * or False if the operation could not be completed successfully.
	 */
	public function Save(&$FormPostValues) {
		// Define the primary key in this model's table.
		$this->DefineSchema();

		// Validate posted data
		if(!$this->Validate($FormPostValues)) {
			return false;
		}

		// Get the Award ID and Rule Name posted via the form
		$AwardID = GetValue('AwardID', $FormPostValues, false);
		$RuleClass = GetValue('RuleClass', $FormPostValues, false);

		// See if an Award Rule with the same key already exists, to decide if the action
		// should be an INSERT or an UPDATE
		$Insert = ($this->GetWhere(array('AwardID' => $AwardID,
																		 'RuleClass' => $RuleClass))->FirstRow() === false);

		// Prepare all the validated fields to be passed to an INSERT/UPDATE query
		$Fields = &$this->Validation->ValidationFields();
		if($Insert) {
			$this->AddInsertFields($Fields);
			$this->Insert($Fields);
		}
		else {
			$this->AddUpdateFields($Fields);
			$this->Update($Fields, array($this->PrimaryKey => $AwardID));
		}

		$Result = array($AwardID, $RuleClass);

		return $Result;
	}

	/**
	 * Deletes an Award and its Rule settings from the Award Rules and AwardRules
	 * tables.
	 *
	 * @param AwardID The ID of the Award to be deleted.
	 * @return Award Rules_OK if Award was deleted successfully, or a numeric error
	 * code if deletion failed.
	 */
	public function Delete($AwardID) {
		// TODO Delete the configuration of all Rules associated with the Award.

		$this->SQL->Delete('Award Rules', array('AwardID' => $AwardID,));
	}
}
