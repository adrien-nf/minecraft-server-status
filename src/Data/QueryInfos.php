<?php

namespace Data;

class QueryInfos extends PingInfos
{
	const FIELDS = [
		"hostname" => "motd",
		"gametype" => "gameType",
		"version" => "version",
		"plugins" => "plugins",
		"map" => "worldName",
		"numplayers" => "playersCount",
		"maxplayers" => "maxPlayers",
		"hostport" => "port",
		"hostip" => "ip",
		"game_id" => "gameName"
	];

	protected string $gameType;
	protected array $plugins;
	protected string $worldName;

	public function __construct(array $infos, array $players)
	{
		parent::__construct(array_merge($infos, ["players" => $players]));

		$this->gameType = $infos["gameType"];
		$this->plugins = $infos["plugins"];
		$this->worldName = $infos["worldName"];
	}

	public function getGameType(): string
	{
		return $this->gameType;
	}

	public function getPlugins(): array
	{
		return $this->plugins;
	}

	public function getPluginsAsString($withVersion = false): string
	{
		return implode(", ", $this->getPluginsAsArray($withVersion));
	}

	public function getPluginsAsArray($withVersion = false): array
	{
		return array_map(function ($line) use ($withVersion) {
			if ($withVersion) return $line["name"] . " " . $line["version"];

			return $line["name"];
		}, $this->getPlugins());
	}

	public function hasPlugin(string $name)
	{
		return in_array($name, $this->getPluginsAsArray());
	}

	public function getWorldName(): string
	{
		return $this->worldName;
	}

	public function getMap(): string
	{
		return $this->getWorldName();
	}

	public function toArray(): array
	{
		return array_merge(parent::toArray(), [
			"gameType" => $this->getGameType(),
			"plugins" => $this->getPlugins(),
			"worldName" => $this->getWorldName()
		]);
	}
}
