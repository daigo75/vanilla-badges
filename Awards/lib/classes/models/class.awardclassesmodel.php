<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Award Classes Model
 */

/**
 * This model is used to retrieve the data related to the AwardClasses.
 */
class AwardClassesModel extends ModelEx {
	/**
	 * Defines the related database table name.
	 *
	 */
	public function __construct() {
		parent::__construct('AwardClasses');

		$this->_SetAwardClassesValidationRules();
	}

	/**
	 * Set Validation Rules that apply when saving a new Award Classes.
	 */
	protected function _SetAwardClassesValidationRules() {
		$Validation = new Gdn_Validation();

		// Add extra rules below

		// Set additional Validation Rules here. Please note that formal validation
		// is done automatically by base Model Class, by retrieving Schema
		// Information.

		$this->Validation = $Validation;
	}

	/**
	 * Build SQL query to retrieve the list of configured AwardClasses.
	 */
	protected function PrepareAwardClassesQuery() {
		$Query = $this->SQL
			->Select('VAAC.AwardClassID')
			->Select('VAAC.AwardClassName')
			->Select('VAAC.AwardClassDescription')
			->Select('VAAC.AwardClassImageFile')
			->Select('VAAC.AwardClassCSS')
			->Select('VAAC.DateInserted')
			->Select('VAAC.DateUpdated')
			->Select('VAAC.TotalAwardsUsingClass')
			->From('v_awards_awardclasseslist VAAC');
		return $Query;
	}

	/**
	 * Convenience method to returns a DataSet containing a list of all the
	 * configured AwardClasses.
	 *
	 * @param int Limit Limit the amount of rows to be returned.
	 * @param int Offset Specifies from which rows the data should be returned. Used
	 * for pagination.
	 * @return Gdn_DataSet A DataSet containing AwardClasses data.
	 *
	 * @see AwardClassesModel::GetWhere()
	 */
	public function Get($Limit = 1000, $Offset = 0) {
		return $this->GetWhere(array(), $Limit, $Offset);
	}

	/**
	 * Convenience method to return the data of a single Award Class.
	 *
	 * @param int AwardClassID The ID of the Award Class for which to retrieve the data.
	 * @return Gdn_DataSet A DataSet containing the data of the specified Award Class.
	 */
	public function GetAwardClassData($AwardClassID) {
		return $this->GetWhere(array('AwardClassID' => $AwardClassID));
	}

	/**
	 * Returns a DataSet containing a list of the configured Award Classes.
	 *
	 * @param array WhereClauses An associative array of WHERE clauses. They should
	 * be passed as specified in Gdn_SQLDriver::Where() method.
	 * @param int Limit Limits the amount of rows to be returned.
	 * @param int Offset Specifies from which rows the data should be returned. Used
	 * for pagination.
	 * @return Gdn_DataSet A DataSet containing a list of the configured Award Classes.
	 *
	 * @see Gdn_SQLDriver::Where()
	 */
	public function GetWhere(array $WhereClauses, $Limit = 1000, $Offset = 0) {
		// Set default Limit and Offset, if invalid ones have been passed.
		$Limit = (is_numeric($Limit) && $Limit > 0) ? $Limit : 1000;
		$Offset = (is_numeric($Offset) && $Offset > 0) ? $Offset : 0;

		// Return the Jobs Started within the Date Range.
		$this->PrepareAwardClassesQuery();

		// Add additional WHERE clauses, if any has been passed
		if(!empty($WhereClauses)) {
			$this->SQL->Where($WhereClauses);
		}

		$Result = $this->SQL
			->OrderBy('VAAC.AwardClassName')
			->Limit($Limit, $Offset)
			->Get();

		return $Result;
	}

	/**
	 * Save an Award Class to the database.
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
			$AwardClassID = GetValue($this->PrimaryKey, $FormPostValues, false);

			// See if an Award with the same ID already exists, to decide if the action
			// should be an INSERT or an UPDATE
			$Insert = ($this->GetWhere(array('AwardClassID' => $AwardClassID))->FirstRow() === false);

			// Prepare all the validated fields to be passed to an INSERT/UPDATE query
			$Fields = &$this->Validation->ValidationFields();
			if($Insert) {
				$this->AddInsertFields($Fields);
				$Result = $this->Insert($Fields);
			}
			else {
				$this->AddUpdateFields($Fields);
				$this->Update($Fields, array($this->PrimaryKey => $AwardClassID));
				$Result = $AwardClassID;

				// TODO Delete the configuration of all Rules associated with the Award. New configuration will be saved later.
			}

			// TODO Save the configuration of all Rules associated with the Award

			$this->Database->CommitTransaction();
			return $Result;
		}
		catch(Exception $e) {
			$this->Database->RollbackTransaction();
			$this->Log()->Error(sprintf(T('Exception occurred while saving AwardClasses. Error: %s'),
																$e->getMessage()));
			return false;
		}
	}

	/**
	 * Deletes an Award and its Rule settings from the AwardClasses and AwardRules
	 * tables.
	 *
	 * @param AwardClassID The ID of the Award to be deleted.
	 * @return AwardClasses_OK if Award was deleted successfully, or a numeric error
	 * code if deletion failed.
	 */
	public function Delete($AwardClassID) {
		$this->SQL->Delete('AwardClasses', array('AwardClassID' => $AwardClassID,));
	}
}