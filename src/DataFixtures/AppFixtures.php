<?php

namespace App\DataFixtures;

use App\Entity\Secret;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use DateInterval;
use DateTimeImmutable;
use Symfony\Component\Uid\Uuid;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Secret1 is expired
        $secret1 = new Secret();
        $expireAfter = 10;
        $createdAt = new \DateTimeImmutable();
        $expiresAt = $expireAfter > 0 ? $createdAt->sub(new DateInterval('PT'.$expireAfter.'M')) : $createdAt;
        $secret1->setSecretText("First secret expires after 30 minutes and has 5 views");
        $secret1->setHash('GfwGbGzu9VYJWafKWxhRCj');
        $secret1->setCreatedAt($createdAt);
        $secret1->setExpiresAt($expiresAt);
        $secret1->setRemainingViews(5);
        $manager->persist($secret1);

        // Secret2 is ok, has 100 views and never expire
        $secret2 = new Secret();
        $expireAfter = 0;
        $createdAt = new \DateTimeImmutable();
        $expiresAt = $expireAfter > 0 ? $createdAt->add(new DateInterval('PT'.$expireAfter.'M')) : $createdAt;
        $secret2->setSecretText("Second secret never expires and has 10 views.");
        $secret2->setHash('GgvtTLfxzJaRLuXKEfevdg');
        $secret2->setCreatedAt($createdAt);
        $secret2->setExpiresAt($expiresAt);
        $secret2->setRemainingViews(100);
        $manager->persist($secret2);

        // Secret3 has no more views
        $secret3 = new Secret();
        $expireAfter = 120;
        $createdAt = new \DateTimeImmutable();
        $expiresAt = $expireAfter > 0 ? $createdAt->add(new DateInterval('PT'.$expireAfter.'M')) : $createdAt;
        $secret3->setSecretText("Third secret expires after 120 minutes but has no more views");
        $secret3->setHash('Y6xbuBrRh5EfGn96dN4dvy');
        $secret3->setCreatedAt($createdAt);
        $secret3->setExpiresAt($expiresAt);
        $secret3->setRemainingViews(0);
        $manager->persist($secret3);

        $manager->flush();
    }
}
