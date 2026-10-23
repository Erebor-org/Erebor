<?php

namespace App\Tests\Service;

use App\Service\Gw2\Gw2SellAdvisor;
use PHPUnit\Framework\TestCase;

class Gw2SellAdvisorTest extends TestCase
{
    private const ECTO_NET = 1360; // one ecto sold instantly, after fees

    private function item(array $overrides = []): array
    {
        return $overrides + [
            'count' => 1, 'rarity' => 'Fine', 'level' => 30, 'itemType' => 'Weapon',
            'flags' => [], 'binding' => null, 'vendorValue' => 40,
        ];
    }

    private static function price(int $buy, int $sell = 0): array
    {
        return ['buys' => ['unit_price' => $buy], 'sells' => ['unit_price' => $sell ?: $buy]];
    }

    private function advise(array $item, ?array $price = null, bool $material = false): array
    {
        return (new Gw2SellAdvisor())->advise($item, $price, self::ECTO_NET, $material);
    }

    public function testNetOfFeesTakesListingAndExchangeFees(): void
    {
        $this->assertSame(850, Gw2SellAdvisor::netOfFees(1000)); // -50 listing, -100 exchange
        $this->assertSame(8, Gw2SellAdvisor::netOfFees(10)); // each fee at least 1 copper
        $this->assertSame(0, Gw2SellAdvisor::netOfFees(1));
        $this->assertSame(0, Gw2SellAdvisor::netOfFees(0));
    }

    public function testJunkGoesToTheMerchant(): void
    {
        $advice = $this->advise($this->item(['rarity' => 'Junk', 'itemType' => 'Trophy', 'vendorValue' => 9, 'count' => 20]));

        $this->assertSame(Gw2SellAdvisor::VENDOR, $advice['action']);
        $this->assertSame(180, $advice['total']);
    }

    public function testAscendedIsKept(): void
    {
        $advice = $this->advise($this->item(['rarity' => 'Ascended', 'level' => 80]), self::price(50000));

        $this->assertSame(Gw2SellAdvisor::KEEP, $advice['action']);
    }

    public function testRareIsSalvagedWhenEctosAreWorthMore(): void
    {
        // 0.875 ecto * 1360 = 1190 > 1234 - fees = 1049
        $advice = $this->advise($this->item(['rarity' => 'Rare', 'level' => 80]), self::price(1234));

        $this->assertSame(Gw2SellAdvisor::SALVAGE, $advice['action']);
        $this->assertSame(1190, $advice['unit']['salvage']);
        $this->assertSame(1049, $advice['unit']['sell']);
        $this->assertSame(1234, $advice['unit']['sellGross']);
        $this->assertSame(1190, $advice['total']);
    }

    public function testRareIsSoldWhenTheBuyOrderBeatsTheEctos(): void
    {
        $advice = $this->advise($this->item(['rarity' => 'Rare', 'level' => 80, 'count' => 2]), self::price(5000));

        $this->assertSame(Gw2SellAdvisor::SELL, $advice['action']);
        $this->assertSame(2 * 4250, $advice['total']);
    }

    public function testRareBelowLevel68HasNoEctoEstimate(): void
    {
        $advice = $this->advise($this->item(['rarity' => 'Rare', 'level' => 67]), self::price(896));

        $this->assertNull($advice['unit']['salvage']);
        $this->assertSame(Gw2SellAdvisor::SELL, $advice['action']);
    }

    public function testBoundExoticIsSalvagedForEctos(): void
    {
        $advice = $this->advise($this->item(['rarity' => 'Exotic', 'level' => 80, 'flags' => ['AccountBound'], 'vendorValue' => 238]));

        $this->assertSame(Gw2SellAdvisor::SALVAGE, $advice['action']);
        $this->assertSame(1632, $advice['unit']['salvage']); // 1.2 * 1360
        $this->assertNull($advice['unit']['sell']);
    }

    public function testSlotBindingMakesATradableItemUnsellable(): void
    {
        // The item has trading post prices, but this copy is soulbound
        $advice = $this->advise($this->item(['rarity' => 'Masterwork', 'binding' => 'Character']), self::price(66, 642));

        $this->assertNull($advice['unit']['sell']);
        $this->assertFalse($advice['tradable']);
        $this->assertSame(Gw2SellAdvisor::SALVAGE, $advice['action']);
    }

    public function testBoundNonSalvageableGearGoesToTheMerchantOrIsKept(): void
    {
        $vendor = $this->advise($this->item(['flags' => ['AccountBound', 'NoSalvage']]));
        $keep = $this->advise($this->item(['flags' => ['AccountBound', 'NoSalvage', 'NoSell']]));

        $this->assertSame(Gw2SellAdvisor::VENDOR, $vendor['action']);
        $this->assertSame(Gw2SellAdvisor::KEEP, $keep['action']);
    }

    public function testCheapLowLevelGearIsSalvaged(): void
    {
        $advice = $this->advise($this->item(['vendorValue' => 42]), self::price(61));

        $this->assertSame(Gw2SellAdvisor::SALVAGE, $advice['action']);
        $this->assertNull($advice['total']); // no reliable value for basic materials
    }

    public function testLowLevelGearWorthSomethingIsSoldOrVendored(): void
    {
        $sold = $this->advise($this->item(['vendorValue' => 14]), self::price(1027));
        $vendored = $this->advise($this->item(['rarity' => 'Masterwork', 'vendorValue' => 157]), self::price(0, 196));

        $this->assertSame(Gw2SellAdvisor::SELL, $sold['action']);
        $this->assertSame(Gw2SellAdvisor::VENDOR, $vendored['action']);
        $this->assertStringContainsString('Aucun ordre d\'achat', $vendored['reason']);
    }

    public function testMaterialsAreKeptForTheStorage(): void
    {
        $advice = $this->advise($this->item(['rarity' => 'Exotic', 'itemType' => 'Trophy', 'vendorValue' => 256]), self::price(1603), true);

        $this->assertSame(Gw2SellAdvisor::KEEP, $advice['action']);
        $this->assertStringContainsString('stockage', $advice['reason']);
        $this->assertSame(1363, $advice['unit']['sell']); // the price is still shown
    }

    public function testContainersAreKeptToBeOpened(): void
    {
        $advice = $this->advise($this->item(['itemType' => 'Container', 'rarity' => 'Rare']), self::price(1387));

        $this->assertSame(Gw2SellAdvisor::KEEP, $advice['action']);
        $this->assertStringContainsString('ouvrir', $advice['reason']);
    }

    public function testTradableConsumableIsSold(): void
    {
        $advice = $this->advise($this->item(['itemType' => 'Consumable', 'flags' => ['NoSell'], 'vendorValue' => 14]), self::price(159));

        $this->assertSame(Gw2SellAdvisor::SELL, $advice['action']);
        $this->assertSame(135, $advice['total']);
    }

    public function testUpgradeWithoutBuyOrderGoesToTheMerchant(): void
    {
        $advice = $this->advise($this->item(['itemType' => 'UpgradeComponent', 'flags' => ['NoSalvage'], 'vendorValue' => 34]), self::price(0, 40));

        $this->assertSame(Gw2SellAdvisor::VENDOR, $advice['action']);
        $this->assertTrue($advice['tradable']);
        $this->assertStringStartsWith('Aucun ordre d\'achat', $advice['reason']);
    }

    public function testBoundConsumableIsKept(): void
    {
        $advice = $this->advise($this->item(['itemType' => 'Consumable', 'flags' => ['AccountBound', 'NoSell', 'NoSalvage']]));

        $this->assertSame(Gw2SellAdvisor::KEEP, $advice['action']);
        $this->assertNull($advice['total']);
    }
}
