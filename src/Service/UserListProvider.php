<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class UserListProvider
{

    private function requestUsersByAPIToken(string $apiToken): array
    {
        $serverIP = "127.0.0.1"; //only loopback allowed
        $requestUrl = "http://{$serverIP}:{$_SERVER['SERVER_PORT']}";

        $api_endpoint = $requestUrl . "/api/users/";

        $client = HttpClient::create([
            'headers' => [
                'x-api-token' => $apiToken
            ]
        ]);

        $response = $client->request('GET', $api_endpoint);
        $users = $response->toArray();

        return $users;
    }

    public function getUsersByAPIToken(string $apiToken): array
    {

        //caching users array for 100 seconds

        $cache = new FilesystemAdapter('UserlistArray', 100);
        $cacheItem = $cache->getItem('users.userlist_array');

        if (!$cacheItem->isHit()) {
            $users = $this->requestUsersByAPIToken($apiToken);
            $cacheItem->set($users);
            $cache->save($cacheItem);
        } else {
            $users = $cacheItem->get();
        }

        return $users;
    }
}
