<?php

namespace Data;

class PingInfos
{
	protected string $version;
	protected string $serverType;
	protected string $motd;
	protected int $maxPlayers;
	protected int $playersCount;
	protected string $ip;
	protected int $port;
	protected array $players;

	public function __construct(array $infos)
	{
		$this->version = $infos["version"];
		$this->serverType = $infos["serverType"];
		$this->motd = $infos["motd"];
		$this->maxPlayers = $infos["maxPlayers"];
		$this->playersCount = $infos["playersCount"];
		$this->ip = $infos["ip"];
		$this->port = $infos["port"];
		$this->players = $infos["players"];
	}

	public static function new(array $infos, string $hostname, int $port): PingInfos
	{
		$versionAndServerType = static::parseVersionAndServerType($infos);

		$data = new PingInfos([
			"version" => $versionAndServerType["version"],
			"serverType" => $versionAndServerType["serverType"],
			"motd" => static::parseMotd($infos),
			"maxPlayers" => static::parseMaxPlayers($infos),
			"playersCount" => static::parsePlayersCount($infos),
			"ip" => $hostname,
			"port" => $port,
			"players" => static::parsePlayers($infos),
		]);

		return $data;
	}

	protected static function parseVersionAndServerType(array $infos): array
	{
		$fullName = $infos["version"]["name"];

		$exploded = explode(" ", $fullName);

		$version = array_pop($exploded);
		$serverType = join(" ", $exploded);

		return [
			"version" => $version,
			"serverType" => $serverType ?? "Vanilla"
		];
	}

	protected static function parseMotd(array $infos): string
	{
		return $infos["description"]["text"] ?? $infos["description"]["extra"][0]["text"];
	}

	protected static function parseMaxPlayers(array $infos): int
	{
		return $infos["players"]["max"];
	}

	protected static function parsePlayersCount(array $infos): int
	{
		return $infos["players"]["online"];
	}

	protected static function parsePlayers(array $infos): array
	{
		if (!isset($infos["players"]["sample"])) return [];

		return array_map((fn ($player) => $player["name"]), $infos["players"]["sample"]);
	}

	public function getMotd(): string
	{
		return $this->motd;
	}

	public function getVersion(): string
	{
		return $this->version;
	}

	public function getPlayersCount(): int
	{
		return $this->playersCount;
	}

	public function getNumPlayers(): int
	{
		return $this->getPlayersCount();
	}

	public function getMaxPlayers(): int
	{
		return $this->maxPlayers;
	}

	public function getPort(): int
	{
		return $this->port;
	}

	public function getIp(): string
	{
		return $this->ip;
	}

	public function getServerType(): string
	{
		return $this->serverType;
	}

	public function isVanilla(): bool
	{
		return $this->getServerType() === "Vanilla";
	}

	public function isBukkit(): bool
	{
		return str_contains("bukkit", strtolower($this->getServerType()));
	}

	public function isCraftBukkit(): bool
	{
		return $this->isBukkit();
	}

	public function isSpigot(): bool
	{
		return str_contains("spigot", strtolower($this->getServerType()));
	}

	public function isPaper(): bool
	{
		return str_contains("paper", strtolower($this->getServerType()));
	}

	public function getPlayers(): array
	{
		return $this->players;
	}
}
