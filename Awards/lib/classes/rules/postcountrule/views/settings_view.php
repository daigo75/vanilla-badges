<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

/* Rules Settings have to be "moved" manually to Form Values because they are
 * stored as JSON and, thus, must be decoded on the fly.
 */
$RuleSettings = $this->Data['RulesSettings']['PostCountRule'];
$this->Form->SetFormValue('Discussions_Enabled', (int)GetValue('Enabled', $RuleSettings->Discussions));
$this->Form->SetFormValue('Discussions_Amount', GetValue('Amount', $RuleSettings->Discussions));
$this->Form->SetFormValue('Comments_Enabled', (int)GetValue('Enabled', $RuleSettings->Comments));
$this->Form->SetFormValue('Comments_Amount', GetValue('Amount', $RuleSettings->Comments));

?>
<fieldset class="Rule">
	<ul>
		<li>
			<div class="Discussions">
			<?php
				echo Wrap('Discussions', 'h3');
				PostCountRule::RenderRuleField($this->Form->CheckBox('Discussions_Enabled', T('User started at least X Discussions')));
				PostCountRule::RenderRuleField($this->Form->TextBox('Discussions_Amount',
																														array('class' => 'InputBox Numeric')));
			?>
			</div>
		</li>
		<li>
			<div class="Comments">
			<?php
				echo Wrap('Comments', 'h3');
				PostCountRule::RenderRuleField($this->Form->CheckBox('Comments_Enabled', T('User posted at least X Comments')));
				PostCountRule::RenderRuleField($this->Form->TextBox('Comments_Amount',
																														array('class' => 'InputBox Numeric')));
			?>
			</div>
		</li>
		<?php
			// TODO Implement interface for "at/every" X questions
			// TODO Implement interface for "at/every" X answers
		?>
	</ul>
</fieldset>
