<?php
declare(strict_types=1);

namespace Twisted\BetterVoting;

use DaPigGuy\PiggyCustomEnchants\enchants\CustomEnchantIds;
use DaPigGuy\PiggyCustomEnchants\PiggyCustomEnchants;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;
use Twisted\BetterVoting\events\PlayerVoteEvent;

class BetterVoting extends PluginBase{

	/** @var null|string $apiKey */
	private $apiKey = null;
	/** @var array $data */
	private $data = [];
	/** @var null|PiggyCustomEnchants $ce */
	private $ce;
	/** @var string[] $processing */
	private $processing = [];

	public function onEnable(): void{
		$config = $this->getConfig();
		if(empty($config->get("api-key"))) $this->getLogger()->error("Please give a valid API key in " . $this->getDataFolder() . "config.yml");
		else $this->apiKey = $config->get("api-key");
		if(!is_array($config->get("claim"))) $this->getLogger()->error("Please give a valid configuration in " . $this->getDataFolder() . "config.yml (Delete to reset)");
		else $this->data = $config->get("claim");
		$this->ce = $this->getServer()->getPluginManager()->getPlugin("PiggyCustomEnchants");
	}

	public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
		if(empty($args[0])){
			if(!$sender instanceof Player){
				$sender->sendMessage(TextFormat::RED . "Use '/vote reload', or use command in game");
				return false;
			}
			if($this->apiKey === null){
				$sender->sendMessage(TextFormat::RED . "This server has not provided a valid API key in their configuration");
				return false;
			}
			if(in_array($sender->getName(), $this->processing)){
				$sender->sendMessage(TextFormat::RED . "Your vote is already processing");
				return false;
			}
			$this->processing[spl_object_id($sender)] = $sender;
			$this->getServer()->getAsyncPool()->submitTask(new ProcessVoteTask($this->apiKey, $sender->getName()));
			return true;
		}
		switch($args[0]){
			case "reload":
				if(!$sender->hasPermission("bettervoting.reload")){
					$sender->sendMessage(TextFormat::RED . "You do not have permission to use this command");
					break;
				}
				$config = new Config($this->getDataFolder() . "config.yml", Config::YAML);
				if(empty($config->get("api-key"))){
					$this->getLogger()->error("Please give a valid API key in " . $this->getDataFolder() . "config.yml");
					$sender->sendMessage("Please give a valid API key in " . $this->getDataFolder() . "config.yml");
				}else $this->apiKey = $config->get("api-key");
				if(!is_array($config->get("claim"))){
					$this->getLogger()->error("Please give a valid configuration in " . $this->getDataFolder() . "config.yml (Delete to reset)");
					$sender->sendMessage("Please give a valid configuration in " . $this->getDataFolder() . "config.yml (Delete to reset)");
				}else $this->data = $config->get("claim");
				$sender->sendMessage(TextFormat::GREEN . "Configuration successfully reloaded");
				break;
			case "top":
				if($this->apiKey === null){
					$sender->sendMessage(TextFormat::RED . "This server has not provided a valid API key in their configuration");
					return false;
				}
				$display = (int)$this->getConfig()->getNested("top-votes.display");
				$this->getServer()->getAsyncPool()->submitTask(new GetTopVotersTask($sender->getName(), $this->apiKey, $display > 0 ? ($display > 500 ? 500 : $display) : 1));
				break;
		}
		return true;
	}

	public function translateMessage(string $message, Player $player): string{
		return str_replace([
			"{real-name}",
			"{display-name}",
			"&",
			"{x}",
			"{floor-x}",
			"{y}",
			"{floor-y}",
			"{z}",
			"{floor-z}",
		], [
			$player->getName(),
			$player->getDisplayName(),
			"§",
			$player->getX(),
			$player->getFloorX(),
			$player->getY(),
			$player->getFloorY(),
			$player->getZ(),
			$player->getFloorZ()
		], $message);
	}

	/**
	 * @return Item[]
	 */
	public function getItemRewards(): array{
		$items = $this->data["items"];
		if(!isset($items) || !is_array($items)){
			$this->getLogger()->error("Please give a valid item rewards [claim.items] array in " . $this->getDataFolder() . "config.yml (Delete to reset)");
			return [];
		}
		$rewards = [];
		foreach($items as $item){
			$info = explode(":", $item);
			if(empty($info[3])) break;
			$id = Item::class . "::" . strtoupper($info[0]);
			if(defined($id)) $reward = Item::get(constant($id), (int)$info[1], (int)$info[2]);
			else continue;
			if(strtolower($info[3]) !== "default") $reward->setCustomName($info[3]);
			$enchants = [];
			for($i = 0; $i < count($info); $i++){
				if($i > 3){
					$level = isset($info[$i + 1]) ? (int)$info[$i + 1] : 1;
					$enchants[strtolower($info[$i])] = $level;
					$i++;
				}
			}
			foreach($enchants as $enchant => $level){
				$id = Enchantment::class . "::" . strtoupper($enchant);
				if(!defined($id) && $this->ce !== null && $this->ce->isEnabled()) $id = CustomEnchantIds::class . "::" . strtoupper($enchant);
				if(defined($id)) $reward->addEnchantment(new EnchantmentInstance(Enchantment::getEnchantment(constant($id)), $level));
			}
			$rewards[] = $reward;
		}
		return $rewards;
	}

	public function claimVote(Player $player): void{
        $voteEvent = new PlayerVoteEvent($player);
        $voteEvent->call();
		$data = $this->data;
		if(isset($data["broadcast"])) $player->getServer()->broadcastMessage($this->translateMessage($data["broadcast"], $player));
		if(isset($data["message"])) $player->sendMessage($this->translateMessage($data["message"], $player));
		foreach($this->getItemRewards() as $reward){
			if($player->getInventory()->canAddItem($reward)) $player->getInventory()->addItem($reward);
			else $player->getLevel()->dropItem($player, $reward);
		}
		if(isset($data["commands"]) && is_array($data["commands"])) foreach($data["commands"] as $command) $this->getServer()->dispatchCommand(new ConsoleCommandSender(), $this->translateMessage($command, $player));
	}
	
	public function stopProcessing(Player $player): void{
		unset($this->processing[spl_object_id($player)]);
	}
}
