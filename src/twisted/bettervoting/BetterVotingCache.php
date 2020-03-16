<?php
declare(strict_types=1);

namespace twisted\bettervoting;

use pocketmine\item\Item;
use pocketmine\Player;
use function in_array;

class BetterVotingCache{

	public const TIME_BETWEEN_CACHE_UPDATE = 3 * 60;

	/** @var array */
	private static $serverInfo = [];
	/** @var array */
	private static $topVoters = [];
	/** @var string[] */
	private static $unclaimedVotes = [];

	/** @var string[] */
	private static $commands = [];
	/** @var Item[] */
	private static $items = [];

	/**
	 * Returns the server info as an array, with the following keys:
	 *  "uptime" - The percentage of the server uptime
	 *  "score" - The monthly score of the server
	 *  "rank" - The rank of the server on the list (based on score)
	 *  "votes" - The number of votes in the current month
	 *  "favorited" - The number of people who have favourited the server
	 *  "comments" - The number of comments left on the server
	 *
	 * @return array
	 */
	public static function getServerInfo() : array{
		return self::$serverInfo;
	}

	/**
	 * This should only be called from the BetterVotingThread, it will
	 *  update every 3 minutes with an array the server's voting info.
	 *
	 * @param array $serverInfo
	 */
	public static function setServerInfo(array $serverInfo) : void{
		self::$serverInfo = $serverInfo;
	}

	/**
	 * Returns the top 10 voters of the current month.
	 * This will automatically update once every 3 minutes.
	 *
	 * @return int[]
	 */
	public static function getTopVoters() : array{
		return self::$topVoters;
	}

	/**
	 * This should only be called from the BetterVotingThread, it will
	 *  update every 3 minutes with an array of the top 10 voters this month.
	 *
	 * @param int[] $topVoters
	 */
	public static function setTopVoters(array $topVoters) : void{
		self::$topVoters = $topVoters;
	}

	/**
	 * Returns an array of player names that have not claimed their
	 *  votes. This will automatically update once every 3 minutes.
	 *
	 * @return array string[]
	 */
	public static function getUnclaimedVotes() : array{
		return self::$unclaimedVotes;
	}

	/**
	 * Returns whether the player has an unclaimed vote or not, this
	 *  should only be used for automatic claiming since it will
	 *  only update once every 3 minutes due to the API caching.
	 *
	 * @param Player $player
	 *
	 * @return bool
	 */
	public static function hasUnclaimedVote(Player $player) : bool{
		return in_array($player->getName(), self::$unclaimedVotes);
	}

	/**
	 * This should only be called from the BetterVotingThread, it
	 *  will update every 3 minutes with an array of unclaimed votes.
	 *
	 * @param string[] $unclaimedVotes
	 */
	public static function setUnclaimedVotes(array $unclaimedVotes) : void{
		self::$unclaimedVotes = $unclaimedVotes;
	}

	/**
	 * The commands to be executed via console
	 *  when a player claims their vote.
	 *
	 * @return string[]
	 */
	public static function getCommands() : array{
		return self::$commands;
	}

	/**
	 * Set the commands to be executed by console
	 *  when a player claims their vote.
	 *
	 * @param string[] $commands
	 */
	public static function setCommands(array $commands) : void{
		self::$commands = $commands;
	}

	/**
	 * An array of items to be added to the player's
	 *  inventory when they claim their vote.
	 *
	 * @return Item[]
	 */
	public static function getItems() : array{
		return self::$items;
	}

	/**
	 * Set the list of items that will be added to a
	 *  player's inventory when they claim their vote.
	 *
	 * @param Item[] $items
	 */
	public static function setItems(array $items) : void{
		self::$items = $items;
	}
}