<?php if (!defined('APPLICATION')) exit();
/**
{licence}
*/

require('plugin.schema.php');

class AwardsSchema extends PluginSchema {
	/**
	 * Create the table which will store the list of configured Awards.
	 */
	protected function create_awards_table() {
		Gdn::Structure()
			->Table('Awards')
			->PrimaryKey('AwardID')
			->Column('Name', 'varchar(100)', FALSE, 'unique')
			->Column('Description', 'varchar(400)')
			->Column('ImageFileName', 'varchar(500)')
			->Column('RankPoints', 'uint', 0)
			->Column('IsEnabled', 'uint', 1, 'index')
			->Column('Configuration', 'text', TRUE)
			->Column('DateInserted', 'datetime', FALSE)
			->Column('InsertUserID', 'int', TRUE)
			->Column('DateUpdated', 'datetime', TRUE)
			->Column('UpdateUserID', 'int', TRUE)
			->Set(FALSE, FALSE);
	}

	/**
	 * Create the table which will store the list of Rules to assign Awards.
	 */
	protected function create_awardrules_table() {
		Gdn::Structure()
			->Table('AwardRules')
			->PrimaryKey('RuleID')
			->Column('AwardID', 'int', 0)
			->Column('Name', 'varchar(100)', FALSE, 'unique')
			->Column('Description', 'varchar(400)')
			->Column('Priority', 'uint', 1, 'index')
			->Column('IsEnabled', 'uint', 1, 'index')
			->Column('Configuration', 'text', TRUE)
			->Column('DateInserted', 'datetime', FALSE)
			->Column('InsertUserID', 'int', TRUE)
			->Column('DateUpdated', 'datetime', TRUE)
			->Column('UpdateUserID', 'int', TRUE)
			->Set(FALSE, FALSE);

		$this->AddForeignKey('AwardRules', 'FK_Awards', array('AwardID'),
												'Awards', array('AwardID'));
		$this->AddIndex('AwardRules', 'IX_Awards', array('`AwardID` ASC'));
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

		$Px = $this->Px;
		// Add Foreign Key referencing AwardRules table
		$SQL = "
			ALTER TABLE `${Px}AwardRulesCriteria`
				ADD CONSTRAINT `FK_AwardRule` FOREIGN KEY (`RuleID`)
				REFERENCES `${Px}AwardsRules` (`RuleID`)
				ON DELETE NO ACTION
				ON UPDATE NO ACTION
			, ADD INDEX `IX_AwardRules` (`RuleID` ASC)
		";
		$this->Construct->Query($SQL);
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
			->Column('DateInserted', 'datetime', FALSE)
			->Column('InsertUserID', 'int', TRUE)
			->Column('DateUpdated', 'datetime', TRUE)
			->Column('UpdateUserID', 'int', TRUE)
			->Set(FALSE, FALSE);

		$Px = $this->Px;
		// Add Foreign Key referencing Users table
		$SQL = "
			ALTER TABLE `${Px}UserAwards`
				ADD CONSTRAINT `FK_User` FOREIGN KEY (`UserID`)
				REFERENCES `${Px}User` (`UserID`)
				ON DELETE NO ACTION
				ON UPDATE NO ACTION
		";
		$this->Construct->Query($SQL);

		// Add Foreign Key referencing Awards table
		$SQL = "
			ALTER TABLE `${Px}UserAwards`
				ADD CONSTRAINT `FK_Awards` FOREIGN KEY (`AwardID`)
				REFERENCES `${Px}Awards` (`AwardID`)
				ON DELETE NO ACTION
				ON UPDATE NO ACTION
			, ADD INDEX `IX_Awards` (`AwardID` ASC)
		";
		$this->Construct->Query($SQL);
	}

	/**
	 * Create all the Database Objects in the appropriate order.
	 */
	protected function CreateObjects() {
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
	}
}
