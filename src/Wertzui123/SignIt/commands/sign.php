<?php

namespace Wertzui123\SignIt\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginOwned;
use Wertzui123\SignIt\Main;

class sign extends Command implements PluginOwned
{

    private $plugin;

    public function __construct(Main $plugin)
    {
        parent::__construct($plugin->getConfig()->getNested('command.sign.command'), $plugin->getConfig()->getNested('command.sign.description'), $plugin->getConfig()->getNested('command.sign.usage'), $plugin->getConfig()->getNested('command.sign.aliases'));
        $this->setPermission('signit.command.sign');
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$sender instanceof Player) {
            $sender->sendMessage($this->plugin->getMessage('command.sign.runIngame'));
            return;
        }
        if (!$sender->hasPermission($this->getPermission())) {
            $sender->sendMessage($this->plugin->getMessage('command.sign.noPermission'));
            return;
        }
        if (time() < $this->plugin->getEndOfCooldown($sender) && !$sender->hasPermission('signit.cooldown.bypass')) {
            $sender->sendMessage($this->plugin->convertSeconds(($this->plugin->playerDataFile->get(strtolower($sender->getName()), 0) + $this->plugin->getConfig()->getNested('cooldown.' . $this->plugin->getPermissionGroup($sender))) - time(), $this->plugin->getMessage('command.sign.cooldown')));
            return;
        }
        if ($sender->getInventory()->getItemInHand()->isNull()) {
            $sender->sendMessage($this->plugin->getMessage('command.sign.invalidItem'));
            return;
        }
        if (!isset($args[0])) {
            $sender->sendMessage($this->plugin->getMessage('command.sign.passText'));
            return;
        }
        $item = $sender->getInventory()->getItemInHand();
        $signs = ($item->getNamedTag()->getListTag('Signs') ?? new ListTag())->getValue();
        $newSign = (new CompoundTag())->setString('Author', $sender->getName())->setString('Text', implode(' ', $args))->setString('Timestamp', time())->setString('FullText', $this->plugin->getLore($sender, implode(' ', $args)));
        if (count($signs) > 0 && $this->plugin->getConfig()->get('overrideOldSign') > 0) {
            $item->setLore(array_filter($item->getLore(), function ($line) use ($signs) {
                foreach ($signs as $sign) {
                    if ($sign->getString('FullText') === $line) {
                        return false;
                    }
                }
                return true;
            }));
            $signs = [$newSign];
        } else {
            $signs[] = $newSign;
        }
        $item->getNamedTag()->setTag('Signs', new ListTag($signs));
        $lore = $item->getLore();
        $lore[] = $this->plugin->getLore($sender, implode(' ', $args));
        $item->setLore($lore);
        $sender->getInventory()->setItemInHand($item);
        $sender->sendMessage($this->plugin->getMessage('command.sign.success', ['{text}' => implode(' ', $args)]));
        if (!$sender->hasPermission('signit.cooldown.bypass')) {
            $this->plugin->playerDataFile->set(strtolower($sender->getName()), time());
        }
    }

    public function getOwningPlugin(): Plugin
    {
        return $this->plugin;
    }

}