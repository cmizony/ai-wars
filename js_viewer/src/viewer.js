/****************************/
/*     Global properties    */
/****************************/
if (typeof PLAYER_MAX_LIFE == 'undefined')
{
	const PLAYER_MAX_LIFE=100;
	const PLAYER_MAX_ENERGY=100;
}

/****************************/
/*     Public functions     */
/****************************/

function loadGame (json_game,dom_game_viewer,players_info)
{
	// Loading json with errors handling
	try
	{
		var game_viewer  = json_game.game; 
	}
	catch(e)
	{
		return false;
	}

	if (typeof game_viewer == "undefined")
		return false;

	// Create panel
	dom_panel = document.createElement('div');
	dom_panel.className = 'panel';

	dom_pager = document.createElement('ul');
	dom_pager.className = 'pager';

	dom_li_turn = document.createElement('li');
	dom_button_turn = document.createElement('a');
	dom_button_turn.setAttribute('href','#');
	dom_button_turn.innerHTML='0/'+(game_viewer.turns.length-1);
	dom_li_turn.appendChild(dom_button_turn);

	dom_li_next = document.createElement('li');
	dom_button_next = document.createElement('a');
	dom_button_next.setAttribute('href','#');
	dom_button_next.innerHTML='Next';
	dom_button_next.addEventListener('click',function(){loadNextTurn(game_viewer,dom_button_turn);}, false);
	dom_li_next.appendChild(dom_button_next);

	dom_li_previous = document.createElement('li');
	dom_button_previous = document.createElement('a');
	dom_button_previous.setAttribute('href','#');
	dom_button_previous.innerHTML='Previous';
	dom_button_previous.addEventListener('click',function(){loadPreviousTurn(game_viewer,dom_button_turn);}, false);
	dom_li_previous.appendChild(dom_button_previous);

	dom_pager.appendChild(dom_li_previous);
	dom_pager.appendChild(dom_li_turn);
	dom_pager.appendChild(dom_li_next);
	dom_panel.appendChild(dom_pager);

	// Re-index spells array to have constant access O(1)
	var indexed_spells = new Array();
	for(var i=0 ; i < game_viewer.static.spells.length ; i++)
	{
		var spell = game_viewer.static.spells[i];
		indexed_spells[spell.id]=spell;
	}
	game_viewer.static.spells=indexed_spells;

	// Load players form setup turn
	var players = game_viewer.turns[0].players;

	var dom_turn = document.createElement('div');
	dom_turn.id='turn';
	dom_turn.setAttribute('data-number','0');

	for(var i=0 ; i < players.length ; i ++)
	{
		var player = players[i];

		// Create elements
		var dom_player = document.createElement('div');
		var dom_name = document.createElement('h4');
		var dom_life = document.createElement('div');
		var dom_life_bar = document.createElement('div');
		var dom_energy = document.createElement('div');
		var dom_energy_bar = document.createElement('div');
		var cast_bar = document.createElement('div');
		var buffs = document.createElement('div');
		var debuffs = document.createElement('div');
		var cooldowns = document.createElement('div');

		// Set attributes
		dom_player.className='player well';
		dom_player.id='player-'+player.id;
		dom_player.setAttribute('data-id',player.id);
		dom_player.setAttribute('data-team',player.team);

		dom_name.className="info";
		dom_name.innerHTML=(typeof players_info == "undefined")?
			'anonymous':
			players_info[i].name;

		dom_life.className='progress life';
		dom_life_bar.className='bar bar-success';
		dom_life_bar.setAttribute('style','width: '+(player.life*100/PLAYER_MAX_LIFE)+'%');
		dom_life_bar.innerHTML = player.life+'/'+PLAYER_MAX_LIFE;
		dom_life.appendChild(dom_life_bar);

		dom_energy.className='progress energy';
		dom_energy_bar.className='bar';
		dom_energy_bar.setAttribute('style','width: '+(player.energy*100/PLAYER_MAX_ENERGY)+'%');
		dom_energy_bar.innerHTML = player.energy+'/'+PLAYER_MAX_ENERGY;
		dom_energy.appendChild(dom_energy_bar);

		cast_bar.className='cast_bar';
		cast_bar.innerHTML='<span class="muted">Casts: </span>';
		buffs.className='buffs';
		buffs.innerHTML='<span class="muted">Buffs: </span>';
		debuffs.className='debuffs';
		debuffs.innerHTML='<span class="muted">Debuffs: </span>';
		cooldowns.className='cooldowns';
		cooldowns.innerHTML='<span class="muted">Cooldowns: </span>';

		// Append childs
		dom_player.appendChild(dom_name);
		dom_player.appendChild(dom_life);
		dom_player.appendChild(dom_energy);
		dom_player.appendChild(cast_bar);
		dom_player.appendChild(buffs);
		dom_player.appendChild(debuffs);
		dom_player.appendChild(cooldowns);
		dom_turn.appendChild(dom_player);
	}

	//Remove previous content
	dom_game_viewer.innerHTML = "";

	//Add new viewer
	dom_game_viewer.appendChild(dom_turn);
	dom_game_viewer.appendChild(dom_panel);

	return true;
}

/****************************/
/*    Protected functions   */
/****************************/

function loadJsonFile(event,dom_game_viewer) 
{
	var file = event.target.files[0];

	if (file.length === 0)
		alert('Error, empty file');

	if (!file.type.match('application/json')) 
		alert('Error, not json file');

	var reader = new FileReader();

	reader.readAsText(file);
	reader.onload = function (event) //callback function
	{
		loadGame (JSON.parse(event.target.result),dom_game_viewer);
	}
}	

function loadNextTurn(game_viewer,dom_button_turn)
{
	var dom_turn = document.getElementById('turn');
	var new_turn_number = parseInt(dom_turn.getAttribute('data-number')) + 1;

	if ( new_turn_number >= game_viewer.turns.length)
		return;

	var players = game_viewer.turns[new_turn_number].players;
	updateGameState(game_viewer,players);

	dom_turn.setAttribute('data-number',new_turn_number);
	dom_button_turn.innerHTML=new_turn_number+'/'+(game_viewer.turns.length-1);
}

function loadPreviousTurn(game_viewer,dom_button_turn)
{
	var dom_turn = document.getElementById('turn');
	var new_turn_number = parseInt(dom_turn.getAttribute('data-number')) - 1;

	if (new_turn_number < 0)
		return;

	var players = game_viewer.turns[new_turn_number].players;
	updateGameState(game_viewer,players);

	dom_turn.setAttribute('data-number',new_turn_number);
	dom_button_turn.innerHTML=new_turn_number+'/'+game_viewer.turns.length;
}

/*****************************/
/*     Private functions     */
/*****************************/

function updateGameState (game_viewer,players)
{
	for(var i=0 ; i < players.length ; i++)
	{
		var player = players[i];
		var dom_player = document.getElementById('player-'+player.id);
		
		for(var j=0 ; j < dom_player.childNodes.length ; j++)
		{
			var dom_node = dom_player.childNodes.item(j);
			var class_name = dom_node.getAttribute('class');

			if (class_name.indexOf('life') != -1)
			{
				dom_life_bar = dom_node.firstChild;
				dom_life_bar.innerHTML = player.life+'/'+PLAYER_MAX_LIFE;
				dom_life_bar.setAttribute('style','width: '+(player.life*100/PLAYER_MAX_LIFE)+'%');

				if (player.life < PLAYER_MAX_ENERGY*0.4)
					dom_life_bar.className='bar bar-warning';
				if (player.life < PLAYER_MAX_ENERGY*0.15)
					dom_life_bar.className='bar bar-danger';
				if (player.life > PLAYER_MAX_ENERGY*0.4)
					dom_life_bar.className='bar bar-success';
			}
			else if (class_name.indexOf('energy') != -1)
			{
				dom_energy_bar = dom_node.firstChild;
				dom_energy_bar.innerHTML = player.energy+'/'+PLAYER_MAX_ENERGY;
				dom_energy_bar.setAttribute('style','width: '+(player.energy*100/PLAYER_MAX_ENERGY)+'%');
			}
			else if (class_name.indexOf('cast_bar') != -1)
				updateCastbar(game_viewer,dom_node,player.cast_bar);
			else if (class_name.indexOf('debuffs') != -1)
				updateDebuffEffects(game_viewer,dom_node,player.debuffs);
			else if (class_name.indexOf('buffs') != -1)
				updateBuffEffects(game_viewer,dom_node,player.buffs);
			else if (class_name.indexOf('cooldowns') != -1)
				updateCooldownEffects(game_viewer,dom_node,player.cooldowns);
		}
	}
}

function updateBuffEffects (game_viewer,dom_node,effects)
{
	for (var i=0 ; i < dom_node.childNodes.length ; i++)
		dom_node.removeChild(dom_node.childNodes[i]);

	dom_node.innerHTML='<span class="muted">Buffs: </span>';

	for (var i=0 ; i < effects.length ; i++)
	{
		var effect = effects[i];
		var dom_effect = document.createElement('span');

		var dom_spell = document.createElement('i');
		dom_spell.className='icon-chevron-up icon-white';
		dom_effect.appendChild(dom_spell);

		dom_effect.className='effect badge badge-info';
		dom_effect.innerHTML+=' '+game_viewer.static.spells[effect.spell_id].name.toLowerCase().replace('_',' ')+
			' '+effect.duration;

		dom_effect.setAttribute('data-toggle','tooltip');
		dom_effect.setAttribute('title',game_viewer.static.spells[effect.spell_id].effect);

		dom_node.appendChild(dom_effect);
	}
}

function updateDebuffEffects (game_viewer,dom_node,effects)
{
	for (var i=0 ; i < dom_node.childNodes.length ; i++)
		dom_node.removeChild(dom_node.childNodes[i]);

	dom_node.innerHTML='<span class="muted">Debuffs: </span>';

	for (var i=0 ; i < effects.length ; i++)
	{
		var effect = effects[i];
		var dom_effect = document.createElement('span');

		var dom_spell = document.createElement('i');
		dom_spell.className='icon-chevron-down icon-white';
		dom_effect.appendChild(dom_spell);

		dom_effect.className='effect badge badge-important';
		dom_effect.innerHTML+=' '+game_viewer.static.spells[effect.spell_id].name.toLowerCase().replace('_',' ')+
			' '+effect.duration;

		dom_effect.setAttribute('data-toggle','tooltip');
		dom_effect.setAttribute('title',game_viewer.static.spells[effect.spell_id].effect);

		dom_node.appendChild(dom_effect);
	}
}

function updateCooldownEffects (game_viewer,dom_node,effects)
{
	for (var i=0 ; i < dom_node.childNodes.length ; i++)
		dom_node.removeChild(dom_node.childNodes[i]);

	dom_node.innerHTML='<span class="muted">Cooldowns: </span>';

	for (var i=0 ; i < effects.length ; i++)
	{
		var effect = effects[i];
		var dom_effect = document.createElement('span');

		var dom_spell = document.createElement('i');
		dom_spell.className='icon-time icon-white';
		dom_effect.appendChild(dom_spell);

		dom_effect.className='effect badge';
		dom_effect.innerHTML+=' '+game_viewer.static.spells[effect.spell_id].name.toLowerCase().replace('_',' ')+
			' '+effect.duration;

		dom_effect.setAttribute('data-toggle','tooltip');
		dom_effect.setAttribute('title',game_viewer.static.spells[effect.spell_id].effect);

		dom_node.appendChild(dom_effect);
	}
}

function updateCastbar(game_viewer,dom_node,cast_bar)
{
	for (var i=0 ; i < dom_node.childNodes.length ; i++)
		dom_node.removeChild(dom_node.childNodes[i]);

	dom_node.innerHTML='<span class="muted">Casts: </span>';

	for (var i=0 ; i < cast_bar.length ; i++)
	{
		var effect = cast_bar[i];
		var dom_effect = document.createElement('span');

		var dom_spell = document.createElement('i');
		dom_spell.className='icon-repeat icon-white';
		dom_effect.appendChild(dom_spell);

		dom_effect.className='effect badge badge-warning';
		dom_effect.innerHTML+=' '+game_viewer.static.spells[effect.spell_id].name.toLowerCase().replace('_',' ')+
			' '+effect.duration;

		dom_effect.setAttribute('data-toggle','tooltip');
		dom_effect.setAttribute('title',game_viewer.static.spells[effect.spell_id].effect);

		dom_node.appendChild(dom_effect);
	}
}
