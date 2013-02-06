<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

?>
<div class="Rule">
	<ul>
		<li>
			<div class="Discussions">
			<?php
				echo Wrap('Discussion', 'h3');
				PostCountRule::RenderRuleField($this->Form->CheckBox('Discussions_Enabled', T('Discussions posted by the User')));
				//echo $this->Form->CheckBox('PostCount_Discussions_Enabled', T('Discussions posted by the User'));
				PostCountRule::RenderRuleField($this->Form->DropDown('Discussions_CountType', PostCountRule::$CountTypes));
				PostCountRule::RenderRuleField($this->Form->TextBox('Discussions_Amount',
																														array('class' => 'InputBox Numeric')));
			?>
			</div>
		</li>
		<li>
			<div class="Comments">
			<?php
				echo Wrap('Comments', 'h3');
				PostCountRule::RenderRuleField($this->Form->CheckBox('Comments_Enabled', T('Comments posted by the User')));
				PostCountRule::RenderRuleField($this->Form->DropDown('Comments_CountType', PostCountRule::$CountTypes));
				PostCountRule::RenderRuleField($this->Form->TextBox('Comments_Amount',
																														array('class' => 'InputBox Numeric')));
				echo Wrap(T('Comments'));
			?>
			</div>
		</li>
		<?php
			// TODO Implement interface for "at/every" X questions
			// TODO Implement interface for "at/every" X answers
		?>
	</ul>
</div>
