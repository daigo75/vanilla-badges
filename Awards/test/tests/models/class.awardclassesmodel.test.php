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
			'AwardClassName' => 'Test Award Class',
			'AwardClassDescription' => 'Test Award Class 1 - Description',
			'AwardClassImageFile' => 'plugins/Awards/design/images/awardclasses/dummyfile.png',
			'AwardClassCSSClass' => 'Test-Award-Class',
			'AwardClassCSS' => '',
			'RankPoints' => 1,
		);
	}

	/**
	 * Test Suite initialization.
	 */
	protected function setUp() {
		$this->AwardClassesModel = new AwardClassesModel();
	}

	/**
	 * Test Suite finalization and cleanup.
	 */
	protected function tearDown() {
		unset($this->AwardClassesModel);
	}

	public function testInsert_InvalidCSSClass() {
		$AwardClassData = $this->_SampleAwardClassData();
		unset($AwardClassData['AwardClassID']);

		$AwardClassData['AwardClassCSSClass'] = 'Invalid CSS Class (not respecting CSS naming convention)';
		$this->assertFalse($this->AwardClassesModel->Save($AwardClassData));
	}

	public function testInsert() {
		$AwardClassData = $this->_SampleAwardClassData();
		unset($AwardClassData['AwardClassID']);

		$this->NewAwardClassID = $this->AwardClassesModel->Save($AwardClassData);
		$this->assertTrue(is_numeric($this->NewAwardClassID), sprintf('Operation failed. Validation results: %s', $this->AwardClassesModel->Validation->ResultsText()));
	}

	/**
	 * @depends testInsert
	 */
	public function testInsert_Duplicate() {
		$AwardClassData = $this->_SampleAwardClassData();
		unset($AwardClassData['AwardClassID']);

		$this->assertFalse($this->AwardClassesModel->Save($AwardClassData));
	}

	/**
	 * @depends testInsert
	 */
	public function testUpdate() {
		$AwardClassData = $this->_SampleAwardClassData();

		$AwardClassData['AwardClassID'] = $this->NewAwardClassID;
		$AwardClassData['AwardClassName'] = 'New-Class-Name';

		$this->assertTrue(is_numeric($this->AwardClassesModel->Save($AwardClassData)), sprintf('Operation failed. Validation results: %s', $this->AwardClassesModel->Validation->ResultsText()));
	}
}
