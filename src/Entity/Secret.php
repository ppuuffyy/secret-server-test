<?php

namespace App\Entity;

use App\Repository\SecretRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


#[ORM\Entity(repositoryClass: SecretRepository::class)]
#[UniqueEntity('hash')]
class Secret
{
    const CHIPER = "AES-128-CBC";
    const KEY = '25c6c7ff35b9979b151f2136cd13b0ff';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private $hash;

    #[ORM\Column(type: 'text')]
    private $secretText;

    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private $expiresAt;

    #[ORM\Column(type: 'bigint')]
    private $remainingViews;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function setHash(string $hash): self
    {
        $this->hash = $hash;

        return $this;
    }

    public function getSecretText(): ?string
    {

        return $this->decryptSecret($this->secretText);
    }

    public function setSecretText(string $secretText): self
    {

        $this->secretText = $this->encryptSecret($secretText);

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    
    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getExpiresAt(): ?\DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function setExpiresAt(\DateTimeImmutable $expiresAt): self
    {
        $this->expiresAt = $expiresAt;

        return $this;
    }

    public function getRemainingViews(): ?string
    {
        return $this->remainingViews;
    }

    public function setRemainingViews(string $remainingViews): self
    {
        $this->remainingViews = $remainingViews;

        return $this;
    }

    private function encryptSecret(string $secretText): string {
        $ivlen = openssl_cipher_iv_length(self::CHIPER);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($secretText, self::CHIPER, self::KEY, $options=OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, self::KEY, $as_binary=true);
        $ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );
        return $ciphertext;
    }

    private function decryptSecret(string $secretText): string {
        $c = base64_decode($secretText);
        $ivlen = openssl_cipher_iv_length(self::CHIPER);
        $iv = substr($c, 0, $ivlen);
        $sha2len=32;
        $ciphertext_raw = substr($c, $ivlen+$sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, self::CHIPER, self::KEY, $options=OPENSSL_RAW_DATA, $iv);
        return $original_plaintext;
    }

    public function getDataAsArray(): array{
        return [
            'hash' => $this->getHash(),
            'secretText'=> $this->getSecretText(),
            "createdAt" => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            "expiresAt" => $this->getExpiresAt()->format('Y-m-d H:i:s'),
            "remainingViews" => $this->getRemainingViews()
        ];
    }
    
}
