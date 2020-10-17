<?php
declare(strict_types=1);

namespace twisted\bettervoting;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\scheduler\ClosureTask;
use twisted\bettervoting\thread\BetterVotingThread;

class BetterVotingListener implements Listener{

	/** @var BetterVoting */
	private $plugin;

	public function __construct(BetterVoting $plugin){
		$this->plugin = $plugin;
	}

	public function onPlayerJoin(PlayerJoinEvent $event) : void{
		if($this->plugin->getConfig()->get("join-check", true)) {
			$player = $event->getPlayer();
			if(BetterVotingCache::hasUnclaimedVote($player)) {
				$thread = $this->plugin->getVoteThread();
				$this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(static function () use ($player, $thread) : void {
					if($player->isOnline()) {
						$thread->addActionToQueue(BetterVotingThread::ACTION_CLAIM_VOTE, $player);
					}
				}), 20);
			}
		}
	}
}