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
			// Field "Recurring" indicates if an Award could be assigned multiple
			// times. The value of this field will be determined by inspecting the
			// Rules for the assignment of the Award. If rules contains at least one
			// recurring criterion (e.g. "every X posts") the Award will be a
			// Recurring one.
			//
			// Examples
			// - Award 1 contains rule "Assign Award X when User votes for the first
			//   time". This is NOT a recurring Award, as User can vote for the first
			//   time only once.
			// - Award 2 contains rule "Assign Award Y for every year of subscription".
			//   This is a recurring Award, as User would get it every year. For
			//   such reason, the rules must be processed every time, even if the Award
			//   was already assigned.
			->Column('Recurring', 'uint', 0, 'index')
			->Column('IsEnabled', 'uint', 1, 'index')
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
			// Primary Key is formed by RuleID and RuleName. AwardID is a FK to
			// Awards table, while Rule Name will be extracted from the
			// Rule class.
			->Column('AwardID', 'int', FALSE, 'primary')
			->Column('RuleName', 'varchar(100)', FALSE, 'primary')
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
	 * Create the table which will store the association between Users and their
	 * Awards.
	 */
	protected function create_userawards_table() {
		Gdn::Structure()
			->Table('UserAwards')
			->PrimaryKey('UserAwardsID')
			// Fields UserID and AwardID should be indexed. This will be done during
			// the creation of Foreign Keys on such fields
			->Column('UserID', 'int', FALSE)
			->Column('AwardID', 'int', FALSE)
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
		$this->CreateIndex('UserAwards', 'IX_DateInserted', array('`DateInserted` DESC'));
	}

	/**
	 * Create all the Database Objects in the appropriate order.
	 */
	protected function CreateObjects() {
		$this->create_awardclasses_table();
		$this->create_awards_table();
		$this->create_awardrules_table();
		$this->create_userawards_table();
	}

	/**
	 * Delete the Database Objects.
	 */
	protected function DropObjects() {
		//$this->DropView('v_logger_appenders');
		$this->DropTable('UserAwards');
		$this->DropTable('AwardsRules');
		$this->DropTable('Awards');
		$this->DropTable('AwardClasses');
	}
}