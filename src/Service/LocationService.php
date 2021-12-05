<?php
namespace App\Service;
use App\Service\IPService;
use App\Entity\Location;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Doctrine\Persistence\ManagerRegistry;

class LocationService
{
    private $location;
    private $ip;
    private $doctrine;

    public function __construct(IPService $ip, HttpClientInterface $location, ManagerRegistry $doctrine)
    {
        $this->location = $location;
        $this->ip = $ip;
        $this->doctrine = $doctrine;
    }

    private function getLocation():string
    {
        //Returns City from DB by IP if there is one
        $city_by_ip = $this->doctrine->getRepository(Location::class)->findOneBy(['ip' => $this->ip->getIP()]);
        if($city_by_ip){
            return $city_by_ip->getCity();
        }
        
        //Gets city by IP using API
        $api_link = "http://ipinfo.io/".$this->ip->showIP()."/json";

        $response = $this->location->request(
            'GET',
            $api_link
        );

        //returns false if IP is invalid
        if($response->getStatusCode() !== 200){
            return false;
        }
        
        //Gets and returns city from API
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();
        $content = $response->toArray();

        //Returns false if can't find city by IP
        if(!@$content["city"]){
            return false;
        }
        return $content["city"];
    }

    public function showLocation(): string{

        $cache = new FilesystemAdapter();
        $cache_location = $cache->getItem('info.location');
        $cache_ip = $cache->getItem('info.IP');

        //If city with IP isn't saved in cache, then saves it
        if (!$cache_location->isHit()) {

            $cache_location->set($this->getLocation());
            $cache_ip->set($this->ip->getIP());

            $cache->save($cache_location);
            $cache->save($cache_ip);

            $this->saveLocation();

        }
        return $cache_location->get();
    }

    private function saveLocation(){

        //If there is saved client IP with city in DB
        $city = $this->doctrine->getRepository(Location::class)->findBy(array('ip' => $this->ip->getIP()));
        if($city){
            return;
        }
        //If client IP with city isn't saved in DB, then do that
        $entityManager = $this->doctrine->getManager();
    
        $location = new Location();
        
        $location->setIp($this->ip->getIP());
        $location->setCity($this->getLocation());

        $entityManager->persist($location);
        $entityManager->flush();
    }
}