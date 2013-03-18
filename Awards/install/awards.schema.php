<?php if(!defined('APPLICATION')) exit();
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
			->PrimaryKey('AwardClassID')
			->Column('AwardClassName', 'varchar(100)', FALSE, 'unique')
			->Column('AwardClassDescription', 'text')
			->Column('AwardClassImageFile', 'text')
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
			->Column('AwardClassID', 'int', FALSE)
			->Column('AwardName', 'varchar(100)', FALSE, 'unique')
			->Column('AwardDescription', 'text')
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
			->Column('RulesSettings', 'text')
			->Column('AwardIsEnabled', 'uint', 1, 'index')
			->Column('AwardImageFile', 'text')
			->Column('RankPoints', 'uint', 0)
			->Column('DateInserted', 'datetime', FALSE)
			->Column('InsertUserID', 'int', TRUE)
			->Column('DateUpdated', 'datetime', TRUE)
			->Column('UpdateUserID', 'int', TRUE)
			->Set(FALSE, FALSE);

		$this->AddForeignKey('Awards', 'FK_Awards_AwardClasses', array('AwardClassID'),
												'AwardClasses', array('AwardClassID'));
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
			->Column('AwardedRankPoints', 'uint', 0)
			->Column('TimesAwarded', 'uint', 0)
			->Column('DateInserted', 'datetime', FALSE)
			->Column('InsertUserID', 'int', TRUE)
			->Column('DateUpdated', 'datetime', TRUE)
			->Column('UpdateUserID', 'int', TRUE)
			->Set(FALSE, FALSE);

		$this->AddForeignKey('UserAwards', 'FK_UserAwards_User', array('UserID'),
												'User', array('UserID'));
		$this->AddForeignKey('UserAwards', 'FK_UserAwards_Awards', array('AwardID'),
												'Awards', array('AwardID'), 'CASCADE');
		$this->CreateIndex('UserAwards', 'IX_DateInserted', array('`DateInserted` DESC'));
	}

	/**
	 * Creates a View that returns a list of the configured Awards.
	 */
	protected function create_awardslist_view() {
		$Px = $this->Px;
		$Sql = "
		SELECT
			A.AwardID
			,A.AwardClassID
			,A.AwardName
			,A.AwardDescription
			,A.Recurring
			,A.AwardIsEnabled
			,A.AwardImageFile
			,A.RankPoints
			,A.DateInserted
			,A.DateUpdated
			,A.RulesSettings
			,AC.AwardClassName
			,AC.AwardClassImageFile
		FROM
			${Px}Awards A
			JOIN
			${Px}AwardClasses AC ON
				(AC.AwardClassID = A.AwardClassID)
		";
		$this->Construct->View('v_awards_awardslist', $Sql);
	}

	/**
	 * Creates a View that returns a list of the configured Awards.
	 */
	protected function create_userawardslist_view() {
		$Px = $this->Px;
		$Sql = "
			SELECT
				UA.UserID
				,UA.DateInserted AS DateAwarded
				,UA.AwardedRankPoints
				,A.AwardID
				,A.AwardName
				,A.AwardDescription
				,A.Recurring
				,A.AwardIsEnabled
				,A.AwardImageFile
				,A.RankPoints
				,A.DateInserted
				,A.DateUpdated
				,AC.AwardClassName
				,AC.AwardClassImageFile
			FROM
				${Px}UserAwards UA
				JOIN
				${Px}Awards A ON
					(A.AwardID = UA.AwardID)
				JOIN
				${Px}AwardClasses AC ON
					(AC.AwardClassID = A.AwardClassID)
		";
		$this->Construct->View('v_awards_userawardslist', $Sql);
	}

	/**
	 * Creates a View that returns a list of the Awards available to each User.
	 */
	protected function create_availableawardslist_view() {
		$Px = $this->Px;
		$Sql = "
			SELECT
				UA.UserID
				,VAAL.AwardID
				,VAAL.AwardName
				,VAAL.AwardDescription
				,VAAL.Recurring
				,VAAL.AwardIsEnabled
				,VAAL.RankPoints
				,VAAL.RulesSettings
				,COUNT(UA.UserID) AS TimesAwarded
			FROM
				${Px}v_awards_awardslist VAAL
				LEFT JOIN
				${Px}UserAwards UA ON
					(UA.AwardID = VAAL.AwardID)
			WHERE
				-- Awards must be enabled to be available
				(VAAL.AwardIsEnabled = 1) AND
				-- An Award is available if it was never assigned before, or if it is
				-- recurring (i.e. it can be assigned multiple times)
				((UA.AwardID IS NULL) OR (VAAL.Recurring = 1))
			GROUP BY
				UA.UserID
				,VAAL.AwardID
				,VAAL.AwardName
				,VAAL.RankPoints
		";
		$this->Construct->View('v_awards_availableawardslist', $Sql);
	}

	/**
	 * Create all the Database Objects in the appropriate order.
	 */
	protected function CreateObjects() {
		$this->create_awardclasses_table();
		$this->create_awards_table();
		$this->create_userawards_table();

		$this->create_awardslist_view();
		$this->create_userawardslist_view();
		$this->create_availableawardslist_view();
	}

	/**
	 * Delete the Database Objects.
	 */
	protected function DropObjects() {
		$this->DropView('v_awards_availableawardslist');
		$this->DropView('v_awards_userawardslist');
		$this->DropView('v_awards_awardlist');

		$this->DropTable('UserAwards');
		$this->DropTable('Awards');
		$this->DropTable('AwardClasses');
	}
}
