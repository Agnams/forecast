<?php
namespace App\Service;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class IPService
{
    public function getIP(): string
    {
        //Gets client IP
        $request = Request::createFromGlobals();

        $ip = $request->getClientIp();

        return $ip;
    }

    public function showIP():string{
        $cache = new FilesystemAdapter();
        $cache_IP = $cache->getItem('info.IP');

        //If IP isn't saved in cache, then saves it
        if (!$cache_IP->isHit()) {
            $cache_IP->set($this->getIP());
            $cache->save($cache_IP);
        }
        return $cache_IP->get();
    }
}