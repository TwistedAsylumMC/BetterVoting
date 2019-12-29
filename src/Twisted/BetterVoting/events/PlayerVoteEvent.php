<?php
declare(strict_types=1);

namespace Twisted\BetterVoting\events;

use pocketmine\Player;

class PlayerVoteEvent extends BetterVotingEvent
{

    public function __construct(Player $player)
    {
        parent::__construct($player);
    }
}