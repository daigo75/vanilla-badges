<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * UserAwards Model
 */

/**
 * This model is used to retrieve the data related to the Awards assigned to the
 * Users.
 */
class UserAwardsModel extends ModelEx {
	/**
	 * Defines the related database table name.
	 */
	public function __construct() {
		parent::__construct('UserAwards');

		$this->_SetUserAwardsValidationRules();
	}

	/**
	 * Set Validation Rules that apply when saving a new User Award.
	 */
	protected function _SetUserAwardsValidationRules() {
		$Validation = new Gdn_Validation();

		// Add extra rules below

		// Set additional Validation Rules here. Please note that formal validation
		// is done automatically by base Model Class, by retrieving Schema
		// Information.

		$this->Validation = $Validation;
	}

	/**
	 * Build SQL query to retrieve the list of configured User Awards.
	 */
	protected function PrepareUserAwardsQuery() {
		$Query = $this->SQL
			->Select('VAUAL.UserAwardID')
			->Select('VAUAL.UserID')
			->Select('VAUAL.DateAwarded')
			->Select('VAUAL.AwardedRankPoints')
			->Select('VAUAL.Status')
			->Select('VAUAL.AwardID')
			->Select('VAUAL.AwardName')
			->Select('VAUAL.AwardDescription')
			->Select('VAUAL.Recurring')
			->Select('VAUAL.AwardImageFile')
			->Select('VAUAL.AwardClassName')
			->From('v_awards_userawardslist VAUAL');
		return $Query;
	}

	/**
	 * Convenience method to returns a DataSet containing a list of all the
	 * Awards obtained by Users.
	 *
	 * @param int Limit Limit the amount of rows to be returned.
	 * @param int Offset Specifies from which rows the data should be returned. Used
	 * for pagination.
	 * @param array OrderBy An associative array of ORDER BY clauses. They should
	 * be passed as specified in Gdn_SQLDriver::OrderBy() method.
	 * @return Gdn_DataSet A DataSet containing User Awards data.
	 *
	 * @see UserAwardsModel::GetWhere()
	 */
	public function Get($Limit = 1000, $Offset = 0, array $OrderBy = array()) {
		return $this->GetWhere(array(), $OrderBy, $Limit, $Offset);
	}

	/**
	 * Convenience method to returns a DataSet containing a list of all the
	 * Awards obtained by a specific User.
	 *
	 * @param int UserID The ID of the User.
	 * @param array OrderBy An associative array of ORDER BY clauses. They
	 * should	be passed as specified in Gdn_SQLDriver::OrderBy() method.
	 * @return Gdn_DataSet A DataSet containing Awards data.
	 *
	 * @see UserAwardsModel::GetWhere()
	 */
	public function GetForUser($UserID, array $OrderBy = array()) {
		return $this->GetWhere(array('VAUAL.UserID' => $UserID), $OrderBy);
	}

	/**
	 * Convenience method to retrieve the details of a single Award eanred by a
	 * User.
	 *
	 * @param int UserID The ID of the User.
	 * @param int AwardID The ID of the Award.
	 * @param array OrderBy An associative array of ORDER BY clauses. They
	 * should	be passed as specified in Gdn_SQLDriver::OrderBy() method.
	 * @return Gdn_DataSet A DataSet containing Awards data.
	 *
	 * @see UserAwardsModel::GetWhere()
	 */
	public function GetUserAwardData($UserID, $AwardID) {
		return $this->GetWhere(array('VAUAL.UserID' => $UserID,
																 'VAUAL.AwardID' => $AwardID))
								->FirstRow();
	}

	/**
	 * Retrieves the last Users who received an Award, together with some User
	 * details.
	 *
	 * @param int AwardID The ID of the Award.
	 * @param array OrderBy An associative array of ORDER BY clauses. They
	 * should	be passed as specified in Gdn_SQLDriver::OrderBy() method.
	 * @param int Limit The maximum amount of rows to return.
	 * @return Gdn_DataSet A DataSet containing Awards data.
	 *
	 * @see UserAwardsModel::GetWhere()
	 */
	public function GetRecentAwardRecipients($AwardID, array $OrderBy = array(), $Limit = 1000) {
		/* Add a bunch of fields related to the Users before calling GetWhere(). Even
		 * though all these clauses are specified here, in a seemingly "random" way,
		 * the SQL Builder will sort them out and build a proper query.
		 */
		$this->SQL
			->Select('U.Name')
			->Select('U.Photo')
			->Select('U.Email')
			->Select('U.Gender')
			->Join('User U', '(U.UserID = VAUAL.UserID)', 'inner');

		// Run the query and return the result;
		return $this->GetWhere(array('AwardID' => $AwardID),
													 $OrderBy,
													 $Limit);
	}

	/**
	 * Returns a DataSet containing a list of the Awards earned by a User.
	 *
	 * @param array WhereClauses An associative array of WHERE clauses. They should
	 * be passed as specified in Gdn_SQLDriver::Where() method.
	 * @param array OrderByClauses An associative array of ORDER BY clauses. They
	 * should	be passed as specified in Gdn_SQLDriver::OrderBy() method.
	 * @param int Limit Limits the amount of rows to be returned.
	 * @param int Offset Specifies from which rows the data should be returned. Used
	 * for pagination.
	 * @return Gdn_DataSet A DataSet containing a list of the configured Awards.
	 *
	 * @see Gdn_SQLDriver::Where()
	 */
	public function GetWhere(array $WhereClauses, array $OrderByClauses = array(), $Limit = 1000, $Offset = 0) {
		// Set default Limit and Offset, if invalid ones have been passed.
		$Limit = (is_numeric($Limit) && $Limit > 0) ? $Limit : 1000;
		$Offset = (is_numeric($Offset) && $Offset > 0) ? $Offset : 0;

		// Prepare the base query
		$this->PrepareUserAwardsQuery();

		// Add additional WHERE clauses, if any has been passed
		if(!empty($WhereClauses)) {
			$this->SQL->Where($WhereClauses);
		}

		//var_dump($this->SQL->GetSelect());die();

		// Add ORDER BY clauses, if any has been passed
		if(!empty($OrderByClauses)) {
			$this->SetOrderBy($OrderByClauses);
		}

		$Result = $this->SQL
			->Limit($Limit, $Offset)
			->Get();

		return $Result;
	}

	/**
	 * Save a User Award to the database.
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
			// Get the User Award ID posted via the form
			$UserAwardID = GetValue($this->PrimaryKey, $FormPostValues, false);

			// See if a User Award with the same ID already exists, to decide if the action
			// should be an INSERT or an UPDATE
			$Insert = ($this->GetWhere(array('UserAwardID' => $UserAwardID))->FirstRow() === false);

			//var_dump('INSERT', $Insert); die();

			// Prepare all the validated fields to be passed to an INSERT/UPDATE query
			$Fields = &$this->Validation->ValidationFields();
			if($Insert) {
				$this->AddInsertFields($Fields);
				$Result = $this->Insert($Fields);
			}
			else {
				$this->AddUpdateFields($Fields);
				$this->Update($Fields, array($this->PrimaryKey => $UserAwardID));
				$Result = $UserAwardID;
			}

			$this->Database->CommitTransaction();
			return $Result;
		}
		catch(Exception $e) {
			$this->Database->RollbackTransaction();
			$this->Log()->Error(sprintf(T('Exception occurred while saving User Award. Error: %s'),
																$e->getMessage()));
			return false;
		}
	}

	/**
	 * Deletes a User Award.
	 *
	 * @param UserAwardID The ID of the User Award to be deleted.
	 */
	public function Delete($UserAwardID) {
		// TODO Transform physical deletion into a logical one
		$this->SQL->Delete('UserAwards', array('UserAwardID' => $UserAwardID,));
	}
}
