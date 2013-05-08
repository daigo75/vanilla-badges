<?php
/**
{licence}
*/

class AwardClassesModelTests extends PHPUnit_Vanilla_TestCase {
	/**
	 * Returns some Award Class Data that can be used to save an Award Class. Each
	 * test can modify such data to save more tha one Award Class.
	 *
	 * @return array An array describing an Award Class.
	 */
	private function _SampleAwardClassData() {
		return array(
			'AwardClassID' => 1,
			'AwardClassName' => 'Test Award Class ',
			'AwardClassDescription' => 'Test Award Class 1 - Description',
			'AwardClassImageFile' => 'plugins/Awards/design/images/awardclasses/dummyfile.png',
			'AwardClassCSS' => '',
			'RankPoints' => 1,
		);
	}

	/**
	 * Test Suite initialization.
	 */
	protected function setUp() {
		$this->AwardClassesModel = new AwardsModel();
	}

	/**
	 * Test Suite finalization and cleanup.
	 */
	protected function tearDown() {
		unset($this->AwardClassesModel);
	}

	/**
	 * Sample test. Verify that internal Plugin variable has been initialized.
	 */
	public function testAwardClassInsert() {
		$AwardClassData = $this->_SampleAwardClassData();
		var_dump("AWARD CLASS DATA:", $AwardClassData);
		unset($AwardClassData['AwardClassID']);

		$NewAwardClassID = $this->AwardClassesModel->Save($AwardClassData);
		$this->assertTrue(is_numeric($NewAwardClassID));
	}
}
