<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

require('plugin.schema.php');

class AwardsSchema extends PluginSchema {
	/**
	 * Create the table which will store the list of configured Award Classes.
	 */
	protected function create_awardclasses_table() {
		Gdn::Structure()
			->Table('AwardClasses')
			->PrimaryKey('ClassID')
			->Column('Name', 'varchar(100)', FALSE, 'unique')
			->Column('Description', 'varchar(400)')
			->Column('BackgroundImageFile', 'varchar(500)')
			->Column('DateInserted', 'datetime', FALSE)
			->Column('InsertUserID', 'int', TRUE)
			->Column('DateUpdated', 'datetime', TRUE)
			->Column('UpdateUserID', 'int', TRUE)
			->Set(FALSE, FALSE);
	}

	/**
	 * Create the table which will store the list of configured Awards.
	 */
	protected function create_awards_table() {
		Gdn::Structure()
			->Table('Awards')
			->PrimaryKey('AwardID')
			->Column('ClassID', 'int', FALSE)
			->Column('Name', 'varchar(100)', FALSE, 'unique')
			->Column('Description', 'varchar(400)')
			->Column('ImageFile', 'varchar(500)')
			->Column('RankPoints', 'uint', 0)
			->Column('IsEnabled', 'uint', 1, 'index')
			->Column('DateInserted', 'datetime', FALSE)
			->Column('InsertUserID', 'int', TRUE)
			->Column('DateUpdated', 'datetime', TRUE)
			->Column('UpdateUserID', 'int', TRUE)
			->Set(FALSE, FALSE);

		$this->AddForeignKey('Awards', 'FK_Awards_AwardClasses', array('ClassID'),
												'AwardClasses', array('ClassID'));
	}

	/**
	 * Create the table which will store the list of Rules to assign Awards.
	 */
	protected function create_awardrules_table() {
		Gdn::Structure()
			->Table('AwardRules')
			->PrimaryKey('RuleID')
			->Column('AwardID', 'int', FALSE)
			->Column('Name', 'varchar(100)', FALSE, 'unique')
			->Column('Description', 'varchar(400)')
			// Field "Recurring" indicates if an Award Assignment Rule could have to
			// run multiple times, even if the Award has been assigned. The value of
			// this field will be determined by inspecting the Criteria composing the
			// Rule. If rule contains at least one recurring Criterion (e.g. "every X
			// posts") the Rule will be a Recurring Rule.
			//
			// Examples
			// - Rule 1 indicates "Assign Award X when User votes for the first time".
			//   This is NOT a recurring Rule, as User can vote for the first time only
			//   once.
			// - Rule 2 indicates "Assign Award Y for every year of subscription".
			//   This is a recurring rule, as User would get the Award every year. For
			//   such reason, the Rule must be processed every time, even if the award
			//   was already assigned.
			->Column('Recurring', 'uint', 0, 'index')
			->Column('Priority', 'uint', 1, 'index')
			->Column('IsEnabled', 'uint', 1, 'index')
			->Column('Configuration', 'text', TRUE)
			->Column('DateInserted', 'datetime', FALSE)
			->Column('InsertUserID', 'int', TRUE)
			->Column('DateUpdated', 'datetime', TRUE)
			->Column('UpdateUserID', 'int', TRUE)
			->Set(FALSE, FALSE);

		$this->AddForeignKey('AwardRules', 'FK_AwardRules_Awards', array('AwardID'),
												'Awards', array('AwardID'));
	}

	/**
	 * Create the table which will store the Criteria that composes each Award
	 * Assignment Rule.
	 */
	protected function create_awardrulescriteria_table() {
		Gdn::Structure()
			->Table('AwardRulesCriteria')
			->PrimaryKey('CriteriaID')
			->Column('RuleID', 'int', 0)
			->Column('IsEnabled', 'uint', 1, 'index')
			->Column('Configuration', 'text', TRUE)
			->Column('DateInserted', 'datetime', FALSE)
			->Column('InsertUserID', 'int', TRUE)
			->Column('DateUpdated', 'datetime', TRUE)
			->Column('UpdateUserID', 'int', TRUE)
			->Set(FALSE, FALSE);

		$this->AddForeignKey('AwardRulesCriteria', 'FK_AwardRulesCriteria_AwardRules', array('RuleID'),
												'AwardRules', array('RuleID'));
	}

	/**
	 * Create the table which will store the association between Users and their
	 * Awards.
	 */
	protected function create_userawards_table() {
		Gdn::Structure()
			->Table('UserAwards')
			->Column('UserID', 'int', FALSE, 'primary')
			->Column('AwardID', 'int', FALSE, 'primary')
			->Column('RankPoints', 'uint', 0)
			->Column('DateInserted', 'datetime', FALSE)
			->Column('InsertUserID', 'int', TRUE)
			->Column('DateUpdated', 'datetime', TRUE)
			->Column('UpdateUserID', 'int', TRUE)
			->Set(FALSE, FALSE);

		$this->AddForeignKey('UserAwards', 'FK_UserAwards_User', array('UserID'),
												'User', array('UserID'));
		$this->AddForeignKey('UserAwards', 'FK_UserAwards_Awards', array('AwardID'),
												'Awards', array('AwardID'));
	}

	/**
	 * Create all the Database Objects in the appropriate order.
	 */
	protected function CreateObjects() {
		$this->create_awardclasses_table();
		$this->create_awards_table();
		$this->create_awardrules_table();
		$this->create_awardrulescriteria_table();
		$this->create_userawards_table();
	}

	/**
	 * Delete the Database Objects.
	 */
	protected function DropObjects() {
		//$this->DropView('v_logger_appenders');
		$this->DropTable('UserAwards');
		$this->DropTable('AwardRulesCriteria');
		$this->DropTable('AwardsRules');
		$this->DropTable('Awards');
		$this->DropTable('AwardClasses');
	}
}
