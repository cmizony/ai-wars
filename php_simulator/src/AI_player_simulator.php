<?php
namespace AI_wars;
use Exception;

define('PLAYER_TURN_ENERGY',100);

class AI_Player_simulator extends AI_player
{
	public $stdin;	// FILE pointer
	public $stdout; // FILE pointer
	public $stderr;	// FILE pointer

	private static $count=1;	// int
	public $time_last_order;	// float

	public $name;		// String
	public $process;	// Resource php
	public $mainfile;	// String
	public $pid;		// int	
	public $language;	// String
	public $ready;		// Bool
	public $orders;		// Array of String

	public function __construct()
	{
		parent::__construct() ;
		$this->id=self::$count;
		self::$count++;

		$this->ready=FALSE;
		$this->orders=array();
		$this->instants=array();
		$this->time_last_order=microtime(TRUE)*1000;
	}

	public function send_text($string)
	{
		//TODO user a buffer limit unix kernel 16*4KB
		if (strlen($string) > 4096)
			throw new Exception("Error, string too long for some STDIN buffer : \"".str_replace("\n",'\\n',substr($string,0,50))."[...]\""); 
		fwrite($this->stdin,$string);
	}

	private function _get_line()
	{
		$line=trim(fgets($this->stdout));
		if ($line AND !empty($line))
			$this->time_last_order=microtime(TRUE)*1000;
		return $line;
	}

	public function recover_line()
	{
		$line=$this->_get_line();
		if ($line AND !empty($line))
			array_push($this->orders,$line);
		return $line;
	}

	public function flush_orders ()
	{
		$this->orders=array();
	}

	public function free()
	{
		_print("Kill player \"$this->name\" (process $this->pid)");

		if (is_resource($this->stdin))		fclose($this->stdin);
		if (is_resource($this->stdout))		fclose($this->stdout);
		if (is_resource($this->stderr))		fclose($this->stderr); 

		// kill php interpreter (created by proc_open)
		$status=proc_get_status($this->process);
		$return_value=posix_kill($status['pid'],SIGKILL);

		// Kill the real process pid
		return posix_kill($this->pid,SIGKILL);
	}

	public function cast_spell($spell,$p_target)
	{
		// Apply energy
		$this->energy-=$spell->energy;

		$effect = new AI_Effect($spell);
		$effect->duration=$spell->cast+1;
		$effect->p_target=$p_target;
		$effect->p_source=$this;
		$this->cast_bar[$spell->id]=$effect;

		return $effect;
	}

	public function regain_energy()
	{
		$this->energy += PLAYER_TURN_ENERGY;
		if($this->energy > PLAYER_MAX_ENERGY)
			$this->energy=PLAYER_MAX_ENERGY;
	}

	public function update_cooldowns()
	{
		foreach($this->cooldowns as $key=>&$effect)
		{
			if($effect->duration > 0)
				$effect->duration--;
			if($effect->duration <= 0)
				unset($this->cooldowns[$key]);
		}
	}

	public function update_cast_bar()
	{
		foreach($this->cast_bar as $key=>&$effect)
		{
			if($effect->duration <= 1)
			{
				$effect->do_cast();
				unset($this->cast_bar[$key]);
			}

			if($effect->duration > 1)
				$effect->duration--;

		}
	}

	public function update_buffs()
	{
		foreach($this->buffs as $key=>&$effect)
		{
			if($effect->duration > 0)
			{
				$effect->do_buff();
				$effect->duration--;
			}
			if($effect->duration <= 0)
				unset($this->buffs[$key]);
		}
	}

	public function update_debuffs()
	{
		foreach($this->debuffs as $key=>&$effect)
		{
			if($effect->duration > 0)
			{
				$effect->do_debuff();
				$effect->duration--;
			}
			if($effect->duration <= 0)
				unset($this->debuffs[$key]);
		}
	}
}

?>
