<?php
declare(strict_types=1);

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