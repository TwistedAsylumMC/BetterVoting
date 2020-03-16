<?php
declare(strict_types=1);

namespace twisted\bettervoting\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use twisted\bettervoting\BetterVoting;
use twisted\bettervoting\BetterVotingCache;
use twisted\bettervoting\thread\BetterVotingThread;
use function count;
use function str_replace;

class VoteCommand extends Command{

	/** @var BetterVoting */
	private $plugin;

	public function __construct(BetterVoting $plugin){
		parent::__construct("vote", "Claim your vote on the server");

		$this->plugin = $plugin;
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(count($args) < 1){
			if(!$sender instanceof Player){
				$sender->sendMessage(TextFormat::colorize($this->plugin->getConfig()->getNested("messages.vote.usage.notplayer", "&cUse '/vote <info|reload|top>'.")));

				return;
			}

			if($this->plugin->getVoteThread()->isActionInQueue(BetterVotingThread::ACTION_VALIDATE_VOTE, $sender)){
				$sender->sendMessage(TextFormat::colorize($this->plugin->getConfig()->getNested("messages.vote.alreadyprocessing", "&cYour vote is already being processed.")));

				return;
			}

			$this->plugin->getVoteThread()->addActionToQueue(BetterVotingThread::ACTION_VALIDATE_VOTE, $sender);
			$sender->sendMessage(TextFormat::colorize($this->plugin->getConfig()->getNested("messages.vote.processing", "&aYour vote is being processed, please wait.")));

			return;
		}

		switch($args[0]){
			case "info":
				$info = BetterVotingCache::getServerInfo();
				$sender->sendMessage(TextFormat::colorize($this->plugin->getConfig()->getNested("messages.info.title", "&aThis server's vote information:")));
				$content = str_replace([
					"{uptime}",
					"{score}",
					"{rank}",
					"{votes}",
					"{favorited}",
					"{comments}",
				], [
					$info["uptime"] ?? "0%",
					$info["score"] ?? "0",
					$info["rank"] ?? "0",
					$info["votes"] ?? "0",
					$info["favorited"] ?? "0",
					$info["comments"] ?? "0",
				], $this->plugin->getConfig()->getNested("messages.info.content", "&aUptime: {uptime}\n&aScore: {score}\n&aRank: {rank}\n&aVotes: {votes}\n&aFavorited: {favorited}\n&aComments: {comments}"));
				$sender->sendMessage(TextFormat::colorize($content));

				break;
			case "reload":
				if(!$sender->hasPermission("bettervoting.command.reload")){
					$sender->sendMessage(TextFormat::colorize($this->plugin->getConfig()->getNested("messages.noperms", "&cYou do not have permission to use this command.")));

					return;
				}

				$this->plugin->loadConfig();
				$sender->sendMessage(TextFormat::colorize($this->plugin->getConfig()->getNested("messages.reload.success", "&aYou have reloaded the server's vote configuration.")));

				break;
			case "top":
				$top = BetterVotingCache::getTopVoters();
				if(count($top) < 1){
					$sender->sendMessage(TextFormat::colorize($this->plugin->getConfig()->getNested("messages.top.novoters", "&cThere are no top voters.")));

					return;
				}

				$sender->sendMessage(TextFormat::colorize($this->plugin->getConfig()->getNested("messages.top.title", "&aTop voters this month:")));
				$place = 1;
				foreach($top as $player => $votes){
					$format = str_replace([
						"{place}",
						"{username}",
						"{votes}"
					], [
						$place,
						$player,
						$votes
					], $this->plugin->getConfig()->getNested("messages.top.format", "&a{place}. {username}: {votes}"));
					$sender->sendMessage(TextFormat::colorize($format));
					++$place;
				}

				break;
			default:
				if($sender instanceof Player){
					if($sender->hasPermission("bettervoting.command.reload")){
						$sender->sendMessage(TextFormat::colorize($this->plugin->getConfig()->getNested("messages.vote.usage.reloadperm", "&cUse '/vote [info|reload|top].")));

						return;
					}
					$sender->sendMessage(TextFormat::colorize($this->plugin->getConfig()->getNested("messages.vote.usage.other", "&cUse '/vote [info|top].")));

					return;
				}
				$sender->sendMessage(TextFormat::colorize($this->plugin->getConfig()->getNested("messages.vote.usage.notplayer", "&cUse '/vote <info|reload|top>'.")));

				break;
		}
	}
}