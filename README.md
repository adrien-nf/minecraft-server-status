# minecraft-server-status
Get informations from a Minecraft server in real time.
## Installation
### Via composer
`composer require adrien-nf/minecraft-server-status`
## Usage
### Server List Ping
No configuration needed from the server, this is the easiest method.
```php
use Ping\Ping;

$infos = (new Ping("localhost", 25565))->getInfos();

echo $infos->getVersion(); // 1.20.1
echo $infos->getServerType(); // Vanilla
echo $infos->getMotd(); // A Minecraft Server
echo $infos->getMaxPlayers(); // 20
echo $infos->getPlayersCount(); // 3
echo $infos->getIp(); // localhost
echo $infos->getPort(); // 25565

// With Ping, you only get a partial player list
var_dump($infos->getPlayers()); // ["Notch", "Deadmau5"]

// Some helper functions
echo $infos->isPlayerConnected("Notch"); // true
echo $infos->isVanilla(); // true
echo $infos->isBukkit(); // false
echo $infos->isSpigot(); // false
echo $infos->isPaper(); // false
```
### Query
Requires configuration in `server.properties`:
- `enable-query=true`
- `query.port=25565`

Once this is done, usage is pretty straightforward.
```php
use Queries\JavaServerQuery;

$infos = (new JavaServerQuery("localhost", 25565))->getInfos();

echo $infos->getGameType(); // SMP
echo $infos->getWorldName(); // world

// With Query, you get a full players list
var_dump($infos->getPlayers()); // ["Notch", "Deadmau5", "Jeb"]

var_dump($infos->getPlugins()); // [["name" => "Shopkeepers", "version" => "2.17.1"], ["name" => "HolographicDisplays", "version" => "3.0.2"]]

// Some helper functions
echo $infos->getPluginsAsString(); // "Shopkeepers, HolographicDisplays"
echo $infos->getPluginsAsString(true); // "Shopkeepers 2.17.1, HolographicDisplays 3.0.2"
echo $infos->hasPlugin("Shopkeepers"); // true
var_dump($infos->getPluginsAsArray()) // ["Shopkeepers", "HolographicDisplays"]
var_dump($infos->getPluginsAsArray(true)) // ["Shopkeepers 2.17.1", "HolographicDisplays 3.0.2"]
```
## Credits
This package has been inspired by [xPaw's](https://github.com/xPaw/PHP-Minecraft-Query) package, extended and corrected for simpler usage.
