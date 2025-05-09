<?php

namespace App\Service;

use App\Entity\Characters;
use App\Entity\Blacklist;
use App\Entity\Warning;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class NotificationService
{
    private HttpClientInterface $httpClient;
    private array $webhookUrls = [
        'character_import' => 'https://n8n.neitosden.fr/webhook/import_character',
        'blacklist_added' => 'https://n8n.neitosden.fr/webhook/blacklist',
        'warning_added' => 'https://n8n.neitosden.fr/webhook/warning',
        'archivage_added' => 'https://n8n.neitosden.fr/webhook/archivage',
        'mule_import' => 'https://n8n.neitosden.fr/webhook/mule_import',
    ];

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    public function notify(string $eventType, mixed $data): void
    {
        if (!isset($this->webhookUrls[$eventType])) {
            throw new \InvalidArgumentException(sprintf('Unsupported event type: %s', $eventType));
        }

        $webhookUrl = $this->webhookUrls[$eventType];
        $payload = $this->buildPayload($eventType, $data);

        $this->httpClient->request('POST', $webhookUrl, [
            'json' => $payload,
        ]);
    }

    private function buildPayload(string $eventType, mixed $data): array
    {
        return match ($eventType) {
            'character_import' => [
                'embeds' => [$this->buildCharacterImportEmbed($data)],
            ],
            'archivage_added' => [
                'embeds' => [$this->buildCharacterArchivedEmbed($data)],
            ],
            'blacklist_added' => [
                'embeds' => [$this->buildBlacklistAddedEmbed($data)],
            ],
            'warning_added' => [
                'embeds' => [$this->buildWarningAddedEmbed($data['warning'], $data['count'])],
            ],
            'mule_import' => [
                'embeds' => [$this->buildMuleImportEmbed($data)],
            ],
            default => throw new \InvalidArgumentException(sprintf('Unsupported event type: %s', $eventType)),
        };
    }
    
    private function buildMuleImportEmbed(Mule $mule): array
    {
        $mainCharacter = $mule->getMainCharacter();
        
        return [
            'title' => 'Import de mule 🐴',
            'description' => sprintf(
                "Mule associée à **%s** :\n\n**Pseudo** : %s\n**Pseudo Ankama** : %s\n**Classe** : %s",
                $mainCharacter->getPseudo(),
                $mule->getPseudo(),
                $mule->getAnkamaPseudo(),
                $mule->getClass()
            ),
            'color' => 16776960, // Jaune
            'timestamp' => (new \DateTime())->format(\DateTime::ATOM),
            'footer' => [
                'text' => 'Système Erebor',
            ],
        ];
    }
    private function buildCharacterImportEmbed(Characters $character): array
    {
        $recruiterPseudo = $character->getRecruiter() ? $character->getRecruiter()->getPseudo() : 'Un recruteur inconnu';
        
        // Build mules list
        $mulesText = "";
        $mules = $character->getMules();
        
        // Force initialization of the collection if it's lazy-loaded
        $mules->initialize();
        
        if ($mules->count() > 0) {
            $mulesText = "\n\n**Mules** : ";
            $mulesList = [];
            foreach ($mules as $mule) {
                $mulesList[] = sprintf("%s (%s)", $mule->getPseudo(), $mule->getClass());
            }
            $mulesText .= implode(", ", $mulesList);
        }

        return [
            'title' => 'Import de personnage ⚔️',
            'description' => sprintf(
                "**%s** a recruté un nouveau joueur :\n\n**Pseudo** : %s\n**Pseudo Ankama** : %s\n**Classe** : %s%s",
                $recruiterPseudo,
                $character->getPseudo(),
                $character->getAnkamaPseudo(),
                $character->getClass(),
                $mulesText
            ),
            'color' => 3066993, // Vert clair
            'timestamp' => (new \DateTime())->format(\DateTime::ATOM),
            'footer' => [
                'text' => 'Système Erebor',
            ],
        ];
    }

    private function buildBlacklistAddedEmbed(Blacklist $blacklist): array
    {
        return [
            'title' => 'Ajout à la blacklist 🚫',
            'description' => sprintf(
                "Le joueur **%s** (%s) a été ajouté à la blacklist.\n\n**Raison** : \"%s\"",
                $blacklist->getPseudo(),
                $blacklist->getAnkamaPseudo(),
                $blacklist->getReason()
            ),
            'color' => 15158332, // Rouge clair
            'timestamp' => (new \DateTime())->format(\DateTime::ATOM),
            'footer' => [
                'text' => 'Système Erebor',
            ],
        ];
    }

    private function buildWarningAddedEmbed(Warning $warning, int $count): array
    {
        $character = $warning->getCharacter();
        $authorCharacter = $warning->getAuthorCharacter();
        $authorName = $authorCharacter ? $authorCharacter->getPseudo() : 'Système';

        $suffix = match ($count) {
            1 => "C'est son **premier avertissement**.",
            2 => "C'est son **deuxième avertissement**.",
            3 => "C'est son **troisième avertissement**.",
            default => "Cela fait **$count avertissements**.",
        };

        return [
            'title' => 'Avertissement ⚠️',
            'description' => sprintf(
                "Le joueur **%s** a reçu un avertissement par **%s**.\n\n**Raison** : \"%s\"\n\n%s",
                $character->getPseudo(),
                $authorName,
                $warning->getDescription(),
                $suffix
            ),
            'color' => 15844367,
            'timestamp' => (new \DateTime())->format(\DateTime::ATOM),
            'footer' => [
                'text' => 'Système Erebor',
            ],
        ];
    }
    private function buildCharacterArchivedEmbed(Characters $character): array
    {
        return [
            'title' => 'Archivage de joueur 📦',
            'description' => sprintf(
                "Le joueur **%s** (%s) a été archivé.\n",
                $character->getPseudo(),
                $character->getAnkamaPseudo(),
                $character->getClass()
            ),
            'color' => 9807270, // Bleu-gris
            'timestamp' => (new \DateTime())->format(\DateTime::ATOM),
            'footer' => [
                'text' => 'Système Erebor',
            ],
        ];
    }
}
