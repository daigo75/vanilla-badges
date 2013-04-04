<?php if(!defined('APPLICATION')) exit();
/**
{licence}
*/

// Register Rule with the Rule Manager
AwardRulesManager::RegisterRule(
	'LikesRule',
	array('Label' => T('Likes'),
				'Description' => T('Checks "Likes" received by the User'),
				'Group' => AwardRulesManager::GROUP_GENERAL,
				'Type' => AwardRulesManager::TYPE_CONTENT,
				// Version is for reference only
				'Version' => '13.04.04',
				)
);


/**
 * Likes Award Rule.
 *
 * Assigns an Award based on the Likes received by a User.
 */
class LikesRule extends BaseAwardRule {
	/**
	 * Checks if the User received enough Likes to be assigned an Award based
	 * on such criteria.
	 *
	 * @param int UserID The ID of the User.
	 * @param stdClass Settings The Rule settings.
	 * @return int "1" if check passed, "0" otherwise.
	 */
	private function CheckUserReceivedLikesCount($UserID, stdClass $Settings) {
		$ReceivedLikesThreshold = $Settings->ReceivedLikes->Amount;
		$this->Log()->trace(sprintf(T('Checking count of Received Likes for User ID %d. Threshold: %d.'),
																$UserID,
																$ReceivedLikesThreshold));
		$UserData = $this->GetUserData($UserID);
		//var_dump($UserData);
		if(GetValue('Liked', $UserData, 0) >= $ReceivedLikesThreshold) {
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
	protected function _Process($UserID, stdClass $Settings, array $EventInfo = null) {
		// Check Received Likes Count
		if(GetValue('Enabled', $Settings->ReceivedLikes) == 1) {
			$Results[] = $this->CheckUserReceivedLikesCount($UserID, $Settings);
		}

		//var_dump("LikesRule Result: " . min($Results));
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

		// Check settings for ReceivedLikes threshold
		$ReceivedLikesSettings = GetValue('ReceivedLikes', $Settings);
		$ReceivedLikesThreshold = GetValue('Amount', $ReceivedLikesSettings);
		if(GetValue('Enabled', $ReceivedLikesSettings)) {
			if(empty($ReceivedLikesThreshold) ||
				 !is_numeric($ReceivedLikesThreshold) ||
				 ($ReceivedLikesThreshold <= 0)) {
				$this->Validation->AddValidationResult('ReceivedLikes_Amount',
																							 T('Received Likes threshold must be a positive integer.'));
			}
		}

		return (count($this->Validation->Results()) == 0);
	}

	/**
	 * Checks if the Rule is enabled, based on the settings and other criteria.
	 *
	 * @param stdClass Settings An object containing settings for the Rule.
	 * @return int An integer value indicating if the Rule should is enabled.
	 * Possible return values are:
	 * - BaseAwardRule::RULE_ENABLED
	 * - BaseAwardRule::RULE_DISABLED
	 * - BaseAwardRule::RULE_ENABLED_CANNOT_PROCESS
	 */
	protected function _IsRuleEnabled(stdClass $Settings) {
		if((GetValue('Enabled', $Settings->ReceivedLikes) == 1)) {
			return self::RULE_ENABLED;
		}

		return self::RULE_DISABLED;
	}

	/**
	 * Class constructor.
	 *
	 * @return LikesRule.
	 */
	public function __construct() {
		parent::__construct();

		$this->_RequiredPlugins[] = 'LikeThis';
	}
}
