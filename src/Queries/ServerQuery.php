<?php

namespace Queries;

use Exception;
use Data\QueryInfos;

abstract class ServerQuery
{
	const STATISTICS = 0x00;
	const HANDSHAKE = 0x09;

	protected $socket;
	protected QueryInfos $infos;

	protected $data;

	public function __construct(string $hostname, int $port)
	{
		$this->connectAndGenerateInfos($hostname, $port);
	}

	protected abstract function connectAndGenerateInfos(string $hostname, int $port): void;

	public function getInfos(): QueryInfos
	{
		return $this->infos;
	}

	protected function send(string $command, string $append = ""): string | false
	{
		$command = pack('c*', 0xFE, 0xFD, $command, 0x01, 0x02, 0x03, 0x04) . $append;
		$length  = strlen($command);

		if ($length !== fwrite($this->socket, $command, $length)) throw new Exception("Couldn't write to server.");

		$data = fread($this->socket, 4096);

		if ($data === false) throw new Exception("Couldn't receive data from server.");

		if (strlen($data) < 5 || $data[0] != $command[2]) return false;

		return substr($data, 5);
	}
}
