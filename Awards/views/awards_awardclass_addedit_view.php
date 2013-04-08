<?php	if(!defined('APPLICATION')) exit();
/*
{licence}
*/

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
		$SourceAwardClassInfo = Wrap($Form->GetValue('SourceAwardClassName'),
																 'span',
																 array('class' => 'ClassInfo',
																			 'title' => $Form->GetValue('SourceAwardDescription'),));
		return sprintf(T('Clone Award Class "%s"'), $SourceAwardClassInfo);
	}

	return $Form->GetValue('AwardClassID') ? sprintf(T('Edit Award Class'), $Form->GetValue('AwardClassName')) : T('Add new Award Class');
}
?>
<div class="AwardsPlugin AwardClassEdit">
	<?php
		echo $this->Form->Open(array('enctype' => 'multipart/form-data'));
		echo $this->Form->Errors();

		echo $this->Form->Hidden('AwardClassID');
		// Field ImageFile contains the path and file name of the Image currently
		// associated with the Award Class. This field will only be populated when
		// an existing Award Class is being modified.
		echo $this->Form->Hidden('AwardClassImageFile');
		// TODO Add Side box with guidelines in creating the Award Class
	?>
	<fieldset id="AwardClass">
		<legend class="Title">
			<?php
				echo Wrap(GetCurrentAction($this->Form, $this->Data), 'h1');
			?>
			<div class="Buttons Top">
				<?php
					echo $this->Form->Button(T('Save'), array('Name' => 'Save',));
					echo $this->Form->Button(T('Cancel'), array('Name' => 'Cancel',));
				?>
			</div>
		</legend>
		<div>
			<ul id="Fields">
			<li>
				<?php
					echo $this->Form->Label(T('Award Class Name'), 'AwardClassName');
					echo Wrap(T('Enter a name for the Award Class. It must be unique amongst the Award ' .
											'Classes and it must respect specifications for CSS class names (i.e. it ' .
											'can only contain letters, numbers, hyphens and underscores).'),
										'div',
										array('class' => 'Info',
													));
					echo $this->Form->TextBox('AwardClassName');
					echo Wrap(sprintf(T('This class is currently being used by %d Awards.'),
														$this->Form->GetValue('TotalAwardsUsingClass')),
										'span',
										array('class' => 'ClassUsage'));
				?>
			</li>
			<li>
				<?php
					echo $this->Form->Label(T('Rank Points'), 'RankPoints');
					echo Wrap(T('Enter the amount of Rank Points to be granted to the Users who ' .
											'receive an Award using this class. These points are <strong>added</strong> ' .
											'to the ones granted by the Award itself.'),
										'div',
										array('class' => 'Info',));
					echo $this->Form->TextBox('RankPoints');
				?>
			</li>
			<li class="clearfix">
				<?php
					// TODO Display Award Class Picture to the left of the Upload File control
					echo $this->Form->Label(T('Award Class Picture'), 'Picture');
				?>
				<div class="ImageColumn">
				<?php
					echo Wrap(T('Current Image'), 'h5');
					echo Wrap(Wrap(Img($this->Form->GetValue('AwardClassImageFile'),
														 array('class' => 'AwardClassImage Large',)),
												 'td'),
										'div',
										array('class' => 'AwardClassImageWrapper'));
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
					echo $this->Form->Label(T('Award Class Description'), 'AwardClassDescription');
					echo Wrap(T('Enter a description for the Award. It will be displayed on the ' .
											'the public Awards page and it can be useful to give your Users an ' .
											'idea what type of Awards are included in the class. For example, a ' .
											'"Gold" class Award could be harder to achieve than a "Silver" class ' .
											'one.'),
										'div',
										array('class' => 'Info',
													));
					echo $this->Form->TextBox('AwardClassDescription',
																		array('multiline' => true,
																					'rows' => 5,
																					'cols' => 60,));
				?>
			</li>
			<li>
				<?php
					echo $this->Form->Label(T('Additional CSS'), 'AwardClassCSS');
					echo Wrap(T('Here you can enter CSS rules that will be applied to the Award Class. ' .
											'Every Award belonging to this class will automatically inherit the ' .
											'rules an it will be rendered accordingly. For example, if you specify ' .
											'a <code>border</code> style, all Awards in this class will have such ' .
											'border.'),
										'div',
										array('class' => 'Info',
													));
					echo Wrap(T('<strong>Important</strong>: just enter CSS commands without enclosing them in curly braces. ' .
											'The plugin will take care of doing it automatically.'),
										'div',
										array('class' => 'Info',
													));
					echo $this->Form->TextBox('AwardClassCSS',
																		array('multiline' => true,
																					'rows' => 5,
																					'cols' => 60,));
				?>
			</li>
		</ul>
		</div>
	</fieldset>
	<fieldset class="Buttons">
		<?php
			echo $this->Form->Button(T('Save'), array('Name' => 'Save',));
			echo $this->Form->Button(T('Cancel'), array('Name' => 'Cancel',));
		?>
	</fieldset>
	<?php
		echo $this->Form->Close();
	?>
</div>
<?php include('awards_admin_footer.php'); ?>
