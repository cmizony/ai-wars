<?php namespace AI_wars;

define('REGEX_TURN_NUMBER','/^T [0-9]*$/');
define('SUB_REGEX_EFFECT','(\([0-9]*;[0-9]*\) ?)');
define('REGEX_EFFECT','/'.SUB_REGEX_EFFECT.'/');
define('REGEX_PLAYER_STATE','/^P [0-9]* [0-9]* [0-9]* [0-9]*( B '.
	SUB_REGEX_EFFECT.'+)?( D '.
	SUB_REGEX_EFFECT.'+)?( C '.
	SUB_REGEX_EFFECT.'+)?( CD '.
	SUB_REGEX_EFFECT.'+)?$/');

define('REGEX_SETUP_ID','/^id [0-9]*$/');
define('REGEX_SETUP_TEAM','/^team [0-9]*$/');
define('REGEX_SETUP_TURNS','/^turns [0-9]*$/');
define('REGEX_SETUP_TURNTIME','/^turntime [0-9]*$/');
define('REGEX_SETUP_LOADTIME','/^loadtime [0-9]*$/');

class AI_Game
{
	protected $stdin;			// FILE stdin
	protected $stderr;			// FILE stderr
	protected $server_infos;	// Array of String

	public $players;		// Array of AI_player
	public $turn;			// Integer

	public $my_id;			// Integer
	public $my_team;		// Integer
	public $max_turn;		// Integer
	public $turn_time;		// Integer
	public $load_time;		// Integer

	public function __construct()
	{
		$this->stdin = fopen('php://stdin', 'r');
		$this->stderr = fopen('php://stderr', 'w');

		$this->server_infos=array();
		$this->players=array();
		$this->turn=0;

		$this->my_id=$this->my_team=$this->max_turn=$this->turn_time=$this->load_time=0;
	}

	public function receiveTurnInfo ()
	{
		$lines=array();

		while(TRUE)
		{
			$line=fgets($this->stdin);

			if ($line==="go\n")
				break;

			array_push($lines,trim($line));
			usleep(100);
		}
		$this->server_infos=$lines;

		return $lines;
	}

	public function parseGameState()
	{
		foreach($this->server_infos as $info)
		{
			if (preg_match(REGEX_TURN_NUMBER,$info))
			{
				sscanf($info,"T %d",$turn);
				$this->turn=intval($turn);
				continue;
			}

			if (preg_match(REGEX_PLAYER_STATE,$info))
			{
				sscanf($info,"P %d %d %d %d",
					$id,
					$team,
					$life,
					$energy);

				if (!isset($this->players[$id]))
					$this->players[$id] = new AI_player();

				$player = $this->players[$id];
				$player->id=$id;
				$player->team=$team;
				$player->life=$life;
				$player->energy=$energy;

				$B_pos = strpos($info,'B');
				$D_pos = strpos($info,'D');
				$C_pos = strpos($info,'C');
				$CD_pos = strpos($info,'CD');
				$spells_pos=array(
					'B' => $B_pos,
					'D' => $D_pos,
					'C' => $C_pos,
					'CD' =>$CD_pos);

				if ($B_pos)
				{
					unset($spells_pos['B']);
					$string=substr($info,$B_pos,$this->_posMin($spells_pos,strlen($info))-$B_pos);
					$player->buffs=$this->_extractEffect($string);
				}
				if ($D_pos)
				{
					unset($spells_pos['D']);
					$string=substr($info,$D_pos,$this->_posMin($spells_pos,strlen($info))-$D_pos);
					$player->debuffs=$this->_extractEffect($string);
				}
				if ($C_pos)
				{
					unset($spells_pos['C']);
					$string=substr($info,$C_pos,$this->_posMin($spells_pos,strlen($info))-$C_pos);
					$player->cast_bar=$this->_extractEffect($string);
				}
				if ($CD_pos)
				{
					unset($spells_pos['CD']);
					$string=substr($info,$CD_pos,$this->_posMin($spells_pos,strlen($info))-$CD_pos);
					$player->cooldowns=$this->_extractEffect($string);
				}

			}
		}
	}

	private function _posMin ($array,$max)
	{
		$min=$max;
		foreach($array as $value)
			if ($value AND $value < $min)
				$min=$value	;
		return $min;
	}

	private function _extractEffect($string)
	{
		$extracted_effects=array();
		$regex_effects=array();

		preg_match_all(REGEX_EFFECT,$string,$regex_effects);
		foreach ($regex_effects[0] as $effect)
		{
			sscanf($effect,"(%d;%d)",$spell_id,$duration);
			$spell = new AI_Spell();
			$spell->id=$spell_id;
			$spell->duration=$duration;
			$extracted_effects[$spell_id]=$spell;
		}

		return $extracted_effects;
	}

	public function parseGameSetup()
	{
		foreach($this->server_infos as $info)
		{
			if (preg_match(REGEX_SETUP_ID,$info))
			{
				sscanf($info,"id %d",$id);
				$this->my_id=intval($id);
				continue;
			}
			if (preg_match(REGEX_SETUP_TEAM,$info))
			{
				sscanf($info,"team %d",$team);
				$this->my_team=intval($team);
				continue;
			}
			if (preg_match(REGEX_SETUP_TURNS,$info))
			{
				sscanf($info,"turns %d",$turns);
				$this->max_turn=intval($turns);
				continue;
			}
			if (preg_match(REGEX_SETUP_TURNTIME,$info))
			{
				sscanf($info,"turntime %d",$turntime);
				$this->turn_time=intval($turntime);
				continue;
			}
			if (preg_match(REGEX_SETUP_LOADTIME,$info))
			{
				sscanf($info,"loadtime %d",$loadtime);
				$this->load_time=intval($loadtime);
				continue;
			}
		}
	}

	public function finishTurn()
	{
		echo "go\n";
	}

	public function printDebug ($string)
	{
		fwrite($this->stderr,"$string\n");
	}

	public function sendSpell($source_id,$spell_id,$target_id)
	{
		echo "C $source_id $spell_id $target_id\n";
	}
}

?>
