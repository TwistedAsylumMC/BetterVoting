<?php
/**
 *      ___        __  __              ____  ______
 *     /   | ___  / /_/ /_  ___  _____/ __ \/ ____/
 *    / /| |/ _ \/ __/ __ \/ _ \/ ___/ /_/ / __/
 *   / ___ /  __/ /_/ / / /  __/ /  / ____/ /___
 *  /_/  |_\___/\__/_/ /_/\___/_/  /_/   /_____/
 *
 * Copyright (c) KingOfTurkey38 2019.
 * Last Modified 29/12/19 23:53
 *
 * This plugin is developed and maintained by KingOfTurkey38.
 *
 * This plugin is distributed under a commercial license and you are only allowed to use it in the way and
 * extent and under conditions explicitly agreed on with KingOfTurkey38 in writing.
 *
 * By the license, you're not allowed to remove this documented snippet.
 *
 */

namespace Twisted\BetterVoting\events;

use pocketmine\event\Event;
use pocketmine\Player;

abstract class BetterVotingEvent extends Event
{
    /** @var Player */
    protected $player;

    public function __construct(Player $player)
    {
        $this->player = $player;
    }

    public function getPlayer(): Player
    {
        return $this->player;
    }
}