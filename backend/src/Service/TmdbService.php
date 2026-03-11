<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class TmdbService
{
    private const BASE_URL = 'https://api.themoviedb.org/3';

    public function __construct(
        private HttpClientInterface $client,
        private string $tmdbApiKey,
        private string $tmdbBearerToken,
        private CacheInterface $cache,
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

    public function getMovieById(int $id): array
    {
        return $this->cache->get('tmdb_movie_' . $id, function (ItemInterface $item) use ($id) {
            $item->expiresAfter(60 * 60 * 24 * 14); // 2 weeks

            $data = $this->request("/movie/{$id}", [
                'language' => 'fr-FR',
            ]);

            $data['poster_path'] = $data['poster_path'] ?? null;

            return $data;
        });
    }

    public function getTvShowById(int $id): array
    {
        return $this->cache->get('tmdb_tv_show_' . $id, function (ItemInterface $item) use ($id) {
            $item->expiresAfter(60 * 60 * 24 * 14); // 2 weeks

            $data = $this->request("/tv/{$id}", [
                'language' => 'fr-FR',
            ]);

            $data['poster_path'] = $data['poster_path'] ?? null;

            return $data;
        });
    }
}
