<?php

namespace App\Service;

use App\Entity\User;
use App\Entity\WatchedShow;
use App\Entity\WatchedEpisode;
use Doctrine\ORM\EntityManagerInterface;

class WatchedShowService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function addShow(User $user, int $tmdbId): bool
    {
        $watchedShow = $this->em->getRepository(WatchedShow::class)->findOneBy([
            'User' => $user,
            'showId' => $tmdbId,
        ]);

        if ($watchedShow) {
            return false; // already added
        }

        $show = new WatchedShow();
        $show->setUser($user);
        $show->setShowId($tmdbId);

        $this->em->persist($show);
        $this->em->flush();

        return true;
    }

    public function deleteShow(User $user, int $id): ?bool
    {
        $show = $this->em->getRepository(WatchedShow::class)->find($id);

        if (!$show) {
            return null; // not found
        }

        if ($show->getUser() !== $user) {
            return false; // forbidden
        }

        $this->em->remove($show);
        $this->em->flush();

        return true;
    }

    public function watchEpisode(User $user, int $showId, int $episodeId): array
    {
        $watchedShow = $this->em->getRepository(WatchedShow::class)->findOneBy([
            'User' => $user,
            'showId' => $showId,
        ]);

        if (!$watchedShow) {
            $watchedShow = new WatchedShow();
            $watchedShow->setUser($user);
            $watchedShow->setShowId($showId);
            $this->em->persist($watchedShow);
        }

        $watchedEpisode = $this->em->getRepository(WatchedEpisode::class)->findOneBy([
            'watchedShow' => $watchedShow,
            'episodeId' => $episodeId,
        ]);

        if ($watchedEpisode) {
            $watchedEpisode->setWatchCount($watchedEpisode->getWatchCount() + 1);
            $this->em->flush();

            return [
                'status' => 'Episode already watched, watch count incremented',
                'watchCount' => $watchedEpisode->getWatchCount()
            ];
        }

        $episode = new WatchedEpisode();
        $episode->setEpisodeId($episodeId);
        $episode->setWatchedShow($watchedShow);

        $this->em->persist($episode);
        $this->em->flush();

        return ['status' => 'created', 'watchCount' => 1];
    }

    public function unwatchEpisode(User $user, int $id): ?bool
    {
        $episode = $this->em->getRepository(WatchedEpisode::class)->find($id);

        if (!$episode) {
            return null;
        }

        if ($episode->getWatchedShow()->getUser() !== $user) {
            return false;
        }

        $this->em->remove($episode);
        $this->em->flush();

        return true;
    }
}
