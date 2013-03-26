<?php	if(!defined('APPLICATION')) exit();
/*
{licence}
*/

// TODO Document function
function AddRuleToUI(array &$AwardRulesSections, array &$AwardRule) {
	$RuleGroup = GetValue('Group', $AwardRule);
	$RuleType = GetValue('Type', $AwardRule);

	// Add the rule to the appropriate Group and Section
	$AwardRulesSections[$RuleGroup]->TypeSections[$RuleType]->Rules[] = $AwardRule['Instance'];
	$AwardRulesSections[$RuleGroup]->CountRules += 1;
}

/**
 * Determines current User action (i.e. Clone, Add or Edit) and returns a
 * description for it.
 *
 * @param Gdn_Form Form The form used by the page.
 * @param array Data An associative array of data passed to the page.
 * @return string The message describing current action.
 */
function GetCurrentAction(Gdn_Form $Form, $Data) {
	if(GetValue('Cloning', $Data)) {
		$SourceAwardInfo = Wrap($Form->GetValue('SourceAwardName'),
																 'span',
																 array('class' => 'Info',
																			 'title' => $Form->GetValue('SourceAwardDescription'),));
		return sprintf(T('Clone Award "%s"'), $SourceAwardInfo);
	}

	return $Form->GetValue('AwardID') ? sprintf(T('Edit Award'), $Form->GetValue('AwardName')) : T('Add new Award ');
}

// Retrieve the Sections to organise the Rules
$AwardRulesSections = $this->Data['AwardRulesSections'];

// The following HTML will be displayed when the Awards DataSet doesn't contain Rules
$OutputForNoRules = Wrap(T('No Award Rules installed.'),
												 'div',
													array('class' => 'NoResultsFound',));


// Check if we're configuring a new appender or editing an existing one.
$AwardID = $this->Form->GetValue('AwardID');
$IsNewAward = empty($AwardID) ? true : false;
?>
<div class="AwardsPlugin AwardEdit">
	<?php
		echo $this->Form->Open(array('enctype' => 'multipart/form-data'));
		echo $this->Form->Errors();

		echo $this->Form->Hidden('AwardID');
		// Field ImageFile contains the path and file name of the Image currently
		// associated with the Award. This field will only be populated when an
		// existing Award is being modified.
		echo $this->Form->Hidden('AwardImageFile');
	?>
	<fieldset id="Award">
		<legend><?php
			echo Wrap(GetCurrentAction($this->Form, $this->Data), 'h1');
		?></legend>
		<div class="Buttons Top">
			<?php
				echo $this->Form->Button(T('Save'), array('Name' => 'Save',));
				echo $this->Form->Button(T('Cancel'));
			?>
		</div>
		<div class="Tabs">
			<div id="AwardInfo" class="Tab">
				<h2 class="Label"><?php echo T('Award Info'); ?></h2>
				<ul id="Fields">
					<li>
						<?php
							// Set IsEnabled to True if we're adding a new Award
							if($IsNewAward) {
								$this->Form->SetValue('AwardIsEnabled', 1);
							}
							echo $this->Form->CheckBox('AwardIsEnabled',
																				 T('<strong>Award is Enabled</strong>. Disabled Awards cannot be assigned, but ' .
																					 'they will be displayed for Users who already obtained them.'),
																				 array('value' => 1,));
						?>
					</li>
					<li>
						<?php
							echo $this->Form->Label(T('Award Name'), 'AwardName');
							echo Wrap(T('Enter a name for the Award. It must be unique amongst the Awards.'),
												'div',
												array('class' => 'Info',
															));
							echo $this->Form->TextBox('AwardName');
						?>
					</li>
					<li>
						<?php
							echo $this->Form->Label(T('Award Class'), 'AwardClassID');
							echo Wrap(T('The Award Class allows to group the Awards. For example, it could ' .
													'be possible to create Gold Awards, Silver Awards and Bronze Awards ' .
													'to give Users an idea of how difficult is to achieve them.'),
												'div',
												array('class' => 'Info',));

							echo $this->Form->DropDown('AwardClassID',
																				 GetValue('AwardClasses', $this->Data),
																				 array('id' => 'AwardClassID',
																							 'ValueField' => 'AwardClassID',
																							 'TextField' => 'AwardClassName'));
						?>
					</li>
					<li>
						<?php
							echo $this->Form->Label(T('Rank Points'), 'RankPoints');
							echo Wrap(T('Enter the amount of Rank Points to give to Users who receive ' .
													'the Award. These points will be used by <a class="Standard" href="#" title="Rankings ' .
													'Plugin has not been released, yet">Rankings Plugin</a> to assign ' .
													'Users titles, permissions, etc.'),
												'div',
												array('class' => 'Info',));
							echo $this->Form->TextBox('RankPoints');
						?>
					</li>
					<li class="clearfix">
						<?php
							echo $this->Form->Label(T('Award Picture'), 'Picture');
						?>
						<div class="ImageColumn">
						<?php
							echo Wrap(T('Current Image'), 'h5');
							echo Wrap(Wrap(Img($this->Form->GetValue('AwardImageFile'),
																 array('class' => 'AwardImage Large',)),
														 'td'),
												'div',
												array('class' => 'AwardImageWrapper'));
						?>
						</div>
						<div class="ImageSelector">
							<?php
								echo Wrap(T('Select new Image'), 'h5');
								// TODO Get picture size from configuration
								echo Wrap(sprintf(T('Select an image on your computer (2mb max) to be used as ' .
																		'an icon for the Award. Image will be resized to %dx%d (width ' .
																		'x height) pixels.'),
																	PictureManager::DEFAULT_IMAGE_WIDTH,
																	PictureManager::DEFAULT_IMAGE_HEIGHT),
													'p');
								echo Wrap(T('<strong>Important</strong>: if you upload a file with the same '.
														'name of one you uploaded before, the old file will be overwritten.'),
													'p');
								echo $this->Form->Input('Picture', 'file');
							?>
						</div>
					</li>
					<li>
						<?php
							echo $this->Form->Label(T('Award Description'), 'AwardDescription');
							// TODO Link "Awards Page" to a public page where they can be displayed
							echo Wrap(T('Enter a description for the Award. It will be displayed in ' .
													'the public Awards page.'),
												'div',
												array('class' => 'Info',
															));
							echo $this->Form->TextBox('AwardDescription',
																				array('multiline' => true,
																							'rows' => 5,
																							'cols' => 60,));
						?>
					</li>
				</ul>
			</div>
			<?php
				$AwardRules = GetValue('AwardRules', $this->Data, array());

				if(empty($AwardRules)) {
					echo $OutputForNoRules;
					// If there are no Rules, empty the Rule Sections, to avoid looping
					// through them for nothing (they would not be rendered anyway)
					$AwardRulesSections = array();
				}

				// Load the Configuration UI for each rule and add it to the appropriate
				// section
				foreach($AwardRules as $AwardRule) {
					//var_dump($AwardRule);
					//var_dump($this);die();
					//include($AwardRule['Instance']->GetConfigUI());
					AddRuleToUI($AwardRulesSections, $AwardRule);
				}

				// Render each Rule's Configuration UI
				foreach($AwardRulesSections as $GroupID => $GroupInfo) {
					if($GroupInfo->CountRules <= 0) {
						continue;
					}

					// Render the Rule Group section
					echo '<fieldset id="RuleGroup-' . $GroupID. '" class="RuleGroup Tab">';
					echo Wrap($GroupInfo->Label . '&nbsp;' . T('Rules'),
										'h2',
										array('class' => 'Label')
										);

					foreach($GroupInfo->TypeSections as $TypeID => $TypeInfo) {
						// Don't render empty sections
						if(empty($TypeInfo->Rules)) {
							continue;
						}

						// Render Rule Type section
						echo '<div class="RuleType">';
						echo Wrap($TypeInfo->Label,
											'h5',
											array('class' => 'Label')
											);

						echo '<ul class="Rules">';

						// Render the Rule's Configuration UI
						foreach($TypeInfo->Rules as $AwardRule) {
							echo '<li>';
							include($AwardRule->GetConfigUI());
							echo '</li>';
						}

						echo '</ul>';
						echo '</div>';
					}
					echo '</div>'; // Rule Group Fieldset
				}
			?>
			<div class="Buttons">
				<?php
					echo $this->Form->Button(T('Save'), array('Name' => 'Save',));
					echo $this->Form->Button(T('Cancel'));
				?>
			</div>
		</div> <!-- End Tabs Container -->
	</fieldset>
	<?php
		echo $this->Form->Close();
	?>
</div>
