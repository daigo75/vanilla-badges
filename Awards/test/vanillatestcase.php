<?php
/**
{licence}
*/

require_once('init.php');

class VanillaTestCase extends PHPUnit_Framework_TestCase {
	protected function EnablePlugin($PluginName, $ThrowExceptionOnError = true) {
		$PluginManager = Gdn::PluginManager();
		// Check if Plugin to be tested is enabled. If not, try to enable it.
		if(!$PluginManager->CheckPlugin($PluginName)) {
			$Validation = new Gdn_Validation();
			if(Gdn::PluginManager()->EnablePlugin($PluginName, $Validation)) {
				printf("Plugin %s has been enabled successfully.\n", $PluginName);
				$Result = true;
			}
			else {
				printf("Plugin %s could not be enabled. Validation Results: %s.\n",
							 $PluginName,
							 $Validation->ResultsText());
				$Result = false;
			}
		}
		if(!$Result && $ThrowExceptionOnError) {
			throw new Exception("Plugin enabling failed.\n");
		}

		return $Result;
	}

}
