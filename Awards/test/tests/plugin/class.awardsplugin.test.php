<?php
/**
{licence}
*/

class AwardsPluginTests extends PHPUnit_Vanilla_TestCase {
	protected $AwardsPlugin;

	/**
	 * Test Suite initialization.
	 */
	protected function setUp() {
		// Use function EnablePlugin() to enable all the plugins required, including
		// the one to be tested.
		$this->EnablePlugin('Logger');
		$this->EnablePlugin('AeliaFoundationClasses');
		$this->EnablePlugin('Awards');

		// Instantiate the plugin to be tested.
		$this->AwardsPlugin = $this->GetPluginInstance('Awards');
	}

	/**
	 * Test Suite finalization and cleanup.
	 */
	protected function tearDown() {
	}

	/**
	 * Sample test. Verify that internal Plugin variable has been initialized.
	 */
	public function testPluginSet() {
		$this->assertNotNull($this->AwardsPlugin, T('Plugin has not been instantiated.'));
	}

	/**
	 * Attempt to register to the Cron Jobs list a null object.
	 *
	 * @expectedException InvalidArgumentException
	 * @expectedExceptionCode CRON_ERR_NOT_AN_OBJECT
	 */
	//public function testRegisterCronJob_NullObject() {
	//	$InvalidObject = null;
	//	$this->CronJobsPlugin->RegisterCronJob($InvalidObject);
	//}
	//
	///**
	// * Attempt to register to the Cron Jobs list an object which doesn't
	// * implement the required Cron() method.
	// *
	// * @expectedException InvalidArgumentException
	// * @expectedExceptionCode CRON_ERR_CRON_METHOD_UNDEFINED
	// */
	//public function testRegisterCronJob_CronMethodUndefined() {
	//	$InvalidObject = new stdClass();
	//	$this->CronJobsPlugin->RegisterCronJob($InvalidObject);
	//}
	//
	///**
	// * Attempt to register to the Cron Jobs list an object which implements
	// * the required Cron() as expected. For the purpose of this test, the
	// * internal CronJobsPlugin is used, as it already implements the required
	// * method. It wouldn't make much sense in real life to register the same
	// * object multiple times, however RegisterCronJob method uses object's
	// * class as a key, preventing duplicate registration.
	// *
	// */
	//public function testRegisterCronJob_ExpectedObject() {
	//	$this->assertTrue($this->CronJobsPlugin->RegisterCronJob($this->CronJobsPlugin),
	//										T('Failed to register plugin for Cron.'));
	//}
}
