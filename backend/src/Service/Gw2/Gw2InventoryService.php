<?php

namespace App\Service\Gw2;

/**
 * Everything the account holds in bags (every character + shared slots) and in the bank,
 * each stack with its trading post price and a sell / vendor / salvage / keep advice.
 */
class Gw2InventoryService
{
    public const LOCATION_INVENTORY = 'inventory';
    public const LOCATION_SHARED = 'shared';
    public const LOCATION_BANK = 'bank';

    public function __construct(
        private Gw2ApiClient $gw2,
        private Gw2ItemFormatter $formatter,
        private Gw2SellAdvisor $advisor,
    ) {
    }

    /**
     * @param bool $refresh skip the 5 minute cache: bags, bank and prices are read from the API again
     */
    public function build(bool $refresh = false): array
    {
        if ($refresh) {
            // Character list included: a character created meanwhile shows up
            $this->gw2->forgetInventories($this->gw2->getCharacterNames());
        }

        $names = $this->gw2->getCharacterNames();
        $inventories = $this->gw2->getCharacterInventories($names);
        $shared = $this->gw2->getSharedInventory();
        $bank = $this->gw2->getBank();

        // Every non-empty slot with where it is
        $raw = [];
        $places = [];
        $addSlots = function (array $slots, string $location, ?string $character) use (&$raw, &$places) {
            foreach ($slots as $slot) {
                if (null !== $slot) {
                    $raw[] = $slot;
                    $places[] = ['location' => $location, 'character' => $character];
                }
            }
        };

        $bagCapacity = [];
        foreach ($inventories as $name => $inventory) {
            $bagCapacity[$name] = ['slots' => 0, 'used' => 0];
            foreach ($inventory['bags'] ?? [] as $bag) {
                if (null === $bag) {
                    continue;
                }
                $bagCapacity[$name]['slots'] += $bag['size'] ?? 0;
                $bagCapacity[$name]['used'] += count(array_filter($bag['inventory'] ?? []));
                $addSlots($bag['inventory'] ?? [], self::LOCATION_INVENTORY, $name);
            }
        }
        $addSlots($shared, self::LOCATION_SHARED, null);
        $addSlots($bank, self::LOCATION_BANK, null);

        $items = $this->formatter->formatSlots($raw);

        $ids = array_unique(array_merge(array_column($raw, 'id'), [Gw2SellAdvisor::ECTO_ID]));
        if ($refresh) {
            $this->gw2->forgetPrices($ids);
        }
        $prices = $this->gw2->getPrices($ids);
        $ectoBuy = (int) ($prices[Gw2SellAdvisor::ECTO_ID]['buys']['unit_price'] ?? 0);
        $ectoNet = Gw2SellAdvisor::netOfFees($ectoBuy) ?: null;
        $storable = array_flip($this->gw2->getStorableMaterialIds());

        $slots = [];
        foreach ($items as $i => $item) {
            $price = $prices[$item['id']] ?? null;
            $slots[] = $places[$i] + $item + [
                'price' => $price ? [
                    'buy' => $price['buys']['unit_price'] ?? 0,
                    'sell' => $price['sells']['unit_price'] ?? 0,
                ] : null,
                'advice' => $this->advisor->advise($item, $price, $ectoNet, isset($storable[$item['id']])),
            ];
        }

        return [
            'slots' => $slots,
            'characters' => array_map(fn (string $name) => ['name' => $name] + ($bagCapacity[$name] ?? ['slots' => 0, 'used' => 0]), $names),
            'shared' => ['slots' => count($shared), 'used' => count(array_filter($shared))],
            'bank' => ['slots' => count($bank), 'used' => count(array_filter($bank))],
            'ecto' => ['buy' => $ectoBuy ?: null, 'net' => $ectoNet],
            'rules' => [
                'ectoPerRare' => Gw2SellAdvisor::ECTO_PER_RARE,
                'ectoPerExotic' => Gw2SellAdvisor::ECTO_PER_EXOTIC,
                'ectoMinLevel' => Gw2SellAdvisor::ECTO_MIN_LEVEL,
                'lowValueThreshold' => Gw2SellAdvisor::LOW_VALUE_THRESHOLD,
                'fees' => Gw2SellAdvisor::LISTING_FEE + Gw2SellAdvisor::EXCHANGE_FEE,
            ],
        ];
    }
}
