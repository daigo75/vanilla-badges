<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

// Register Rule with the Rule Manager
AwardRulesManager::RegisterRule('PostCountRule',
																array('Label' => T('Post Count'),
																			'Description' => T('Checks User\'s Post count'),
																			'Type' => AwardRulesManager::TYPE_CONTENT,
																			'Group' => AwardRulesManager::GROUP_GENERAL,
																			)
																);


/**
 * Post Count Award Rule.
 */
class PostCountRule extends BaseAwardRule {
	/* @var array Contains the types of calculation available for the Rule
	 * - At: Rule returns true when the specified threshold is reached.
	 * - Every: Rule returns true whenever the specified amount of content type is reached.
	 *
	 * Note: variable cannot be initialized on declaration because it makes use of
	 * T() for translations.
	 */
	public static $CountTypes;

	private function GetUserData($UserID) {
		if(!isset($this->_UserData)) {
			$UserModel = new UserModel();

			$this->_UserData = $UserModel->GetID($UserID);
		}

		return $this->_UserData;
	}

	/**
	 * Checks if the User posted enough Discussions to be assigned an Award based
	 * on such criteria.
	 *
	 * @param int UserID The ID of the User.
	 * @param stdClass RuleConfig The Rule configuration.
	 * @return int "1" if check passed, "0" otherwise.
	 */
	private function CheckUserDiscussionsCount($UserID, stdClass $RuleConfig) {
		$DiscussionsThreshold = $RuleConfig->Discussions->Amount;
		$this->Log()->trace(sprintf(T('Checking "CountDiscussions" for User ID %d. Threshold: %d.'),
																$UserID,
																$DiscussionsThreshold));
		//var_dump($this->GetUserData($UserID));
		if($this->GetUserData($UserID)->CountDiscussions >= $DiscussionsThreshold) {
			$this->Log()->trace(T('Passed.'));
			return self::ASSIGN_ONE;
		}
		$this->Log()->trace(T('Failed.'));
		return self::NO_ASSIGNMENTS;
	}

	/**
	 * Checks if the User posted enough Comments to be assigned an Award based
	 * on such criteria.
	 *
	 * @param int UserID The ID of the User.
	 * @param stdClass RuleConfig The Rule configuration.
	 * @return int "1" if check passed, "0" otherwise.
	 */
	private function CheckUserCommentsCount($UserID, stdClass $RuleConfig) {
		$CommentsThreshold = $RuleConfig->Comments->Amount;
		$this->Log()->trace(sprintf(T('Checking "CountComments" for User ID %d. Threshold: %d.'),
																$UserID,
																$CommentsThreshold));
		//var_dump($this->GetUserData($UserID));
		if($this->GetUserData($UserID)->CountComments >= $CommentsThreshold) {
			$this->Log()->trace(T('Passed.'));
			return self::ASSIGN_ONE;
		}
		$this->Log()->trace(T('Failed.'));
		return self::NO_ASSIGNMENTS;
	}

	/**
	 * Runs the processing of the Rule, which will return how many times the Award
	 * should be assigned to the User, based on the specified configuration.
	 *
	 * @see AwardBaseRule::Process().
	 */
	public function Process($UserID, $RuleConfig, array $EventInfo = null) {
		if(!$RuleConfig->RuleIsEnabled) {
			return null;
		}

		$Results = array();
		// Check Discussion Count
		if(GetValue('Enabled', $RuleConfig->Discussions) == 1) {
			$Results[] = $this->CheckUserDiscussionsCount($UserID, $RuleConfig);
		}

		// Check Comment Count
		if(GetValue('Enabled', $RuleConfig->Comments) == 1) {
			$Results[] = $this->CheckUserCommentsCount($UserID, $RuleConfig);
		}

		var_dump("PostCountRule Result: " . min($Results));
		return min($Results);
	}

	/**
	 * Validates Rule's settings.
	 *
	 * @param array Settings The array of settings to validate.
	 * @return bool True, if all settings are valid, False otherwise.
	 */
	protected function _ValidateSettings(array $Settings) {
		$Result = array();

		// Check settings for Discussions threshold
		$DiscussionsSettings = GetValue('Discussions', $Settings);
		$DiscussionsThreshold = GetValue('Amount', $DiscussionsSettings);
		if(GetValue('Enabled', $DiscussionsSettings) || !empty($DiscussionsThreshold)) {
			if(!is_numeric($DiscussionsThreshold) || ($DiscussionsThreshold <= 0)) {
				$this->Validation->AddValidationResult(self::RenameRuleField('Discussions_Amount'),
																							 T('Discussions threshold must be a positive integer.'));
			}
		}

		// Check settings for Comments  threshold
		$CommentsSettings = GetValue('Comments', $Settings);
		$CommentsThreshold = GetValue('Amount', $CommentsSettings);
		if(GetValue('Enabled', $CommentsSettings) || !empty($CommentsThreshold)) {
			if(!is_numeric($CommentsThreshold) || ($CommentsThreshold <= 0)) {
				$this->Validation->AddValidationResult(self::RenameRuleField('Comments_Amount'),
																							 T('Comments threshold must be a positive integer.'));
			}
		}
		return (count($this->Validation->Results()) == 0);
	}

	protected function IsRuleEnabled(array $Settings) {
		return GetValue('Discussions', $Settings) || GetValue('Comments', $Settings);
	}

	/**
	 * Class constructor.
	 *
	 * @return PostCountRule.
	 */
	public function __construct() {
		parent::__construct();
		self::$CountTypes = array(1 => T('At'),
															2 => T('Every'),);
	}

	// TODO Add Model (if needed) to perform Rule checks
}
