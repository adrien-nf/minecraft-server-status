<?php

namespace Queries;

use Exception;
use Queries\ServerQuery;
use Services\StatusParser;

class JavaServerQuery extends ServerQuery
{
	public function __construct(string $hostname = "127.0.0.1", int $port = 25565)
	{
		parent::__construct($hostname, $port);
	}

	protected function connectAndGenerateInfos($hostname, $port): void
	{
		$this->socket = fsockopen("udp://" . $hostname, $port);

		stream_set_timeout($this->socket, 3);
		stream_set_blocking($this->socket, true);

		try {
			$this->generateInfos();
		} finally {
			fclose($this->socket);
		}
	}

	protected function generateInfos(): void
	{
		$challengeToken = $this->getChallengeToken();

		$status = $this->send(self::STATISTICS, $challengeToken . pack('c*', ...[self::STATISTICS, self::STATISTICS, self::STATISTICS, self::STATISTICS]));

		if (!$status) throw new Exception("Couldn't get status from server.");

		$this->infos = (new StatusParser($status))->getQueryInfos();
	}

	protected function getChallengeToken()
	{
		$token = $this->send(self::HANDSHAKE);

		if ($token === false) throw new Exception("Couldn't receive challenge token from server.");

		return pack('N', $token);
	}
}
