<?php

namespace Services;

use Data\QueryInfos;

class StatusParser
{
	protected QueryInfos $queryInfos;

	public function __construct($status)
	{
		$cleaned = $this->cleanStatus($status);

		[$infos, $players] = explode("player_", $cleaned);

		$infos = $this->handleInfos($infos);
		$players = $this->handlePlayers($players);

		$this->queryInfos = new QueryInfos($infos, $players);
	}

	public function getQueryInfos(): QueryInfos
	{
		return $this->queryInfos;
	}

	protected function cleanStatus($status): string
	{
		return trim(substr($status, strlen("splitnumXX")));
	}

	protected function handleInfos($infos): array
	{
		$infos = explode("\00", $infos);

		$filteredInfos = [];

		foreach ($infos as $k => $field) {
			if (array_key_exists($field, QueryInfos::FIELDS)) {
				$filteredInfos[QueryInfos::FIELDS[$field]] = $infos[$k + 1] ?? null;
			}
		}

		return $this->parseInfos($filteredInfos);
	}

	protected function parseInfos($infos): array
	{
		foreach (["playersCount", "maxPlayers", "port"] as $field) {
			$infos[$field] = intval($infos[$field]);
		}

		$infos = $this->infosWithParsedPlugins($infos);

		return $infos;
	}

	protected function infosWithParsedPlugins($infos): array
	{
		$plugins = $infos["plugins"];

		if (empty($plugins)) {
			$infos["serverType"] = "Vanilla";
			$infos["plugins"] = [];

			return $infos;
		}

		[$serverType, $plugins] = explode(": ", $plugins, 2);

		$infos["serverType"] = $serverType;
		$infos["plugins"] = array_map(function ($line) {
			[$name, $version] = explode(" ", trim($line));

			return ["name" => $name, "version" => $version];
		}, explode(";", $plugins));

		return $infos;
	}

	protected function handlePlayers($players): array
	{
		$players = trim($players);

		if (empty($players)) return [];

		return array_map(fn ($player) => trim($player), explode("\x00", $players));
	}
}
