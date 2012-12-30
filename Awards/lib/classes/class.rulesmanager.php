<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

/**
 * Manages the list of all available Rules and provides convenience
 * functions to retrieve the Model, Validation and View for each one.
 */
class RulesManager {
	private $Log;
	// @var array Contains a list of all available Rules.
	public static $Rules = array();

	/**
	 * Install an Rule Class's auxiliary classes into Vanilla Factories, for
	 * later use.
	 *
	 * @param Rule The Class of the Rule.
	 * @return void.
	 */
	protected function InstallRule($RuleClass) {
		// Install Rule's Model and Validation class names into Vanilla
		// built-in factory. This will allow to leverage Vanilla's mechanisms for
		// the management of Singletons
		$ConfigModelClass = $this->GetConfigModelClass($RuleClass);
		$ValidationClass = $this->GetValidationClass($RuleClass);

		Gdn::FactoryInstall($ConfigModelClass,
												$ConfigModelClass,
												AWARDS_PLUGIN_RULES_PATH . '/' . $RuleClass . '/models',
												Gdn::FactorySingleton);
		Gdn::FactoryInstall($ValidationClass,
												$ValidationClass,
												AWARDS_PLUGIN_RULES_PATH . '/' . $RuleClass . '/validators',
												Gdn::FactorySingleton);
	}

	/**
	 * Install in Vanilla's Factories all auxiliary classes for available Rule
	 * Classes.
	 *
	 * @return void.
	 */
	protected function InstallRules() {
		foreach(self::$Rules as $RuleClass => $RuleInfo) {
			$this->InstallRule($RuleClass);
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
	 * Builds and returns the name of the Model that handles the configuration
	 * of a Rule class.
	 *
	 * @param RuleClass The class of Rule for which to retrieve the
	 * Model.
	 * @return The class name of the Model.
	 */
	protected function GetConfigModelClass($RuleClass) {
		return $this->RuleExists($RuleClass) ? sprintf('%sConfigModel', $RuleClass) : null;
	}

	/**
	 * Builds and returns the name of the Validation that will be used to validate
	 * the configuration for the a Rule.
	 *
	 * @param RuleClass The class of Rule for which to retrieve the
	 * Model.
	 * @return The class name of the Validation.
	 */
	protected function GetValidationClass($RuleClass) {
		return $this->RuleExists($RuleClass) ? sprintf('%sValidation', $RuleClass) : null;
	}

	/**
	 * Builds and returns the full name of the View to be used as an interface to
	 * configure Rule.
	 *
	 * @param RuleClass The class of Rule for which to retrieve the
	 * View.
	 * @return The full path and file name of the View.
	 */
	public function GetConfigView($RuleClass) {
		if(!$this->RuleExists($RuleClass)) {
			return null;
		}

		return sprintf('%s/%s/views/config_view.php', AWARDS_PLUGIN_RULES_PATH, strtolower($RuleClass));
	}

	/**
	 * Factory method. It instantiates the appropriate Model and Validation for
	 * the specified Rule, and returns the configured Model.
	 *
	 * @param RuleClass The class of Rule for which to instantiate the
	 * Model.
	 * @return An instance of the Model to handle the configuration of the
	 * specified Rule Class.
	 * @throws An Exception if either the Model or its Validation could not be
	 * instantiated.
	 */
	public function GetModel($RuleClass) {
		$ModelClass = $this->GetConfigModelClass($RuleClass);

		// If ModelClass is valid, then it means that the Rule is in the list.
		// Therefore, the Validation just needs to be retrieved.
		if(isset($ModelClass)) {
			$ValidationClass = $this->GetValidationClass($RuleClass);

			try {
				// The Validation is passed to the Model to "assemble" a complete model,
				// which will automatically perform appropriate validation of the
				// configuration.
				$Model = Gdn::Factory($ModelClass, Gdn::Factory($ValidationClass));

				return $Model;
			}
			catch(Exception $e) {
				// Log the exception to keep track of it, but throw it again afterwards.
				// This is useful in case the person who sees the Exception can't fix it
				// and has to require assistance from a Developer, who might not be
				// readily available.
				$Message = sprintf(T('Exception occurred while instantiating Model for Rule "%s": %s'),
																			$RuleClass,
																			$e->getMessage());
				$this->Logger->Error($Message);
				throw new Exception($Message, null, $e);
			}
		}
	}

	/**
	 * Given an Attribute Name, it returns a list of all the Rule Classes and
	 * the value of the specified Attribute for each class.
	 *
	 * @return An associative array having Rule Classes as Keys and the
	 * specified Attribute as Values.
	 */
	protected function GetRulesListWithAttribute($AttributeName) {
		$result = array();
		foreach(self::$Rules as $RuleClass => $Attributes) {
			$result[$RuleClass] = $Attributes[$AttributeName];
		}
		return $result;
	}

	/**
	 * Returns a list of all the Rule Classes with their Labels.
	 *
	 * @return An associative array having Rule Classes as Keys and their
	 * Labels as Values.
	 */
	public function GetRulesLabels() {
		return $this->GetRulesListWithAttribute('Label');
	}

	/**
	 * Returns a list of all the Rule Classes with their descriptions.
	 *
	 * @return An associative array having Rule Classes as Keys and their
	 * Descriptions as Values.
	 */
	public function GetRulesDescriptions() {
		return $this->GetRulesListWithAttribute('Description');
	}

	/**
	 * Returns the Information about a specified Rule Class.
	 *
	 * @param RuleClass The Rule Class for which to retrieve the
	 * information.
	 * @return An associative array containing information about the Rule
	 * Class.
	 */
	public function GetRuleInfo($RuleClass) {
		return self::$Rules[$RuleClass];
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
			return;
		}

		// Load all Rule Files, so that they can add themselves to the list of
		// installed Rules
    while($File = readdir($Handle)) {
      if(strpos($File, 'class.rule') == 0) {
				include_once(sprintf('%s/%s', $RulesDir, $File));
			}
		}
		closedir($Handle);
	}

	/**
	 * Constructor. It initializes the class and populates the list of available
	 * Rules.
	 */
	public function __construct() {
		// Get System (root) Logger
		$this->Logger = LoggerPlugin::GetLogger();

		$this->LoadRulesDefinitions();
		$this->InstallRules();
	}
}
