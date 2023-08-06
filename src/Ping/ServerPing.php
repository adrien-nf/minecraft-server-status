<?php

namespace Ping;

use Data\PingInfos;
use Exception;

class ServerPing
{
	protected $socket;
	protected string $hostname;
	protected int $port;
	protected int $timeout = 3;

	protected PingInfos $infos;

	public function __construct(string $hostname, int $port)
	{
		$this->hostname = $hostname;
		$this->port = $port;

		$this->connectAndGenerateInfos();
	}

	public function getInfos(): PingInfos
	{
		return $this->infos;
	}

	protected function connectAndGenerateInfos(): void
	{
		$this->socket = fsockopen($this->hostname, $this->port);

		stream_set_timeout($this->socket, $this->timeout);
		stream_set_blocking($this->socket, true);

		try {
			$data = $this->generateInfos();

			$this->infos = PingInfos::new($data, $this->hostname, $this->port);
		} finally {
			fclose($this->socket);
		}
	}

	protected function generateInfos()
	{
		$this->handshake();
		$this->ping();

		if ($this->readInt() < 10) return false;

		$packetType = $this->readInt();

		$data = $this->getDataFromServer();

		if (empty($data)) throw new Exception("Couldn't get data from server.");

		return json_decode($data, true);
	}

	protected function handshake(): void
	{
		$message = join("", [
			"\x00",
			"\x04",
			pack('c', strlen($this->hostname)) . $this->hostname,
			pack('n', $this->port),
			"\x01"
		]);

		$messageWithLength = pack('c', strlen($message)) . $message;

		fwrite($this->socket, $messageWithLength);
	}

	protected function ping(): void
	{
		fwrite($this->socket, "\x01\x00");
	}

	protected function getDataFromServer(): string
	{
		$timeStart = microtime(true);
		$stringLength = $this->readInt();

		$buffer = "";

		do {
			$timeDelta = microtime(true) - $timeStart;

			if ($timeDelta > $this->timeout) throw new Exception("Timed out from server.");

			$data = fread($this->socket, $stringLength - strlen($buffer));

			if (!$data) throw new Exception("Not enough data from server.");

			$buffer .= $data;
		} while (strlen($buffer) < $stringLength);

		return $buffer;
	}

	private function readInt()
	{
		$data = 0;
		$length = 0;

		while (true) {
			$k = @fgetc($this->socket);

			if ($k === FALSE) return 0;

			$ord = ord($k);

			$data |= ($ord & 0x7F) << $length++ * 7;

			if ($length > 5) throw new Exception("Data read is too big.");

			if (($ord & 0x80) != 128) break;
		}

		return $data;
	}
}
