<?php
/**
{licence}
*/

class AwardsModelTests extends PHPUnit_Vanilla_TestCase {
	/**
	 * Returns some Award Data that can be used to save an Award. Each test can
	 * modify such data to save more tha one award/
	 *
	 * @return array An array describing an Award.
	 */
	private function _SampleAwardData() {
		return array(
			'AwardID' => 1,
			'AwardClassID' => 1,
			'AwardName' => 'Test Award 1',
			'AwardDescription' => 'Test Award 1 - Description',
			'Recurring' => 0,
			'RulesSettings' => json_encode(array()),
			'AwardIsEnabled' => 1,
			'AwardImageFile' => 'plugins/Awards/design/images/awards/dummyfile.png',
			'RankPoints' => 1
		);
	}

	/**
	 * Test Suite initialization.
	 */
	protected function setUp() {
		$this->AwardsModel = new AwardsModel();
	}

	/**
	 * Test Suite finalization and cleanup.
	 */
	protected function tearDown() {
		unset($this->AwardsModel);
	}

	/**
	 * Sample test. Verify that internal Plugin variable has been initialized.
	 */
	public function testAwardInsert() {
		$AwardData = $this->_SampleAwardData();
		unset($AwardData['AwardID']);

		$NewAwardID = $this->AwardsModel->Save($AwardData);
		$this->assertTrue(is_numeric($NewAwardID),
											sprintf('Operation failed. Validation results: %s', $this->AwardsModel->Validation->ResultsText()));
	}
}
