<?php

namespace App\Controller;

use App\Service\ForecastService;
use App\Service\ResetService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main")
     */
    public function index(ForecastService $forecast): Response
    {
        $my_forecast = $forecast->showForecast();

        return $this->render('main/index.html.twig', [
            'forecast' => $my_forecast,
        ]);
    }

    /**
     * @Route("/reset", name="reset")
     */
    public function reset()
    {
        $reset = new ResetService();

        return $this->redirectToRoute('main');
    }

    /**
     * @Route("/forecast", name="forecast")
     */
    public function forecast(ForecastService $forecast): Response
    {
        $my_forecast = $forecast->showForecast();

        return $this->render('main/forecast.html.twig', [
            'forecast' => $my_forecast,
        ]);
    }
}
