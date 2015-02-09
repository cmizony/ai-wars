<?php
namespace AI_wars;

/* Supposition on turn numbers :
 * Linear continuous distribution in N [0,n]
 */

class AI_Game_history
{
	private $spells;	// Array of AI_Effect
	private $messages;	// Array of String
	private $players;	// Array of AI_Player_simulator

	public function __construct()
	{
		$this->reset();
	}

	public function reset()
	{
		$this->spells=array();
		$this->messages=array();
		$this->players=array();
	}

	public function add_spell($turn,$spell)
	{
		if(!isset($this->spells[$turn]))
			$this->spells[$turn]=array();
		array_push($this->spells[$turn],$spell);
	}

	public function add_message($turn,$message)
	{
		if(!isset($this->messages[$turn]))
			$this->messages[$turn]=array();
		array_push($this->messages[$turn],$message);
	}

	public function add_player($turn,$player)
	{
		if(!isset($this->players[$turn]))
			$this->players[$turn]=array();
		array_push($this->players[$turn],clone($player));
	}

	public function get_messages($turn)
	{
		if (!isset($this->messages[$turn]))
			return FALSE;
		return $this->messages[$turn];
	}
	public function get_players($turn)
	{
		if (!isset($this->players[$turn]))
			return FALSE;
		return $this->players[$turn];
	}

	public function get_spells($turn)
	{
		if (!isset($this->spells[$turn]))
			return FALSE;
		return $this->spells[$turn];
	}
}

?>
