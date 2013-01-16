<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

?>
<div class="Rule">
	<ul>
		<li>
			<?php
			// TODO Implement interface for "at/every" X questions
			// TODO Implement interface for "at/every" X comments
			// TODO Implement interface for "at/every" X answers
			// TODO Implement interface for "at/every" X of anything
				echo $this->Form->CheckBox('PostCount_Discussions_Enabled', T('Discussions posted by the User'));
				echo $this->Form->DropDown('PostCount_Discussions_CountType', PostCountRule::$CountTypes);
				echo $this->Form->TextBox('PostCount_Discussions_Amount',
																	array('class' => 'InputBox Numeric'));
				echo Wrap(T('Discussions'));
			?>
		</li>		<li>
			<?php
			// TODO Implement interface for "at/every" X discussions
			// TODO Implement interface for "at/every" X questions
			// TODO Implement interface for "at/every" X comments
			// TODO Implement interface for "at/every" X answers
			// TODO Implement interface for "at/every" X of anything
				echo $this->Form->CheckBox('PostCount_Comments_Enabled', T('Comments posted by the User'));
				echo $this->Form->DropDown('PostCount_Comments_CountType', PostCountRule::$CountTypes);
				echo $this->Form->TextBox('PostCount_Comments_Amount',
																	array('class' => 'InputBox Numeric'));
				echo Wrap(T('Comments'));
			?>
		</li>
	</ul>
</div>
