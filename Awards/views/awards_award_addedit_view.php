<?php	if (!defined('APPLICATION')) exit();
/*
Copyright 2012 Diego Zanella IT Services
This file is part of UserStats Plugin for Vanilla Forums.

UserStats Plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or (at your option) any later version.
UserStats Plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
You should have received a copy of the GNU General Public License along with UserStats Plugin .  If not, see <http://www.gnu.org/licenses/>.

Contact Diego Zanella at diego [at] pathtoenlightenment [dot] net
*/

// The following HTML will be displayed when the Awards DataSet doesn't contain Rules
$OutputForNoRules = Wrap(T('No Award Rules installed.'),
												 'div',
													array('class' => 'NoResultsFound',));


// TODO Replace dummy array with a DataSet containing data retrieved from AwardClasses table
$this->Data['AwardClasses'] = array(1 => 'Gold',
																		2 => 'Silver',
																		3 => 'Bronze',);

?>
<div class="UserStats ClientEdit">
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
			<li>
      <?php
				// TODO Display Award Picture to the left of the Upload File control
				echo $this->Form->Label(T('Award Picture'), 'Picture');
				echo Wrap(T('Select an image on your computer (2mb max) to be used as an icon for the Award.'),
									'p');
				echo Wrap(T('<strong>Important</strong>: if you upload a file with the same '.
										'name of one you uploaded before, the old file will be overwritten.'),
									'p');
				echo $this->Form->Input('Picture', 'file');
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
		<ul>
			<li>
				<?php
					$AwardDataSet = $this->Data['$AwardDataSet'];

					// If DataSet is empty, just print a message.
					if(empty($AwardDataSet)) {
						echo $OutputForNoRules;
					}

					foreach($AwardDataSet as $AwardRule) {
					// TODO Output the configuration section for each Award Rule
					}
				?>
			</li>
		</ul>
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
