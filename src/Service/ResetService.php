<?php
namespace App\Service;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class ResetService
{
    public function __construct()
    {
        $cache = new FilesystemAdapter();
        $cache->deleteItem('info.IP');
        $cache->deleteItem('info.location');
        $cache->deleteItem('info.forecast');
    }
}