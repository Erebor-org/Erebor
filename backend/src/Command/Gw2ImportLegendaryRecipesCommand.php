<?php

namespace App\Command;

use App\Service\Gw2\Gw2ApiClient;
use App\Service\Gw2\Gw2LegendaryCatalog;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Dev tool, run by hand, never called at runtime. Builds the data/gw2/ files of the legendary
 * tracker for every legendary in Gw2LegendaryCatalog, in three steps (--step, all by default):
 *
 * - recipes      legendary_recipes.json: the recipe trees. The official API only knows regular
 *                crafting; the Mystic Forge recipes (gifts, final combination...) come from
 *                gw2treasures.com item pages.
 * - achievements legendary_achievements.json: the in-game collections of each legendary
 *                ("Arme légendaire : X", precursor collections "X I/II/III"), whose progress the
 *                account API exposes.
 * - hints        legendary_hints.json: how to get the items that can't be bought, from the
 *                Le Bus Magique legendary guides (French).
 *
 * Pages are read politely (5 s between requests) and kept in var/ so a re-run doesn't fetch
 * them again. legendary_sources.json is written by hand and wins over the generated hints.
 */
#[AsCommand(name: 'app:gw2:import-legendary-recipes', description: 'Build the legendary tracker data (recipe trees, in-game collections, hints)')]
class Gw2ImportLegendaryRecipesCommand extends Command
{
    private const API = 'https://api.guildwars2.com/v2';
    private const TREASURES = 'https://en.gw2treasures.com/item/';
    private const USER_AGENT = 'Erebor guild site - private legendary tracker recipe import';
    private const CRAWL_DELAY = 5;
    private const LEGENDARY_WEAPONS_CATEGORY = 114; // achievement category "Armes légendaires"
    private const GUIDES = [
        'https://www.lebusmagique.fr/pages/guides/armurerie-legendaire/armes-legendaires/les-armes-legendaires.html',
        'https://www.lebusmagique.fr/pages/guides/armurerie-legendaire/armes-legendaires/armes-legendaires-end-of-dragons.html',
    ];
    private const STEPS = ['recipes', 'achievements', 'hints'];
    // Le Bus Magique shows prices with icons: their alt text, in words
    private const ICON_WORDS = [
        'piecedor' => 'po', 'or' => 'po', "pièce d'argent" => 'pa', '20px piece d argent' => 'pa',
        'pièce de bronze' => 'pc', '20px piece de bronze' => 'pc', 'karma 20px' => 'karma',
        '40px mystic forge' => '', 'cclncompagnoecommerciale2' => '(CCLN)',
        '20px shard of glory' => 'éclats de gloire', '20px memory of battle' => 'souvenirs de bataille',
        '20px ascalonian tear' => 'larmes ascaloniennes', '20px relique fractale' => 'reliques fractales',
        '2604569' => 'faveurs impériales', '1495572' => 'éclats d\'esprit',
    ];

    // Mystic Forge recipes we never expand: random results, or material promotions
    // (T5 -> T6...) that would turn every fine material into an endless chain
    private const NOT_EXPANDED = [
        19675, // Mystic Clover: random Mystic Forge result
    ];

    private array $items = [];
    private array $storable = [];
    private ?float $lastFetch = null;

    public function __construct(
        private HttpClientInterface $httpClient,
        private string $projectDir,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('offline', null, InputOption::VALUE_NONE, 'Only use pages already cached in var/');
        $this->addOption('step', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'recipes, achievements and/or hints', self::STEPS);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $offline = $input->getOption('offline');
        $steps = $input->getOption('step');
        if (array_diff($steps, self::STEPS)) {
            $io->error('Unknown step, expected: '.implode(', ', self::STEPS));

            return Command::INVALID;
        }

        if (in_array('recipes', $steps, true)) {
            $this->importRecipes($io, $offline);
        }
        if (in_array('achievements', $steps, true)) {
            $this->importAchievements($io);
        }
        if (in_array('hints', $steps, true)) {
            $this->importHints($io, $offline);
        }

        return Command::SUCCESS;
    }

    private function importRecipes(SymfonyStyle $io, bool $offline): void
    {

        foreach ($this->api('/materials', ['ids' => 'all']) as $category) {
            foreach ($category['items'] as $id) {
                $this->storable[$id] = true;
            }
        }

        $recipes = [];
        $alternatives = [];
        $queue = array_column(Gw2LegendaryCatalog::ITEMS, 'id');
        $seen = [];

        while ($queue) {
            $id = array_shift($queue);
            if (isset($seen[$id])) {
                continue;
            }
            $seen[$id] = true;
            $item = $this->item($id);
            $name = $item['name'] ?? "#$id";

            $recipe = null;
            $crafting = $this->api('/recipes/search', ['output' => $id]);
            if ($crafting) {
                $raw = $this->api('/recipes/'.$crafting[0]);
                $recipe = [
                    'source' => 'crafting',
                    'output' => $raw['output_item_count'],
                    'disciplines' => $raw['disciplines'],
                    'ingredients' => array_values(array_map(fn (array $i) => [
                        'type' => 'Currency' === $i['type'] ? 'currency' : 'item',
                        'id' => $i['id'],
                        'count' => $i['count'],
                    ], array_filter($raw['ingredients'], fn (array $i) => in_array($i['type'], ['Item', 'Currency'], true)))),
                ];
            } elseif (!isset($this->storable[$id]) && !in_array($id, self::NOT_EXPANDED, true)) {
                $forge = array_values(array_filter(
                    $this->treasuresRecipes($id, $offline, $io),
                    fn (array $r) => 'Mystic Forge' === $r['discipline'],
                ));
                if ($forge) {
                    $recipe = ['source' => 'mystic_forge', 'output' => 1, 'ingredients' => $forge[0]['ingredients']];
                    if (count($forge) > 1) {
                        $alternatives[$id] = count($forge) - 1;
                    }
                }
            }

            if (!$recipe) {
                $io->writeln(sprintf('  <comment>leaf</comment>   %d %s', $id, $name));
                continue;
            }

            $recipes[$id] = $recipe;
            $io->writeln(sprintf('  <info>%-7s</info> %d %s (%d ingredients)', 'crafting' === $recipe['source'] ? 'craft' : 'forge', $id, $name, count($recipe['ingredients'])));
            foreach ($recipe['ingredients'] as $ingredient) {
                if ('item' === $ingredient['type'] && !isset($seen[$ingredient['id']])) {
                    $queue[] = $ingredient['id'];
                }
            }
        }

        ksort($recipes);
        $file = $this->writeData('legendary_recipes.json', [
            'sources' => ['official API /v2/recipes', 'gw2treasures.com (Mystic Forge recipes)'],
            'recipes' => $recipes,
        ]);

        $io->success(sprintf('%d recipes written to %s', count($recipes), $file));
        if ($alternatives) {
            $io->note('Several Mystic Forge recipes found (first one kept) for: '.implode(', ', array_map(
                fn ($id) => sprintf('%d %s', $id, $this->items[$id]['name'] ?? ''),
                array_keys($alternatives),
            )));
        }
    }

    /**
     * Matches the "Armes légendaires" achievements to the catalogue by French name:
     * "Arme légendaire : Crépuscule" and the precursor collections "Crépuscule I/II/III : ...".
     */
    private function importAchievements(SymfonyStyle $io): void
    {
        // Recent schemas list the category's achievements as {id, ...} objects
        $ids = array_map(
            fn ($a) => is_array($a) ? $a['id'] : $a,
            $this->api('/achievements/categories/'.self::LEGENDARY_WEAPONS_CATEGORY)['achievements'] ?? [],
        );
        $achievements = [];
        foreach (array_chunk($ids, 200) as $chunk) {
            foreach ($this->api('/achievements', ['ids' => implode(',', $chunk), 'lang' => 'fr']) ?? [] as $achievement) {
                $achievements[] = $achievement;
            }
        }

        $catalogIds = array_column(Gw2LegendaryCatalog::ITEMS, 'id');
        $names = [];
        foreach ($this->api('/items', ['ids' => implode(',', $catalogIds), 'lang' => 'fr']) ?? [] as $item) {
            $names[$item['id']] = $item['name'];
        }

        // The game isn't consistent: item "Eternité" but achievement "Éternité", "La Herse" but "Herse III"
        $normalize = fn (string $s) => strtr(
            mb_strtolower(trim(preg_replace('/^(le|la|les)\s+/iu', '', str_replace("\u{a0}", ' ', $s)))),
            ['é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e', 'à' => 'a', 'â' => 'a', 'î' => 'i', 'ï' => 'i', 'ô' => 'o', 'û' => 'u', 'ù' => 'u', 'ç' => 'c'],
        );
        $links = [];
        foreach ($names as $id => $name) {
            $collection = null;
            $precursor = [];
            foreach ($achievements as $achievement) {
                $title = str_replace("\u{a0}", ' ', $achievement['name']);
                if (preg_match('/^Arme légendaire\s*:\s*(.+)$/u', $title, $m) && $normalize($m[1]) === $normalize($name)) {
                    $collection = $achievement['id'];
                } elseif (preg_match('/^(.+?)\s+(I{1,3}|IV)\s*:/u', $title, $m) && $normalize($m[1]) === $normalize($name)) {
                    $precursor[$m[2]] = $achievement['id'];
                }
            }
            uksort($precursor, fn ($a, $b) => strlen($a) <=> strlen($b) ?: strcmp($a, $b));
            if ($collection || $precursor) {
                $links[$id] = ['collection' => $collection, 'precursor' => array_values($precursor)];
            }
            $io->writeln(sprintf('  %-45s collection %-6s precursor %s', $name, $collection ?? '-', implode(', ', $precursor) ?: '-'));
        }

        $file = $this->writeData('legendary_achievements.json', [
            'sources' => ['official API /v2/achievements (category "Armes légendaires")'],
            'legendaries' => $links,
        ]);
        $io->success(sprintf('Collections of %d legendaries written to %s', count($links), $file));
    }

    /**
     * Le Bus Magique guides list, for each ingredient, how to get it. Every table row (or list
     * entry) that starts with an item link gives that item's hint.
     */
    private function importHints(SymfonyStyle $io, bool $offline): void
    {
        $recipes = json_decode(@file_get_contents($this->projectDir.'/data/gw2/legendary_recipes.json'), true)['recipes'] ?? [];
        $wanted = [];
        foreach ($recipes as $outputId => $recipe) {
            $wanted[(int) $outputId] = true;
            foreach ($recipe['ingredients'] as $ingredient) {
                if ('item' === $ingredient['type']) {
                    $wanted[$ingredient['id']] = true;
                }
            }
        }

        $hints = [];
        foreach (self::GUIDES as $url) {
            $html = $this->fetchCached($url, 'lebusmagique/'.basename($url), $offline, $io);
            if (!$html) {
                continue;
            }
            $dom = new \DOMDocument();
            @$dom->loadHTML('<?xml encoding="utf-8"?>'.$html);
            $xpath = new \DOMXPath($dom);

            foreach ($xpath->query('//tr') as $row) {
                $cells = $xpath->query('./td', $row);
                if ($cells->length < 2) {
                    continue;
                }
                $link = $xpath->query('.//a[@data-gw2-item or @data-item-id]', $cells->item(0))->item(0);
                if (!$link) {
                    continue;
                }
                // A next cell starting with "250 <item>": the row lists a recipe, not how to get this item
                $nested = false;
                for ($i = 1; $i < $cells->length; ++$i) {
                    $nested = $nested || $this->startsWithItem($cells->item($i), $xpath);
                }
                if ($nested) {
                    continue;
                }
                $id = (int) ($link->getAttribute('data-gw2-item') ?: $link->getAttribute('data-item-id'));
                $text = '';
                for ($i = 1; $i < $cells->length; ++$i) {
                    $text .= ' '.$this->readable($cells->item($i));
                }
                $text = trim(preg_replace('/\s+/u', ' ', $text));
                if (isset($wanted[$id]) && mb_strlen($text) > 10 && !isset($hints[$id])) {
                    $hints[$id] = ['hint' => $text, 'source' => $url];
                }
            }

            // Inline mentions, in lists: "Don de la Province de Seitung — Province de Seitung explorée à 100 %",
            // "Don d'Ascalon (500 larmes ascaloniennes au marchand de donjon...)"
            foreach ($xpath->query('//a[@data-gw2-item or @data-item-id]') as $link) {
                $id = (int) ($link->getAttribute('data-gw2-item') ?: $link->getAttribute('data-item-id'));
                if (!isset($wanted[$id]) || isset($hints[$id])) {
                    continue;
                }
                $after = '';
                for ($node = $link->nextSibling; $node && mb_strlen($after) < 300; $node = $node->nextSibling) {
                    if ($node instanceof \DOMElement && ($node->hasAttribute('data-gw2-item') || $node->hasAttribute('data-item-id') || 'br' === $node->nodeName)) {
                        break;
                    }
                    $after .= $node instanceof \DOMElement ? $this->readable($node) : $node->textContent;
                }
                $after = trim(preg_replace('/\s+/u', ' ', $after));
                $sentence = !preg_match('/^[,.;:]/u', $after) && !str_contains($after, '[&') && count(preg_split('/\s+/u', $after)) >= 5;
                if (preg_match('/^\((.{10,})\)$/u', $after, $m) || $sentence) {
                    $hints[$id] = ['hint' => $m[1] ?? $after, 'source' => $url];
                }
            }
        }

        // The legendaries themselves aren't ingredients to get
        $hints = array_diff_key($hints, array_flip(array_column(Gw2LegendaryCatalog::ITEMS, 'id')));
        ksort($hints);
        $file = $this->writeData('legendary_hints.json', [
            'sources' => array_values(self::GUIDES),
            'items' => $hints,
        ]);
        $io->success(sprintf('%d hints written to %s', count($hints), $file));
    }

    private function startsWithItem(\DOMNode $cell, \DOMXPath $xpath): bool
    {
        $link = $xpath->query('.//a[@data-gw2-item or @data-item-id]', $cell)->item(0);
        if (!$link) {
            return false;
        }
        $before = '';
        foreach ($xpath->query('.//text()', $cell) as $text) {
            if ($link->contains($text)) {
                break;
            }
            $before .= $text->textContent;
        }

        return 1 === preg_match('/^[\s\d\x{a0}]*$/u', $before);
    }

    // Text of a page fragment, with price icons turned into words and map links dropped
    private function readable(\DOMNode $node): string
    {
        $clone = $node->cloneNode(true);
        $doc = $clone->ownerDocument;
        foreach (iterator_to_array($clone instanceof \DOMElement ? $clone->getElementsByTagName('img') : []) as $img) {
            $word = self::ICON_WORDS[mb_strtolower($img->getAttribute('alt'))] ?? '';
            $img->parentNode->replaceChild($doc->createTextNode($word ? ' '.$word.' ' : ' '), $img);
        }

        return preg_replace('/\[&[A-Za-z0-9+\/=]+\]/', '', $clone->textContent);
    }

    private function fetchCached(string $url, string $cacheName, bool $offline, SymfonyStyle $io): ?string
    {
        $cache = $this->projectDir.'/var/'.$cacheName;
        if (is_file($cache)) {
            return file_get_contents($cache);
        }
        if ($offline) {
            return null;
        }
        $this->politeDelay();
        $response = $this->httpClient->request('GET', $url, ['headers' => ['User-Agent' => self::USER_AGENT]]);
        $html = $response->getContent(false);
        if (200 !== $response->getStatusCode()) {
            $io->warning(sprintf('%s answered %d', $url, $response->getStatusCode()));

            return null;
        }
        @mkdir(dirname($cache), 0775, true);
        file_put_contents($cache, $html);

        return $html;
    }

    // gw2treasures' robots.txt asks for 5 s between requests: the same pace for every site
    private function politeDelay(): void
    {
        if (null !== $this->lastFetch) {
            $wait = self::CRAWL_DELAY - (microtime(true) - $this->lastFetch);
            if ($wait > 0) {
                usleep((int) ($wait * 1_000_000));
            }
        }
        $this->lastFetch = microtime(true);
    }

    private function writeData(string $name, array $data): string
    {
        $file = $this->projectDir.'/data/gw2/'.$name;
        @mkdir(dirname($file), 0775, true);
        file_put_contents($file, json_encode(
            ['generatedAt' => (new \DateTimeImmutable())->format(\DATE_ATOM)] + $data,
            \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_UNICODE,
        )."\n");

        return $file;
    }

    /** @return array<int, array{discipline: string, ingredients: array}> recipes of the "Crafted From" section */
    private function treasuresRecipes(int $id, bool $offline, SymfonyStyle $io): array
    {
        $cache = $this->projectDir.'/var/gw2treasures/'.$id.'.html';
        if (is_file($cache)) {
            $html = file_get_contents($cache);
        } elseif ($offline) {
            return [];
        } else {
            $this->politeDelay();
            $response = $this->httpClient->request('GET', self::TREASURES.$id, ['headers' => ['User-Agent' => self::USER_AGENT]]);
            $html = $response->getContent(false);
            if (200 !== $response->getStatusCode()) {
                $io->warning(sprintf('gw2treasures answered %d for item %d', $response->getStatusCode(), $id));

                return [];
            }
            @mkdir(dirname($cache), 0775, true);
            file_put_contents($cache, $html);
        }

        $dom = new \DOMDocument();
        @$dom->loadHTML($html);
        $xpath = new \DOMXPath($dom);

        // Recipe boxes that follow the "Crafted From" heading, up to the next heading
        $heading = $xpath->query('//h2[contains(., "Crafted From")]')->item(0);
        if (!$heading) {
            return [];
        }

        $recipes = [];
        for ($node = $heading->nextSibling; $node && 'h2' !== $node->nodeName; $node = $node->nextSibling) {
            if (!$node instanceof \DOMElement) {
                continue;
            }
            foreach ($xpath->query('.//div[contains(@class, "RecipeBox_box")]', $node) as $box) {
                $discipline = trim($xpath->query('.//span[contains(@class, "RecipeBox_disciplines")]', $box)->item(0)?->textContent ?? '');
                $ingredients = [];
                foreach ($xpath->query('.//div[contains(@class, "Ingredients_count")]', $box) as $countNode) {
                    $link = $countNode->nextSibling;
                    while ($link && !$link instanceof \DOMElement) {
                        $link = $link->nextSibling;
                    }
                    if (!$link || !preg_match('#^/(item|currency)/(\d+)#', $link->getAttribute('href'), $m)) {
                        continue;
                    }
                    $ingredients[] = [
                        'type' => $m[1],
                        'id' => (int) $m[2],
                        'count' => (int) str_replace([',', '×', ' '], '', $countNode->textContent),
                    ];
                }
                if ($ingredients) {
                    $recipes[] = ['discipline' => $discipline, 'ingredients' => $ingredients];
                }
            }
        }

        return $recipes;
    }

    private function item(int $id): array
    {
        return $this->items[$id] ??= $this->api('/items/'.$id) ?? [];
    }

    private function api(string $path, array $query = []): mixed
    {
        usleep(200_000); // stay far below the 300 requests/min limit
        $response = $this->httpClient->request('GET', self::API.$path, [
            'query' => $query,
            // Same schema as Gw2ApiClient: recipe ingredients as {type, id, count}
            'headers' => ['X-Schema-Version' => Gw2ApiClient::SCHEMA_VERSION],
        ]);

        return 200 === $response->getStatusCode() || 206 === $response->getStatusCode()
            ? $response->toArray(false)
            : null;
    }
}
