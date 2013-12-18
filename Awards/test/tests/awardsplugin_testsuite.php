<?php


class AwardsPluginTestSuite extends PHPUnit_Vanilla_TestSuite {
	const PLUGIN_TESTS_DIR = 'plugin';
	const MODELS_TESTS_DIR = 'models';
	const MANAGERS_TESTS_DIR = 'managers';

	protected static $_TestFiles = array(
		// Tests for Plugin's main class
		self::PLUGIN_TESTS_DIR => array(
			'class.awardsplugin.test.php',
		),
		// Tests for Plugin's Models
		self::MODELS_TESTS_DIR => array(
			'class.awardclassesmodel.test.php',
			'class.awardsmodel.test.php',
		),
		// Tests for Plugin's Managers (Controllers)
		self::MANAGERS_TESTS_DIR => array(

		),
	);

	/**
	 * Instantiates a Test Suite.
	 *
	 * @param array TestFiles A list of Test Files to load.
	 * @return PHPUnit_Vanilla_TestSuite A Test Suite instance.
	 */
	public static function suite() {
		$TestSuite = new AwardsPluginTestSuite();
		$TestSuite->addTestFiles(self::_GetTestFiles(__DIR__));
		return $TestSuite;
	}

	/**
	 * Test Suite setup.
	 */
	protected function setUp() {
		// Use function EnablePlugin() to enable all the plugins required, including
		// the one to be tested.
		$this->EnablePlugin('Logger');
		$this->EnablePlugin('AeliaFoundationClasses');
		$this->EnablePlugin('Awards');
  }

	/**
	 * Test Suite teardown.
	 */
  protected function tearDown() {
    print "\nMySuite::tearDown()";
	}
}
