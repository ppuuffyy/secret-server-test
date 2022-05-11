<?php

namespace App\Tests;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use ApiTestCase\JsonApiTestCase;

class SecretControllerTest extends ApiTestCase
{
    public function testGettingSecretByHash(): void
    {
        $response = static::createClient()->request('GET', '/secret/GgvtTLfxzJaRLuXKEfevdg');
        // Check if secret is received if hash is correct, remainingViews > 0 and secret don't expiring
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['secretText' => 'Second secret never expires and has 10 views.']);
        $remainingViews1 = json_decode($response->getContent())->remainingViews;
        
        $response = static::createClient()->request('GET', '/secret/GgvtTLfxzJaRLuXKEfevdg');
        $remainingViews2 = json_decode($response->getContent())->remainingViews;
        // Check if $remainingViews2 is decreasing after requesting
        $this->assertEquals($remainingViews2, $remainingViews1 - 1);
    }

    public function testNoSecretFoundIfNorRemainingViews(): void
    {
        $response = static::createClient()->request('GET', '/secret/Y6xbuBrRh5EfGn96dN4dvy');
        $this->assertResponseStatusCodeSame(404);
    }   
    
    public function testNoSecretFoundIfExpired(): void
    {
        $response = static::createClient()->request('GET', '/secret/GfwGbGzu9VYJWafKWxhRCj');
        $this->assertResponseStatusCodeSame(404);
    }    
    
    // public function testCreateNewSecret(): void
    // {
    //     $response = static::createClient()->request('POST', '/secret', [
    //         'body' => [
    //             'secret' => 'Testing if secret is created',
    //             'expireAfterViews' => '15',
    //             'expireAfter' => '5'
    //         ]
    //     ]);
    //     $this->assertResponseIsSuccessful();
    //     $this->assertJsonContains(['secretText' => 'Testing if secret is created']);
    // }      
    
}
