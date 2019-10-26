<?php

namespace Wertzui123\SignIt\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use Wertzui123\SignIt\Main;

class signit extends Command
{

    private $plugin;

    public function __construct(Main $plugin, $data)
    {
        parent::__construct($data['command'], $data['description'], null, $data['aliases']);
        $this->plugin = $plugin;
        $this->setPermission('signit.command.sign');
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if(!$sender instanceof Player){
            $sender->sendMessage($this->plugin->getMessage('run_ingame'));
            return;
        }

        if(!$sender->hasPermission($this->getPermission())){
            $sender->sendMessage($this->plugin->getMessage('no_permission'));
            return;
        }

        if($this->plugin->getUntil($sender) > $this->plugin->getNow()){
            $sender->sendMessage($this->plugin->getUntilMessage($this->plugin->getUntil($sender)));
            return;
        }

        if($sender->getInventory()->getItemInHand()->getId() === 0){
            $sender->sendMessage($this->plugin->getMessage('cant_sign_air'));
            return;
        }

        if(!isset($args[0])){
            $sender->sendMessage($this->plugin->getMessage('no_text_given'));
            return;
        }

        $item = $sender->getInventory()->getItemInHand();
        $item->setLore([$this->plugin->getLore($sender, implode(' ', $args))]);
        $sender->getInventory()->setItemInHand($item);
        $sender->sendMessage(str_replace('{text}' , implode(' ', $args), $this->plugin->getMessage('succes')));
        if(!$sender->hasPermission('signit.waiting.bypass')) $this->plugin->setUntil($sender, $this->plugin->getNow() + ($this->plugin->getConfig()->get('wait_time') * 60));
    }

}