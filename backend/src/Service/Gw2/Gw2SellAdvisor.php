<?php

namespace App\Service\Gw2;

/**
 * Says what to do with an item stack: sell it on the trading post (instantly, to the highest
 * buy order), sell it to a merchant, salvage it, or keep it. Pure logic, no API call.
 *
 * The API doesn't expose salvage results, so salvage is only valued where a reliable average
 * exists: Globs of Ectoplasm from rare/exotic gear level 68+. Lower gear is salvaged when it is
 * worth almost nothing anyway.
 */
class Gw2SellAdvisor
{
    public const SELL = 'sell';
    public const VENDOR = 'vendor';
    public const SALVAGE = 'salvage';
    public const KEEP = 'keep';

    public const ECTO_ID = 19721;
    // Average Globs of Ectoplasm per item salvaged with a master's/mystic kit (community averages)
    public const ECTO_PER_RARE = 0.875;
    public const ECTO_PER_EXOTIC = 1.2;
    public const ECTO_MIN_LEVEL = 68;

    // Below 1 silver at best, low-level gear is worth more as crafting materials
    public const LOW_VALUE_THRESHOLD = 100;

    // Trading post fees taken from the seller: 5 % listing + 10 % exchange, each at least 1 copper
    public const LISTING_FEE = 0.05;
    public const EXCHANGE_FEE = 0.10;

    private const GEAR_TYPES = ['Armor', 'Weapon', 'Trinket', 'Back'];

    /** What the seller actually receives when selling at $unitPrice */
    public static function netOfFees(int $unitPrice): int
    {
        if ($unitPrice <= 0) {
            return 0;
        }

        return max(0, $unitPrice
            - max(1, (int) round($unitPrice * self::LISTING_FEE))
            - max(1, (int) round($unitPrice * self::EXCHANGE_FEE)));
    }

    /**
     * @param array      $item              formatted slot (Gw2ItemFormatter): rarity, level, itemType, flags, binding, vendorValue, count
     * @param array|null $price             /commerce/prices entry, null when the item can't be traded
     * @param int|null   $ectoUnitNet       what one ecto sells for instantly, after fees
     * @param bool       $storableMaterial  the material storage accepts this item
     *
     * @return array{action: string, reason: string, tradable: bool, unit: array{sell: ?int, sellGross: ?int, vendor: ?int, salvage: ?int}, total: ?int}
     */
    public function advise(array $item, ?array $price, ?int $ectoUnitNet, bool $storableMaterial): array
    {
        $flags = $item['flags'] ?? [];
        $bound = null !== ($item['binding'] ?? null)
            || in_array('AccountBound', $flags, true)
            || in_array('SoulbindOnAcquire', $flags, true);

        $buy = (int) ($price['buys']['unit_price'] ?? 0);
        $tradable = !$bound && null !== $price;
        // Selling instantly needs at least one buy order
        $sell = $tradable && $buy > 0 ? self::netOfFees($buy) : null;
        $noSaleReason = $tradable ? 'Aucun ordre d\'achat au comptoir' : 'Invendable au comptoir';
        $vendor = in_array('NoSell', $flags, true) ? null : ((int) ($item['vendorValue'] ?? 0) ?: null);

        $isGear = in_array($item['itemType'] ?? null, self::GEAR_TYPES, true);
        $salvageable = $isGear && !in_array('NoSalvage', $flags, true);
        $salvage = $salvageable ? $this->ectoSalvageValue($item, $ectoUnitNet) : null;

        $unit = ['sell' => $sell, 'sellGross' => $buy ?: null, 'vendor' => $vendor, 'salvage' => $salvage];
        $decide = fn (string $action, string $reason) => [
            'action' => $action,
            'reason' => $reason,
            'tradable' => $tradable,
            'unit' => $unit,
            'total' => match ($action) {
                self::SELL => $sell * ($item['count'] ?? 1),
                self::VENDOR => $vendor * ($item['count'] ?? 1),
                self::SALVAGE => null !== $salvage ? (int) round($salvage * ($item['count'] ?? 1)) : null,
                default => null,
            },
        ];

        $rarity = $item['rarity'] ?? null;
        if ('Junk' === $rarity) {
            return null !== $vendor
                ? $decide(self::VENDOR, 'Objet inutile : il ne sert qu\'à être vendu au marchand.')
                : $decide(self::KEEP, 'Objet inutile, mais le marchand ne le reprend pas.');
        }
        if (in_array($rarity, ['Ascended', 'Legendary'], true)) {
            return $decide(self::KEEP, 'Objet élevé ou légendaire : à garder.');
        }

        if ($isGear) {
            // Rare/exotic level 68+: real comparison between the three outlets
            if (null !== $salvage) {
                $options = array_filter([self::SELL => $sell, self::VENDOR => $vendor, self::SALVAGE => $salvage], fn ($v) => null !== $v);
                arsort($options);
                $best = array_key_first($options);

                return $decide($best, match ($best) {
                    self::SALVAGE => $bound
                        ? 'Lié, donc invendable au comptoir : les ectoplasmes du recyclage valent plus que le marchand.'
                        : 'Les ectoplasmes du recyclage valent plus que la vente instantanée.',
                    self::SELL => 'La vente instantanée rapporte plus que les ectoplasmes du recyclage.',
                    default => 'Le marchand en donne plus que le comptoir et le recyclage.',
                });
            }

            if ($bound) {
                if ($salvageable) {
                    return $decide(self::SALVAGE, 'Lié, donc invendable au comptoir : le recyclage récupère des matériaux.');
                }

                return null !== $vendor
                    ? $decide(self::VENDOR, 'Lié et non recyclable : seul le marchand le reprend.')
                    : $decide(self::KEEP, 'Lié, non recyclable et refusé par le marchand.');
            }

            if ($salvageable && max($sell ?? 0, $vendor ?? 0) < self::LOW_VALUE_THRESHOLD) {
                return $decide(self::SALVAGE, 'Vaut moins de 1 pa à la vente : ses matériaux de recyclage valent plus.');
            }

            return ($sell ?? 0) >= ($vendor ?? 0) && null !== $sell
                ? $decide(self::SELL, 'Se vend mieux au comptoir qu\'au marchand.')
                : $decide(self::VENDOR, null === $sell
                    ? $noSaleReason.' : le marchand le reprend.'
                    : 'Le marchand en donne plus que le comptoir.');
        }

        if ($storableMaterial) {
            return $decide(self::KEEP, 'Matériau : à déposer dans le stockage de matériaux.');
        }
        if ('Container' === ($item['itemType'] ?? null)) {
            return $decide(self::KEEP, 'Conteneur : à ouvrir.');
        }
        if (null !== $sell && $sell >= ($vendor ?? 0)) {
            return $decide(self::SELL, 'Se vend au comptoir.');
        }
        if (null !== $vendor) {
            return $decide(self::VENDOR, null === $sell
                ? $noSaleReason.' : le marchand le reprend.'
                : 'Le marchand en donne plus que le comptoir.');
        }

        return $decide(self::KEEP, match (true) {
            $bound => 'Lié : ni vendable ni recyclable.',
            $tradable => 'Aucun ordre d\'achat au comptoir et refusé par le marchand.',
            default => 'Ni vendable ni recyclable.',
        });
    }

    private function ectoSalvageValue(array $item, ?int $ectoUnitNet): ?int
    {
        if (!$ectoUnitNet || ($item['level'] ?? 0) < self::ECTO_MIN_LEVEL) {
            return null;
        }

        return match ($item['rarity'] ?? null) {
            'Rare' => (int) round(self::ECTO_PER_RARE * $ectoUnitNet),
            'Exotic' => (int) round(self::ECTO_PER_EXOTIC * $ectoUnitNet),
            default => null,
        };
    }
}
