<?php	if(!defined('APPLICATION')) exit();
/*
{licence}
*/
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
	?>
	<fieldset id="AwardClass">
		<legend><?php echo Wrap(T('Award Class Configuration'), 'h1'); ?></legend>
		<ul>
			<li>
				<?php
					echo $this->Form->Label(T('Award Class Name'), 'AwardClassName');
					echo Wrap(T('Enter a name for the Award Class. It must be unique, amongst the Award Classes.'),
										'div',
										array('class' => 'Info',
													'maxlength' => '100',
													));
					echo $this->Form->TextBox('AwardClassName');
					echo Wrap(sprintf(T('This class is currently being used by %d Awards.'),
														$this->Form->GetValue('TotalAwardsUsingClass')),
										'p');
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
													'maxlength' => '400',
													));
					echo $this->Form->TextBox('AwardClassDescription',
																		array('multiline' => true,
																					'rows' => 5,
																					'cols' => 60,));
				?>
			</li>
			<li>
				<?php
					echo $this->Form->Label(T('Award Class CSS'), 'AwardClassCSS');
					echo Wrap(T('Here you can enter CSS rules that will be applied to the Award Class. ' .
											'Every Award belonging to this class will automatically inherit the ' .
											'rules an it will be rendered accordingly. For example, if you specify ' .
											'a <code>border</code> style, all Awards in this class will have such ' .
											'border'),
										'div',
										array('class' => 'Info',
													'maxlength' => '400',
													));
					echo $this->Form->TextBox('AwardClassCSS',
																		array('multiline' => true,
																					'rows' => 5,
																					'cols' => 60,));
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