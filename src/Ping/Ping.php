<?php

namespace Ping;

class Ping extends ServerPing
{
	public function __construct(string $hostname = "127.0.0.1", int $port = 25565)
	{
		parent::__construct($hostname, $port);
	}
}
