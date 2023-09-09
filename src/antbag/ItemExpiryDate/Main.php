<?php

namespace antbag\ItemExpiryDate;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerItemHeldEvent;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\nbt\tag\IntTag;
use pocketmine\player\Player;
use pocketmine\plugin\Plugin;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;
use antbag\ItemExpiryDate\command\ExpirationDateItemCommand;

final class Main extends PluginBase implements Listener{
    use SingletonTrait;

    public const TAG_EXPIRATION = "expirationDate";

    protected function onLoad(): void{
        self::setInstance($this);
    }

    protected function onEnable(): void{
        $this->getServer()->getCommandMap()->register(strtolower($this->getName()), new ExpirationDateItemCommand());
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    public function isOutExpirationDateItem(Item $item): bool{
        if (!$item->getNamedTag()->getTag(self::TAG_EXPIRATION) instanceof IntTag) {
            return false;
        }

        return time() >= $item->getNamedTag()->getInt(self::TAG_EXPIRATION);
    }

    /**
     * @param PlayerItemHeldEvent $event
     *
     * @priority HIGHEST
     */
    public function onPlayerItemHeld(PlayerItemHeldEvent $event): void{
        if ($this->isOutExpirationDateItem($event->getItem())) {
            $player = $event->getPlayer();
            $player->getInventory()->setItemInHand(VanillaItems::AIR());
            $player->sendMessage("expiration.out.date.item"));
        }
    }

    /**
     * @param PlayerInteractEvent $event
     *
     * @priority HIGHEST
     */
    public function onPlayerInteract(PlayerInteractEvent $event): void{
        if ($this->isOutExpirationDateItem($event->getItem())) {
            $player = $event->getPlayer();
            $player->getInventory()->setItemInHand(VanillaItems::AIR());
            $player->sendMessage("expiration.out.date.item");
        }
    }

    /**
     * @param EntityDamageByEntityEvent $event
     *
     * @priority HIGHEST
     */
    public function onEntityDamage(EntityDamageByEntityEvent $event): void{
        /** @var Player $player */
        if (!($player = $event->getDamager()) instanceof Player)
            return;

        if (!$this->isOutExpirationDateItem($player->getInventory()->getItemInHand()))
            return;
            
        $player->getInventory()->setItemInHand(ItemFactory::air());
        $player->sendMessage("expiration.out.date.item");
    }
}
