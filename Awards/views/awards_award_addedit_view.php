<?php	if (!defined('APPLICATION')) exit();
/*
{licence}
*/

//function LoadRuleUI(BaseAwardRule $Rule) {
//	ob_start();
//	include($Rule->GetConfigUI());
//	$Result = ob_get_contents();
//	@ob_end_clean();
//	return $Result;
//}

function AddRuleToUI(array &$AwardRulesSections, array &$AwardRule) {
	$RuleGroup = GetValue('Group', $AwardRule);
	$RuleType = GetValue('Type', $AwardRule);

	// Add the rule to the appropriate Group and Section
	$AwardRulesSections[$RuleGroup]->TypeSections[$RuleType]->Rules[] = $AwardRule['Instance'];
	$AwardRulesSections[$RuleGroup]->CountRules += 1;
}

$AwardRulesSections = $this->Data['AwardRulesSections'];

// The following HTML will be displayed when the Awards DataSet doesn't contain Rules
$OutputForNoRules = Wrap(T('No Award Rules installed.'),
												 'div',
													array('class' => 'NoResultsFound',));


// TODO Replace dummy array with a DataSet containing data retrieved from AwardClasses table
$this->Data['AwardClasses'] = array(1 => 'Gold',
																		2 => 'Silver',
																		3 => 'Bronze',);

?>
<div class="AwardsPlugin ClientEdit">
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
		<legend><?php echo Wrap(T('Award Configuration'), 'h1'); ?></legend>
		<ul>
			<li>
				<?php
					echo $this->Form->Label(T('Award Name'), 'AwardName');
					echo Wrap(T('Enter a name for the Award. It must be unique, amongst the Awards.'),
										'div',
										array('class' => 'Info',
													'maxlength' => '100',
													));
					echo $this->Form->TextBox('AwardName');
				?>
			</li>
			<li class="clearfix">
      <?php
				// TODO Display Award Picture to the left of the Upload File control
				echo $this->Form->Label(T('Award Picture'), 'Picture');
			?>
			<div class="AwardImageColumn">
			<?php
				echo Wrap(T('Current Image'), 'h5');
				echo Wrap(Wrap(Img($this->Form->GetValue('AwardImageFile'),
													 array('class' => 'AwardImage',)),
											 'td'),
									'div',
									array('class' => 'AwardImageWrapper'));
			?>
			</div>
			<div class="ImageSelector">
				<?php
					echo Wrap(T('Select new Image'), 'h5');
					// TODO Get picture size from configuration
					echo Wrap(T('Select an image on your computer (2mb max) to be used as an icon for the Award. ' .
											'Image will be resized to 50x50 pixels.'),
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
					echo $this->Form->Label(T('Award Class'), 'AwardClassID');
					echo Wrap(T('The Award Class allows to group the Awards. For example, it could ' .
											'be possible to create Gold Awards, Silver Awards and Bronze Awards ' .
											'to give Users an idea of how difficult is to achieve them.'),
										'div',
										array('class' => 'Info',));

					echo $this->Form->DropDown('AwardClassID',
																		 $this->Data['AwardClasses'],
																		 array('id' => 'AwardClassID',));
				?>
			</li>
			<li>
				<?php
					echo $this->Form->Label(T('Award Description'), 'AwardDescription');
					// TODO Link "Awards Page" to a public page where they can be displayed
					echo Wrap(T('Enter a description for the Award. It will be displayed in ' .
											'the public Awards page.'),
										'div',
										array('class' => 'Info',
													'maxlength' => '400',
													));
					echo $this->Form->TextBox('AwardDescription',
																		array('multiline' => true,
																					'rows' => 5,
																					'cols' => 60,));
				?>
			</li>
			<li>
				<?php
					echo $this->Form->CheckBox('AwardIsEnabled',
																		 T('<strong>Award is Enabled</strong>. Disabled Awards cannot be assigned, but ' .
																			 'they will be displayed for Users who already obtained them.'),
																		 array('value' => 1,));
				?>
			</li>
		</ul>
	</fieldset>
	<fieldset id="AwardRules">
		<legend><?php echo Wrap(T('Rules'), 'h2'); ?></legend>
		<div class="Groups Tabs">
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
					echo '<div id="RuleGroup-' . $GroupID. '" class="RuleGroup">';
					echo Wrap($GroupInfo->Label,
										'h4',
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
					echo '</div>';
				}
			?>
		</div>
	</fieldset>
	<fieldset id="Buttons">
		<?php
			echo $this->Form->Button(T('Save'), array('Name' => 'Save',));
			echo $this->Form->Button(T('Cancel'));
		?>
	</fieldset>
	<?php
		echo $this->Form->Close();
	?>
</div>
