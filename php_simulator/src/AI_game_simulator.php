<?php namespace AI_wars;

use Exception;

define ('VERSION','0.1');
define ('REGEX_CAST','/^C [0-9]* [0-9]* [0-9]*$/');

$SIMULATOR_QUIET=FALSE; // Global variable

function _print($string)
{
	global $SIMULATOR_QUIET;
	if (!$SIMULATOR_QUIET)
		echo "$string\n";
}


class AI_Game_simulator
{
	private $simulator_config;	// Array of String
	private $game_config;		// Array of String
	private $bots_config;		// Array of String
	private $turn;				// int
	private $players;			// Array of AI_Player_simulator
	private $spells_validator;	// AI_Spells_validator

	private $game_history;
	private $quiet;

	public function __construct()
	{
		$this->players=array();
		$this->spells_validator=new AI_Spells_validator();
		$this->game_history=new AI_Game_history();
	}

	private function _initialize()
	{
		$this->turn=0;
		$this->players=array();
		$this->history=array();
		$this->spells_validator->initialize($this->simulator_config);
		$this->game_history->reset();
	}

	private function _loadConfig($config)
	{
		$this->simulator_config=array_shift($config);

		if($this->simulator_config['version'] != VERSION)
			throw new Exception("Error, different version from game simulator and config (version ".$this->simulator_config['version'].")\n");

		global $SIMULATOR_QUIET;
		$SIMULATOR_QUIET=(bool)$this->simulator_config['quiet'];

		$this->game_config=array_shift($config);
		$this->bots_config=$config;
	}

	public function run($config)
	{
		$this->_loadConfig($config);
		_print("Game engine simulator version ".VERSION);

		$this->_initialize();
		$this->_launchBots();
		
		$this->_playGame();
	}

	public function free()
	{
		$this->_freePlayers();
	}

	public function render($type)
	{
		switch ($type)
		{
		case "txt":
			return $this->_renderTxt();
			break;
		case "json":
			return $this->_renderJson();
			break;
		default:
			throw new Exception("Error, Simulator doesn't support \"$type\" format render");
			break;
		}
	}

	public function getPlayerAlives ()
	{
		$alives = array();

		foreach($this->players as $player)
			array_push($alives,$player->name);

		return $alives;
	}

	public function getTurn ()
	{
		return $this->turn;
	}
		
	private function _freePlayers()
	{
		foreach ($this->players as $player)
			$player->free();
	}

	private function _launchBots()
	{
		foreach ($this->bots_config as $player_ai)
		{
			global $SIMULATOR_QUIET;

			// Step 0 : check if IA exist
			if (!file_exists($player_ai['mainfile']))
				throw new AI_Exception("Error, AI \"".$player_ai['mainfile']."\" doesn't exist");

			$player = new AI_Player_simulator();
			$player->mainfile=$player_ai['mainfile'];
			$player->name=$player_ai['name'];
			$player->team=(int)$player_ai['team'];

			// Step 1 : set up language and cmd
			$language=$player_ai['language'];
			$player->language=$language;

			switch($language)
			{
			case 'php':
				$cmd='/usr/bin/php5 '.$player_ai['mainfile'];
				break;
			case 'bash':
				$cmd='/bin/bash '.$player_ai['mainfile'];
				break;
			case 'c':
				$cmd=$player_ai['mainfile'];
				break;
			default:
				throw new Exception("Error, language \"$language\" not supported");
				break;
			}

			// Step 2 : Lauch process

			$descriptors = array(
				0 => array("pipe", "r"),  // stdin
				1 => array("pipe", "w")  // stdout
			);

			if ($SIMULATOR_QUIET)
				$descriptors[2]=array("pipe","r"); // handle stderr from bot

			$process = proc_open($cmd, $descriptors, $pipes, NULL, NULL);

			if (!is_resource($process))
				throw new Exception("Error, unable to launch bot \"".$player_ai['mainfile']."\"");

			$player->stdin=$pipes[0];
			$player->stdout=$pipes[1];
			if ($SIMULATOR_QUIET)
				$player->stderr=$pipes[2];

			stream_set_blocking($player->stdout,0);
			if ($SIMULATOR_QUIET)
				stream_set_blocking($player->stderr,0); 

			$status=proc_get_status($process);

			$player->process=$process;
			$player->pid=$status['pid']+1; //Because proc_open open a new php process

			usleep(2500); // Wait php interpreter create child process

			array_push($this->players,$player);
		}
		shuffle($this->players);
		_print(count($this->players)." bots launched");
	}

	private function _botsSetup()
	{
		_print("Start bots setup phase (max ".$this->game_config['loadtime']."ms)");
		foreach($this->players as $player)
		{
			$player->sendtext(
				"id $player->id\nteam $player->team\nturns ".$this->game_config['turns']."\nturntime ".$this->game_config['turntime']."\nloadtime ".$this->game_config['loadtime']."\ngo\n");
		}

		//Wait for go orders
		$this->_receiveOrders($this->game_config['loadtime']);

		foreach($this->players as $key=>$player)
		{
			$player->flushOrders();
			if (!$player->ready)
			{
				$player->free();
				unset($this->players[$key]);
			}
			$this->game_history->addPlayer($this->turn,$player);
		}
		_print(count($this->players)." bot(s) succed setup phase");
	}

	private function _isGameFinish()
	{
		$end=((count($this->players) == 0) OR
			($this->turn > $this->game_config['turns']));

		// Only one team remain
		if (!$end)
		{
			$team=reset($this->players)->team;
			foreach($this->players as $player)
				if($player->team != $team)
					return FALSE;
			$end=TRUE;
		}

		if ($end)
			_print("End game (at the end or turn ".($this->turn-1).")");

		return $end;	
	}

	private function _playGame()
	{
		// Turn 0
		$this->_botsSetup();
		$this->turn++;
		_print("Start game (max ".$this->game_config['turns']." turns)");

		// Turn n in [1;max_turn]
		while(!$this->_isGameFinish())
		{
			_print("Turn $this->turn (".count($this->players)." players)");
			$this->_sendGameState();

			$this->_receiveOrders($this->game_config['turntime']);
			$this->_updateGame();

			$this->turn++;
		}
	}

	private function _sendGameState()
	{
		foreach($this->players as $player_order)
		{
			$player_order->sendText("T $this->turn\n");
			foreach($this->players as $player)
				$player_order->sendText($player."\n");
		}

		//send all go at the same time
		foreach($this->players as $player) 
			$player->sendText("go\n");
	}



	private function _receiveOrders($max_time)
	{
		foreach($this->players as $player)
			$player->ready=FALSE;

		$start_time=microtime(TRUE)*1000;
		do
		{
			usleep(($max_time*1000)/10);
			$time_elapsed=(microtime(TRUE)*1000)-$start_time;
			$players_ready=TRUE;

			foreach($this->players as $player)
			{
				if ($player->ready)
					continue;

				$line=$player->recoverLine();
				$player->ready=($line==="go");
				$players_ready&=$player->ready;
			}
		}while(!$players_ready AND
			($time_elapsed<((int)$max_time)));

		// First come first served ...
		AI_player::sortByProperty($this->players,'time_last_order');
	}

	private function _updateGame()
	{
		foreach($this->players as $key=>$player)
		{
			// Check "go" order
			if (array_pop($player->orders)!=="go")
			{
				_print("AI_player $player->name crashed (turn time limit)");
				$player->free();
				unset($this->players[$key]);
				continue;
			}

			// Check and apply orders
			foreach($player->orders as $order)
			{
				if(!preg_match(REGEX_CAST,$order))
				{
					_print("Syntax order from player $player->name not valid ($order)");
					continue;
				}

				sscanf($order,"C %d %d %d",$p_source_id,$spell_id,$p_target_id);

				if($p_source_id != $player->id)
					_print("Spell error from player $player->name, source id \"$p_source_id\" invalid)");	
				$p_target=AI_player::searchByProperty($this->players,'id',$p_target_id);
				if (!$p_target)
				{
					_print("Spell error from player $player->name, target not found (id $p_target_id)");	
					continue;
				}

				// Cast succed & save into game history
				$spell=$this->spells_validator->validSpell($player,$spell_id);
				if ($spell)
				{
					$effect=$player->castSpell($spell,$p_target);
					$this->game_history->addSpell($this->turn,$effect);
				}
			}
			$player->flushOrders();

			// Update order effects
			AI_Effect::sortByProperty($this->cast_bar,'date');
			$player->updateCastBar();
		}

		// Apply effects to player
		foreach($this->players as $player)
		{
			AI_Effect::sortByProperty($this->buffs,'date');
			$player->updateBuffs();

			AI_Effect::sortByProperty($this->debuffs,'date');
			$player->updateDebuffs();

			// Update cooldowns and effects duration
			AI_Effect::sortByProperty($this->cooldowns,'date'); 
			$player->updateCooldowns();
			$player->regainEnergy();

		}

		//Remove dead players
		foreach($this->players as $key=>$player)
		{
			$this->game_history->addPlayer($this->turn,$player);
			_print("  ".$player);
			if ($player->life <= 0)
			{
				$player->free();
				unset($this->players[$key]);
			}
		}
	}

	private function _renderTxt()
	{
		$txt='';
		for($i=0;$i < $this->turn;$i++)
		{
			$txt.="turn $i\n";

			$spells=$this->game_history->getSpells($i);
			if ($spells)
				foreach($spells as $spell)
					$txt.=(string)$spell."\n";

			$players=$this->game_history->getPlayers($i);
			AI_player::sortByProperty($players,'id');
			if ($players)
				foreach($players as $player)
					$txt.=(string)$player."\n";
		}
		
		return $txt;
	}

	private function _renderJson()
	{
		$json="
		{\"game\":
			{
			\"version\": ".VERSION.",
			\"static\":";	
		$json.=$this->spells_validator->toJson();
		$json .=",
			\"turns\":
			[";

		for($i=0;$i < $this->turn;$i++)
		{
			$json.="
				{ 
				\"number\": $i,
				\"players\": 
					[";

			$players=$this->game_history->getPlayers($i);
			AI_player::sortByProperty($players,'id');
			if ($players)
				foreach($players as $player)
				{
					$json.=$player->toJson();
					$json.=',';
				}
			$json=rtrim($json,',');
			$json.='
					]
				},';
		}
		$json=rtrim($json,',');
		$json.="
			]
			}
		}";

		$json=preg_replace('/\s\s+/', '', $json); 
		
		return $json;
	}
}

?>
