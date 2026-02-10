<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbService
{
    private const BASE_URL = 'https://api.themoviedb.org/3';

    public function __construct(
        private HttpClientInterface $client,
        private string $tmdbApiKey,
        private string $tmdbBearerToken,
    ) {}

    private function request(string $uri, array $query = []): array
    {
        $response = $this->client->request(
            'GET',
            self::BASE_URL . $uri,
            [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->tmdbBearerToken,
                    'Accept' => 'application/json',
                ],
                'query' => $query,
            ]
        );

        return $response->toArray(false);
    }

    public function search(string $query): array
    {
        return $this->request('/search/multi', [
            'query' => $query,
            'language' => 'fr-FR',
        ]);
    }
}
