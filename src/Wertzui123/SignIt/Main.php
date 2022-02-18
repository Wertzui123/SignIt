<?php

namespace Wertzui123\SignIt;

use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use Wertzui123\SignIt\commands\sign;

class Main extends PluginBase
{

    /** @var float */
    const CONFIG_VERSION = 3.1;

    public $playerDataFile;
    private $messagesFile;

    public function onEnable(): void
    {
        $this->configUpdater();
        $this->playerDataFile = new Config($this->getDataFolder() . 'players.json', Config::JSON);
        $this->messagesFile = new Config($this->getDataFolder() . 'messages.yml', Config::YAML);
        $this->getServer()->getCommandMap()->register('SignIt', new sign($this));
    }

    /**
     * Returns a message from the messages file
     * @param string $key
     * @param array $replace [optional]
     * @return string
     */
    public function getMessage($key, $replace = [])
    {
        return str_replace(array_keys($replace), $replace, $this->messagesFile->getNested($key));
    }

    /**
     * @api
     * Returns until when a player still has to wait before they can sign an item again
     * @param Player $player
     * @return int
     */
    public function getEndOfCooldown(Player $player)
    {
        return $this->playerDataFile->get(strtolower($player->getName()), 0) + $this->getConfig()->getNested('cooldown.' . $this->getPermissionGroup($player));
    }

    /**
     * Returns the PurePerms group of a player
     * @param Player $player
     * @return string
     */
    public function getGroup(Player $player)
    {
        return ($pp = $this->getServer()->getPluginManager()->getPlugin('PurePerms')) ? $pp->getUserDataMgr()->getData($player)['group'] : '/';
    }

    /**
     * Calculates the lore of an item that is being signed
     * @param Player $player
     * @param string $text
     * @return string
     */
    public function getLore(Player $player, $text)
    {
        $lore = $this->getConfig()->get('sign_format');
        $lore = str_replace('{player}', $player->getName(), $lore);
        $lore = str_replace('{text}', $text, $lore);
        $lore = str_replace('{date}', (new \DateTime())->format($this->getConfig()->get('time_format')), $lore);
        $lore = str_replace(['{group}', '{rank}'], $this->getGroup($player), $lore);
        return $lore;
    }

    /**
     * @api
     * Returns the permission group for a player
     * @param Player $player
     * @return string
     */
    public function getPermissionGroup(Player $player)
    {
        foreach ($this->getConfig()->get('permission_groups') as $group) {
            if ($player->hasPermission('signit.permissions.' . $group)) {
                return $group;
            }
        }
        return 'default';
    }

    /**
     * Converts seconds to hours, minutes and seconds
     * @param int $seconds
     * @param string $message
     * @return string
     */
    public function convertSeconds($seconds, $message)
    {
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds / 60) % 60);
        $seconds = $seconds % 60;
        return str_replace(['{hours}', '{minutes}', '{seconds}'], [$hours, $minutes, $seconds], $message);
    }

    /**
     * Checks whether the config version is the latest and updates it if it isn't
     */
    private function configUpdater()
    {
        $this->saveResource('config.yml');
        $this->saveResource('messages.yml');
        if ($this->getConfig()->get('config-version') !== self::CONFIG_VERSION) {
            $configVersion = $this->getConfig()->get('config-version');
            if (!$this->getConfig()->exists('config-version') && $this->getConfig()->exists('version')) { // backwards compatibility
                $configVersion = $this->getConfig()->get('version');
            }
            $this->getLogger()->info("§eYour config isn't the latest. SignIt renamed your old config to §bconfig-" . $configVersion . ".yml §6and created a new config. Have fun!");
            rename($this->getDataFolder() . 'config.yml', $this->getDataFolder() . 'config-' . $configVersion . '.yml');
            rename($this->getDataFolder() . 'messages.yml', $this->getDataFolder() . 'messages-' . $configVersion . '.yml');
            $this->saveResource('config.yml');
            $this->reloadConfig();
            $this->saveResource('messages.yml');
        }
    }

    public function onDisable(): void
    {
        $this->playerDataFile->save();
    }

}