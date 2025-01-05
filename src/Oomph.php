<?php

namespace ethaniccc\Oomph;

use cooldogedev\Spectrum\Spectrum;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerToggleFlightEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\PlayerAuthInputPacket;
use pocketmine\network\mcpe\protocol\ProtocolInfo;
use pocketmine\network\mcpe\protocol\types\PlayerAuthInputFlags;
use pocketmine\plugin\PluginBase;

class Oomph extends PluginBase implements Listener {

	private const OOMPH_PACKET_DECODE = [
		ProtocolInfo::ADD_ACTOR_PACKET,
		ProtocolInfo::ADD_PLAYER_PACKET,
		ProtocolInfo::CHUNK_RADIUS_UPDATED_PACKET,
		ProtocolInfo::LEVEL_CHUNK_PACKET ,
		ProtocolInfo::MOB_EFFECT_PACKET ,
		ProtocolInfo::MOVE_ACTOR_ABSOLUTE_PACKET ,
		ProtocolInfo::MOVE_PLAYER_PACKET ,
		ProtocolInfo::REMOVE_ACTOR_PACKET ,
		ProtocolInfo::SET_ACTOR_DATA_PACKET ,
		ProtocolInfo::SET_ACTOR_MOTION_PACKET ,
		ProtocolInfo::SET_PLAYER_GAME_TYPE_PACKET,
		ProtocolInfo::SUB_CHUNK_PACKET ,
		ProtocolInfo::UPDATE_ABILITIES_PACKET ,
		ProtocolInfo::UPDATE_ATTRIBUTES_PACKET ,
		ProtocolInfo::UPDATE_BLOCK_PACKET ,
		ProtocolInfo::UPDATE_PLAYER_GAME_TYPE_PACKET,
	];

	private static Oomph $instance;

	public function onEnable(): void {
		$spectrum = $this->getServer()->getPluginManager()->getPlugin("Spectrum");
		if (!$spectrum instanceof Spectrum) {
			throw new \RuntimeException("Oomph-PM requires Spectrum to run.");
		}
		foreach (self::OOMPH_PACKET_DECODE as $pkID) {
			$spectrum->decode[$pkID] = true;
		}

		self::$instance = $this;
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public static function getInstance(): Oomph {
		return self::$instance;
	}

	/**
	 * @param PlayerToggleFlightEvent $event
	 * @priority HIGHEST
	 * @ignoreCancelled TRUE
	 * We do this because for some reason PM doesn't handle it themselves... lmao!
	 */
	public function onToggleFlight(PlayerToggleFlightEvent $event): void {
		if ($event->isFlying() && !$event->getPlayer()->getAllowFlight()) {
			$event->cancel();
		}
	}

	/**
	 * @priority HIGHEST
	 * @param DataPacketReceiveEvent $event
	 * @return void
	 */
	public function onClientPacket(DataPacketReceiveEvent $event): void {
		$player = $event->getOrigin()->getPlayer();
		$packet = $event->getPacket();

		// The fact we even have to do this is stupid LMAO.
		// Remember to notify dylanthecat!!! (i never did, i never will)
		if ($packet instanceof PlayerAuthInputPacket && $packet->getInputFlags()->get(PlayerAuthInputFlags::START_FLYING) && !$player->getAllowFlight()) {
			$player?->getNetworkSession()->syncAbilities($player);
		}
	}

}
