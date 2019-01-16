<?php
declare(strict_types=1);

namespace Twisted\BetterVoting;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;
use pocketmine\utils\Internet;
use pocketmine\utils\TextFormat;

class ProcessVoteTask extends AsyncTask{

	/** @var string $apiKey */
	private $apiKey;
	/** @var string $username */
	private $username;

	public function __construct(string $apiKey, string $username){
		$this->apiKey = $apiKey;
		$this->username = $username;
	}

	public function onRun(): void{
		$result = Internet::getURL("https://minecraftpocket-servers.com/api/?object=votes&element=claim&key=" . $this->apiKey . "&username=" . str_replace(" ", "+", $this->username));
		if($result === "1") Internet::getURL("https://minecraftpocket-servers.com/api/?action=post&object=votes&element=claim&key=" . $this->apiKey . "&username=" . str_replace(" ", "+", $this->username));
		$this->setResult($result);
	}

	public function onCompletion(Server $server): void{
		$result = $this->getResult();
		$player = $server->getPlayer($this->username);
		if($player === null) return;
		if(explode(" ", $result)[0] === "Error:"){
			$player->sendMessage(TextFormat::RED . "An error has occurred whilst trying to vote, contact an admin for support as it is most likely an issue with their API key.");
			return;
		}
		if($result === "0"){
			$player->sendMessage(TextFormat::RED . "You have not voted yet");
			return;
		}
		if($result === "2"){
			$player->sendMessage(TextFormat::RED . "You have already voted today");
			return;
		}
		/** @var BetterVoting $main */
		$main = $server->getPluginManager()->getPlugin("BetterVoting");
		$main->claimVote($player);
	}
}
