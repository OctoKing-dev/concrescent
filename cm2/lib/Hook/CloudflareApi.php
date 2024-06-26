<?php

namespace {
    require_once __DIR__.'/../../../vendor/autoload.php';
    require_once __DIR__.'/../database/misc.php';
    require_once __DIR__ .'/../../config/config.php';
}

namespace App\Hook {

    use Symfony\Component\HttpClient\HttpClient;
    use Symfony\Contracts\HttpClient\HttpClientInterface;

    readonly class CloudflareApi
    {
        private HttpClientInterface $client;

        public function __construct(
            ?HttpClientInterface $client = null
        ) {
            $this->client = $client ?? HttpClient::create();
        }

        public function purge( ): void
        {
            try {
                global $cm_config;

                $bearer = $cm_config['cloudflare']['bearer_token'] ?? null;
                $files = $cm_config['cloudflare']['purge']['files'] ?? null;
                $zoneId = $cm_config['cloudflare']['purge']['zone_id'] ?? null;

                if ($bearer === null || $files === null || $zoneId === null) {
                    return;
                }

                $response = $this->client->request(
                    'POST',
                    "https://api.cloudflare.com/client/v4/zones/$zoneId/purge_cache",
                    [
                        'headers' => [
                            'authorization' => "Bearer $bearer",
                        ],
                        'json' => [
                            'files' => $files,
                        ],
                    ]
                );

                \error_log('Purging Cloudflare zone ' . $zoneId . ' resulted in ' . $response->getContent());
            } catch (\Throwable $e) {
                \error_log('Failed to execute Cloudflare purge : '. $e->getMessage());
            }
        }
    }
}
