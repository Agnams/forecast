<?php
namespace App\Service;
use App\Service\ResetService;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ForecastService
{
    private $location;
    private $forecast;
    private $ip;

    public function __construct(IPService $ip, LocationService $location, HttpClientInterface $forecast)
    {
        $this->location = $location;
        $this->forecast = $forecast;
        $this->ip = $ip;
    }

    private function getForecast()
    {
        //Gets forecast for client by API
        $api_link = "https://api.openweathermap.org/data/2.5/weather?q=".$this->location->showLocation()."&appid=26e29aa16ee3a3a8af761f4dd0410824&units=metric";


        $response = $this->forecast->request(
            'GET',
            $api_link
        );

        //If city is invalid
        if($response->getStatusCode() !== 200){
            return false;
        }

        //Returns forecast by city
        $contentType = $response->getHeaders()['content-type'][0];
        $content = $response->getContent();

        return $content;
    }

    public function showForecast(){
        $cache = new FilesystemAdapter();
        
        $cache_IP = $cache->getItem('info.IP');
        $ip = $cache_IP->get();
        
        //If IP ins't saved in cache
        if($ip !== $this->ip->getIP()){
            $reset = new ResetService();
        }

        $cache_forecast = $cache->getItem('info.forecast');

        //If forecast ins't saved in cache
        if (!$cache_forecast->isHit()) {
            $cache_forecast->set($this->getForecast());
            $cache->save($cache_forecast);
        }
        return json_decode($cache_forecast->get());
    }
}