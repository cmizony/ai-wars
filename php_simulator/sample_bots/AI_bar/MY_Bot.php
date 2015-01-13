<?php

require_once('libs/AI_game.php');

class MY_Bot
{
	private $game;

	/**********************/
	/*    Start setup     */
	/**********************/

	public function run ()
	{
		$this->game = new AI_Game();
		$this->game->receive_turn_info();
		$this->game->parse_game_setup();
		$this->game->finish_turn();

		for ($turn=1 ; $turn <= $this->game->max_turn ; $turn++)
		{
			$this->game->receive_turn_info();
			$this->game->parse_game_state();
			$this->do_turn();
			$this->game->finish_turn();
		}
	}

	/**********************/
	/* Personal functions */
	/**********************/

	private function get_first_ennemy ()
	{
		foreach ($this->game->players as $player)
			if ($player->team != $this->game->my_team)
				return $player;
	}

	private function check_and_cast ($spell_id,$target)
	{
		if (!isset($this->game->players[$this->game->my_id]->cooldowns[$spell_id]) AND
			!isset($this->game->players[$this->game->my_id]->cast_bar[$spell_id]))
		{
			$this->game->send_spell($this->game->my_id,$spell_id,$target->id);
			return TRUE;
		}
		return FALSE;
	}

	private function get_my_player ()
	{
		return $this->game->players[$this->game->my_id];
	}

	/**********************/
	/*      Turn AI       */
	/**********************/
	private function do_turn()
	{

		// Local variables
		$me = $this->get_my_player();
		$ennemy= $this->get_first_ennemy();

		if (!$ennemy)
			return; // Stop turn

		// Defensive spells first
		if ($me->life < PLAYER_MAX_LIFE*0.8) // 80%
			$this->check_and_cast(REPARING,$me);

		// Offensive spell if enough energy (always keep energy just in case)
		if ($me->energy > PLAYER_MAX_ENERGY*0.2) // 20%
		{
			$this->check_and_cast(LASER_SHOT,$ennemy);

			if (!isset($ennemy->debuffs[OFFENSIVE_BOTS]))
				$this->check_and_cast(OFFENSIVE_BOTS,$ennemy);
		}
	}
}

?>
