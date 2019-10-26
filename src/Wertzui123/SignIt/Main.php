<?php

namespace Wertzui123\SignIt;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Wertzui123\SignIt\commands\signit;

class Main extends PluginBase
{

    private $players;
    private $messages;

    public function onLoad()
    {
        $this->ConfigUpdater(2.0);
    }

    public function onEnable(): void
    {
        $this->players = new Config($this->getDataFolder().'players.yml', Config::YAML);
        $this->messages = new Config($this->getDataFolder().'messages.yml', Config::YAML);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getServer()->getCommandMap()->register('SignIt', new signit($this, ['command' => $this->getConfig()->getAll()['command'], 'description' => $this->getConfig()->getAll()['description'], 'aliases' => $this->getConfig()->getAll()['aliases']]));
    }

    public function getNow(){
        return time();
    }

    public function getUntil(Player $player){
        return $this->getPlayersFile()->get(strtolower($player->getName()));
    }

    public function setUntil(Player $player, $time = null){
        $this->getPlayersFile()->set(strtolower($player->getName()), $time ?? $this->getNow());
    }

    public function getUntilMessage($time){
        $time = $time - $this->getNow();
        $hours = floor($time / 3600);
        $minutes = floor(($time / 60) % 60);
        $seconds = $time % 60;
        return str_replace(['{hours}', '{minutes}', '{seconds}'], [$hours, $minutes, $seconds],$this->getMessage('wait'));
    }

    public function getGroup(Player $player){
        return ($pp = $this->getServer()->getPluginManager()->getPlugin('PurePerms')) ? $pp->getUserDataMgr()->getData($player)['group'] : 'PurePerms isn\'t installed';
    }

    public function getPlayersFile() : Config{
        return $this->players;
    }

    public function getMessages() : Config
    {
	    return $this->messages;
    }

    public function getMessage($string){
        return $this->getMessages()->get($string);
    }

    public function getLore(Player $player, $text){
        $lore = $this->getConfig()->get('sign_format');
        $lore = str_replace(['{group}', '{rank}'], $this->getGroup($player), $lore);
        $lore = str_replace('{text}', $text, $lore);
        $lore = str_replace('{player}', $player->getName(), $lore);
        $lore = str_replace('{date}', (new \DateTime())->format($this->getConfig()->get('lore_timeformat')), $lore);
        return $lore;
    }

    public function ConfigUpdater($version)
    {
        $cfgpath = $this->getDataFolder() . "config.yml";
        $msgpath = $this->getDataFolder() . "messages.yml";
        if (file_exists($cfgpath)) {
            $cfgversion = $this->getConfig()->get("version");
            if ($cfgversion !== $version) {
                $this->getLogger()->info("Your config has been renamed to config-" . $cfgversion . ".yml and your messages file has been renamed to messages-" . $cfgversion . ".yml. That's because your config version wasn't the latest avable. So we created a new config and a new messages file for you!");
                rename($cfgpath, $this->getDataFolder() . "config-" . $cfgversion . ".yml");
                rename($msgpath, $this->getDataFolder() . "messages-" . $cfgversion . ".yml");
                $this->saveResource("config.yml");
                $this->saveResource("messages.yml");
            }
        } else {
            $this->saveResource("config.yml");
            $this->saveResource("messages.yml");
        }
    }

    public function onDisable()
    {
        $this->getConfig()->save();
        $this->getPlayersFile()->save();
        unset($this->players);
        unset($this->messages);
    }
}