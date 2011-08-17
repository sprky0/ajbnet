<?php

/**
 * Scores is an AJBnet plugin for keeping track of user ratings.
 * 
 * Scores are stored as rating operations, up, down or reset.  This ensures that
 * computed ratings are acurate regardless of sequence.
 * 
 * @author Avery Brooks
 * @copyright 2009
 * @package AJBnet
 * @subpackage Modules
 * 
 */
class Scores extends AJBnet_DAO {
	
	protected $Table = "Scores";
	protected $Tables = array(
	"

		CREATE TABLE  `Scores` (
			`ScoreID` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
			`R_UserID` INT( 10 ) UNSIGNED NOT NULL,
			`Type` INT( 1 ) UNSIGNED NOT NULL DEFAULT  '0',
			`Created` DATETIME NOT NULL,
			`Updated` DATETIME NOT NULL,
			PRIMARY KEY (  `ScoreID` ),
			INDEX (  `R_UserID` )
		) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci

	",
	"

		CREATE TABLE `Scores_Friends` (
			`Scores_FriendID` INT( 10 ) UNSIGNED NOT NULL AUTO_INCREMENT,
			`R_UserID_Requester` INT( 10 ) UNSIGNED NOT NULL,
			`R_UserID_Requestee` INT( 10 ) UNSIGNED NOT NULL,
			`Created` DATETIME NOT NULL,
			`Updated` DATETIME NOT NULL,
			PRIMARY KEY ( `Scores_FriendID` )
		) ENGINE = INNODB CHARACTER SET utf8 COLLATE utf8_unicode_ci

	"
	);
	
	public function __construct($AJBnet = null) {
		parent::__construct(&$AJBnet);	
	}

	// DANGER WILL ROBINSON!  MUST REMOVE THIS BEFORE REAL DEPLOYMENT

	public function Refresh() {
		$this->Query("DROP TABLE `Scores`;");
		$this->Query("DROP TABLE `Scores_Friends`;");
		$this->Install();
		return $this->Select("SELECT * FROM `{$this->Table}`");
	}

	/**
	 * Add a positive mark to the specified User.
	 * 
	 * @param integer $UserID
	 * @return boolean
	 */
	public function RateUp($UserID = null) {
		$this->RequireLogin();
		if (null === $UserID && !empty($_POST['UserID']))
			$UserID = (int) $_POST['UserID'];
		if (null === $UserID)
			throw new ApplicationException("Missing required param UserID for /User/RateUp");
		if (!$this->IsMyFriend($UserID))
			throw new ApplicationException("That person isn't your friend!");
		$this->AddMessage("I am rating up UserID {$UserID}");
		return $this->_Rate($UserID,1);
	}

	/**
	 * Add a negative mark to the specified User.
	 * 
	 * @param integer $UserID
	 * @return boolean
	 */
	public function RateDown($UserID = null) {
		$this->RequireLogin();
		if (null === $UserID && !empty($_POST['UserID']))
			$UserID = (int) $_POST['UserID'];
		if (null === $UserID)
			throw new ApplicationException("Missing required param UserID for /User/RateDown");
		if (!$this->IsMyFriend($UserID))
			throw new ApplicationException("That person isn't your friend!");
		$this->AddMessage("I am rating down UserID {$UserID}");
		return $this->_Rate($UserID,2);
	}

	/**
	 * "Reset" the points of the specified User.
	 * 
	 * @param integer $UserID
	 * @return boolean
	 */
	public function Reset($UserID = null) {
		$this->RequireLogin();
		if (null === $UserID)
			$UserID = $_POST['UserID'];
		if (!$this->IsMyFriend($UserID))
			throw new ApplicationException("That person isn't your friend!");
		$this->AddMessage("I am resetting UserID {$UserID}");
		return $this->_Rate($UserID,0);
	}

	private function GetName($UserID = null) {
		if (null === $UserID)
			throw new ApplicationException("Missing required param UserID for Scores::GetName",406);
		$res = $this->SelectOne("SELECT `UserName` FROM `User` WHERE `UserID` = '{$UserID}' LIMIT 1");
		return $res['UserName'];
	}

	public function GetScore($UserID=null) {

		// this will cause problems internally if we allow null >:O
		// if (null === $UserID)
		//	$UserID = (int) $this->GetID();

		// BAD!
		if (null === $UserID)
			return;

		$this->AddMessage("I am returning score for UserID {$UserID}");

		// Get latest "reset"
		$ret = $this->SelectOne("SELECT * FROM `{$this->Table}` WHERE `R_UserID` = {$UserID} AND Type = 0 ORDER BY `Created` DESC LIMIT 1");

		$SQL = "SELECT * FROM `{$this->Table}` WHERE `R_UserID` = {$UserID}"; 

		if (false !== $ret && isset($ret['Created']))
			$SQL .=  " AND Created > '{$ret['Created']}';";

		$ret = $this->Select($SQL);

		// If this shits the bed, we will default to a score of 0
		$score = 0;

		if (false !== $ret && is_array($ret)) {

			foreach($ret as &$r) { 
				switch((int)$r['Type']):

					case 1: // RATE UP
						$score ++;
						break;

					case 2: // RATE DOWN
						$score --;
						break;

					case 0: // RESET
						// BAD!  THIS MUST NOT HAPPEN, but lets handle it.
						$this->AddMessage("Note that the data got really fucked here.");
						$score = 0;
						break;

				endswitch;

			}
		
		}

		return $score;
	}

	// FRIEND FUNCTIONS

	public function IsMyFriend($UserID = null) {
		$this->RequireLogin();
		if (null === $UserID)
			$UserID = (int) $this->GetID();
		if (null === $UserID)
			throw new ApplicationException("Missing ID for Scores::IsMyFriend",406);

		$Result = $this->GetFriendLink($UserID);

		return !empty($Result);
	}

	public function AddFriend($UserID = null) {
		$this->RequireLogin();
		if (null === $UserID && empty($_POST['UserID']))
			throw new ApplicationException("Missing param UserID for Scores::AddFriend!",406);

		if (null === $UserID)
			$UserID = $_POST['UserID'];

		if ($this->AJBnet->User->GetUserID() === $UserID)
			throw new ApplicationException("This person is you.",406);

		if ($this->IsMyFriend($UserID))
			throw new ApplicationException("This person is already your friend.",500);

		$Values = array(
			"R_UserID_Requester" => $this->AJBnet->User->GetUserID(),
			"R_UserID_Requestee" => (int) $UserID
		);

		$this->Insert($Values,"Scores_Friends");

		// I feel pretty good about this.  At this point, if we haven't
		return true;
	}

	public function RemoveFriend($UserID = null) {
		$this->RequireLogin();
		if (null === $UserID && empty($_POST['UserID']))
			throw new ApplicationException("Missing param UserID for Scores::RemoveFriend!",406);

		if (!$this->IsMyFriend($UserID))
			throw new ApplicationException("This person is not your friend.",500);

		$res = $this->GetFriendLink($UserID);

		return $res;
	}
 
		private function GetFriendLink($UserID = null) {
			$this->RequireLogin();
			if (null === $UserID)
				throw new ApplicationException("Missing param UserID for Scores::RemoveFriend!",406);
	
			$MyUserID = $this->AJBnet->User->GetUserID();
	
			$SQL = "SELECT * FROM `Scores_Friends` WHERE";
			$SQL .= "\n`R_UserID_Requester` = '{$MyUserID}' AND `R_UserID_Requestee` = '{$UserID}'";
			$SQL .= "\nOR";
			$SQL .= "\n`R_UserID_Requester` = '{$UserID}' AND `R_UserID_Requestee` = '{$MyUserID}';";
	
			return $this->SelectOne($SQL);
		}

	public function GetFriends() {
		$this->RequireLogin();

		$UserID = (int) $this->AJBnet->User->GetUserID();

		$SQL = "
			SELECT S.R_UserID_Requestee, S.R_UserID_Requester, U1.UserName as UN1, U2.UserName as UN2 FROM Scores_Friends S
			LEFT JOIN `User` U1 ON S.R_UserID_Requestee = U1.UserID
			LEFT JOIN `User` U2 ON S.R_UserID_Requester = U2.UserID
			WHERE
			`R_UserID_Requester` = '{$UserID}'
			OR
			`R_UserID_Requester` = '{$UserID}'
		";

		$Users = $this->Select($SQL);

		if (empty($Users))
			throw new ApplicationException("No friends found.",206);

		$Data = array(
			array(
				"uid" => $UserID,
				"username" => $this->AJBnet->User->GetUserName(),
				"score" => $this->GetScore($UserID)
			)
		);
		foreach($Users as $User) {

			if ($User['R_UserID_Requester'] == $UserID) {
				$_UserID = $User['R_UserID_Requestee'];
				$_UserName = $User['UN1'];
			} else {
				$_UserID = $User['R_UserID_Requester'];
				$_UserName = $User['UN2'];
			}

			$Data[] = array(
				"uid" => $_UserID,
				"username"=> $_UserName,
				"score"=> $this->GetScore($_UserID)
			);
		}
		return $Data;
	}

	public function FindFriend($UserName=null) {
		$this->RequireLogin();
		if (null === $UserName)
			$UserName = $this->GetID();
		if (empty($UserName))
			throw new ApplicationException("Missing ID for FindFriend!",406);
		$UserName = $this->Escape($UserName);
		$SQL = "SELECT UserID as uid, UserName as username FROM User WHERE UserName LIKE '%{$UserName}%' LIMIT 10;";
		$res = $this->Select($SQL);

		if (false === $res)
			throw new ApplicationException("No Friends found.",204);

		return $res;
	}

	/**
	 * Rate a user
	 * 
	 * @param integer $UserID
	 * @param integer $Type
	 * @return integer
	 * @internal
	 */
	private function _Rate($UserID,$Type) {
		$UserID = (int) $UserID;
		$Type = (int) $Type;
		$res = $this->Insert(array("Type" => $Type,"R_UserID" => $UserID));
		if (false === $res)
			throw new DatabaseException("Error inserting row!",500);
		return $this->GetScore($UserID);
	}

}

?>