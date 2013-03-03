<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Manages the list of all available Rules and provides convenience
 * functions to retrieve the Model, Validation and View for each one.
 */
class AwardRulesManager extends BaseManager {
	// @var array Contains a list of all available Rules.
	private static $Rules = array();

	const GROUP_GENERAL = 'general';
	const GROUP_CUSTOM = 'custom';
	const GROUP_UNSPECIFIED = 'unspecified';

	const TYPE_CONTENT = 'content';
	const TYPE_USER = 'user';
	const TYPE_UNSPECIFIED = 'unspecified';

	public static $RuleGroups = array();
	public static $RuleTypes = array();

	/**
	 * Registers a Rule to the array of available Rules.
	 *
	 * @param string RuleClass The name of the Rule Class.
	 * @param array An associative array of Rule Information.
	 * @throws An Exception if the Rule Class doesn't exist.
	 */
	public static function RegisterRule($RuleClass, array $RuleInfo) {
		self::$Rules[$RuleClass] = $RuleInfo;
		// If a Rule Group was not specified, assign the Rule to the "Unspecified" group
		if(empty(self::$Rules[$RuleClass]['Group'])) {
			self::$Rules[$RuleClass]['Group'] = self::GROUP_UNSPECIFIED;
		}

		// If a Rule Type was not specified, assign the Rule to the "Unspecified" type
		if(empty(self::$Rules[$RuleClass]['Type'])) {
			self::$Rules[$RuleClass]['Type'] = self::TYPE_UNSPECIFIED;
		}
	}

	/**
	 * Returns the Rule Information array associated to a Rule Class.
	 *
	 * @param string RuleClass The Rule Class for which to retrieve the
	 * information.
	 * @return array|null An associative array of Rule Information, or null, if
	 * the Rule Class could not be found.
	 */
	public function GetRuleInfo($RuleClass) {
		return GetValue($RuleClass, self::$Rules, null);
	}

	/**
	 * Getter for Rules property.
	 *
	 * @return array The value of Rules property.
	 */
	public function GetRules() {
		return self::$Rules;
	}

	/**
	 * Returns the instance of a previously loaded Rule.
 	 *
	 * @param string RuleClass The Rule Class for which to retrieve the instance.
	 * @return $RuleClass An instance of the specified Rule Class.
	 * @throws InvalidArgumentException if the Rule Class is not registered.
 	 */
	protected function GetRuleInstance($RuleClass) {
		if(!$this->RuleExists($RuleClass)) {
			$this->Log()->error($ErrorMsg = sprintf(T('Requested instance for invalid class: %s.',
																							$RuleClass)));
			throw new InvalidArgumentException($ErrorMsg);
		}
		// Return the instance stored during the loading of Rule Classes
		return self::$Rules[$RuleClass]['Instance'];
 	}

	/**
	 * Install an Rule Class's auxiliary classes into Vanilla Factories, for
	 * later use.
	 *
	 * @param Rule The Class of the Rule.
	 * @return void.
	 */
	protected function LoadRule($RuleClass) {
		// Instantiate the Rule to have it readily available when required. This will
		// also prevent the need of instantiating the same rule multiple times
		self::$Rules[$RuleClass]['Instance'] = new $RuleClass();
	}

	/**
	 * Install in Vanilla's Factories all auxiliary classes for available Rule
	 * Classes.
	 *
	 * @return void.
	 */
	protected function LoadRules() {
		//var_dump(self::$Rules);
		foreach(self::$Rules as $RuleClass => $RuleInfo) {
			$this->LoadRule($RuleClass);
		}
	}

	/**
	 * Checks if a Rule Class exists in the list of the configured ones.
	 *
	 * @param RuleClass The Rule class to be checked.
	 * @return True if the class exists in the list of configured Rules, False otherwise.
	 */
	function RuleExists($RuleClass) {
		return array_key_exists($RuleClass, self::$Rules);
	}

	/**
	 * Checks if the specified file name is a valid directory (i.e. it is a
	 * directory, but not "." or "..").
	 *
	 * @param string Directory The directory where the file is located.
	 * @param string FileName The file name to check.
	 * @return bool True if the specified FileName is a directory, False if it is
	 * not a directory, or if it is "." or "..".
	 */
	private function IsValidDirectory($Directory, $FileName) {
		return ($FileName !== '.') &&
					 ($FileName !== '..') &&
					 (is_dir($Directory . '/' . $FileName));
	}

	/**
	 * Loads all Rule files found in the specified folder.
	 *
	 * @param string RulesDir The folder where to look for Rule files.
	 * @return bool False, if directory doesn't exist or could not be opened, True
	 * if it exist and could be opened (regardless if any Rule file was loaded).
	 */
	private function LoadRuleFiles($RulesDir) {
		$Handle = opendir($RulesDir);
		if(empty($Handle)) {
			return false;
		}

		// Load all Rule Files, so that they can add themselves to the list of
		// installed Rules
    while($File = readdir($Handle)) {
      if(!is_dir($File) && preg_match('/^class\..+?rule/i', $File) == 1) {
				include_once($RulesDir . '/' . $File);
			}
		}
		closedir($Handle);
		return true;
	}

	/**
	 * Scans the Rules directory for all appender files and loads them, so
	 * that they can add themselves to the list of available appenders.
	 *
	 * @return void.
	 */
	private function LoadRulesDefinitions() {
		$RulesDir = sprintf('%s/rules', AWARDS_PLUGIN_CLASS_PATH);
		$Handle = opendir($RulesDir);
		if(empty($Handle)) {
			return false;
		}

		// Look for subfolders in Rules folder. Each Rule should be stored in its
		// SubFolder.
    while($File = readdir($Handle)) {
			if($this->IsValidDirectory($RulesDir, $File)) {
				$this->LoadRuleFiles($RulesDir . '/' . $File);
			}
		}
		closedir($Handle);
	}

	/**
	 * Renders the Rules List page.
	 *
	 * @param Gdn_Plugin Caller The Plugin which called the method.
	 * @param object Sender Sending controller instance.
	 */
	public function RulesList(Gdn_Plugin $Caller, $Sender) {
		$Sender->SetData('CurrentPath', AWARDS_PLUGIN_RULES_LIST_URL);
		// Prevent non authorised Users from accessing this page
		$Sender->Permission('Plugins.Awards.Manage');

		// TODO Implement Awards Rules List page
		$Sender->Render($this->GetView('awards_ruleslist_view.php'));
	}

	// TODO Document method
	public function ValidateRulesSettings(Gdn_Form $Form) {
		$RulesSettings = &$Form->GetFormValue('Rules');

		if(empty($RulesSettings)) {
			$Form->AddError(T('No Rules configured. Please enable and configure at least ' .
												'one Rule.'));
		}

		$Result = true;
		foreach($RulesSettings as $RuleClass => $Settings) {
			$RuleInstance = $this->GetRuleInstance($RuleClass);

			// Validate Rules settings and add the validation results to the form
			$Result = $Result && $RuleInstance->ValidateSettings($Form, $Settings);
		}

		return $Result;
	}

	// TODO Document method
	public function SaveRulesSettings(Gdn_Form $Form) {
		$RulesSettings = &$Form->GetFormValue('Rules');

		foreach($RulesSettings as $RuleClass => $Settings) {
			$RuleInstance = $this->GetRuleInstance($RuleClass);

			// Validate Rules settings and add the validation results to the form
			if(!$RuleInstance->SaveSettings($Form->GetFormValue('AwardID'), $Settings)) {
				return false;
			}
		}

		return true;
	}


	/**
	 * Constructor. It initializes the class and populates the list of available
	 * Rules.
	 */
	public function __construct() {
		parent::__construct();

		self::$RuleGroups = array(self::GROUP_GENERAL => T('General'),
															self::GROUP_CUSTOM => T('Custom'));

		self::$RuleTypes = array(self::TYPE_CONTENT => T('Content'),
														 self::TYPE_USER => T('User'),
														 self::TYPE_UNSPECIFIED => T('Misc.'));

		$this->LoadRulesDefinitions();
		$this->LoadRules();
	}
}
