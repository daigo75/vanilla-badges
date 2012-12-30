<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

?>
<div class="AwardsPlugin">
	<div class="Header">
		<?php include('awards_admin_header.php'); ?>
	</div>
	<div class="Content">
		<?php
			echo $this->Form->Open();
			echo $this->Form->Errors();
		?>
		<fieldset>
			<legend>
				<h3><?php echo T('Rules Criteria'); ?></h3>
				<?php
				echo Wrap(T('Here you can see a list of the Criteria that can be used to create Awards Rules. ' .
										'By enabling or disabling a Criterion, you determine if it will be available in ' .
										'Rules Configuration page.'),
									'p');

				echo Wrap(T('Important'), 'h4');
				echo Wrap(T('Disabled Criteria will not only be unavailable in Rules Configuration page, it ' .
										'will also be ignored during Rules processing. That means, if you use a ' .
										'Criterion in a Rule, then disable it, the Rule will ignore that Criterion ' .
										'until you enable it again'),
									'p');
				?>
			</legend>
			<ul>
				<li><?php
					// TODO Display list of Criteria
					// TODO Display Enable/Disable button

					//echo $this->Form->Label(T('Awards Level'), 'Plugin.Awards.LogLevel');
					//echo Wrap(T('Select the Log Level. Messages with a level lower than the one selected ' .
					//						'will be ignored. <strong>Example</strong>: if you select "<i>Warning</i>", ' .
					//						'messages logged as <i>Trace</i>, <i>Debug</i> and <i>Info</i> will be ignored.'),
					//					'div',
					//					array('class' => 'Info',));
					//echo $this->Form->DropDown('Plugin.Awards.LogLevel',
					//													 $AwardsLevels,
					//													 array('id' => 'AwardsLevel',
					//																 'value' => $CurrentAwardsLevel,));
				?></li>
			</ul>
		</fieldset>
		<?php
			 echo $this->Form->Close('Save');
		?>
	</div>
</div>
