<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\HolidaysService;
use App\Form\Type\HolidayType;

/**
 * Class HolidayController
 * @package AppBundle\Controller
 */
class HolidayController extends AbstractController 
{    
    /**
     *
     * @Route("/", name="index")
     *
     * @param Request             $request
     * @param HolidaysService     $holidaysService
     * @param TranslatorInterface $translator
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request, HolidaysService $holidaysService, TranslatorInterface $translator)
    {   
        $holidaysInfo = [];
        $error = $maxFreeDays = $currentDayStatus = null;
        $countries = $holidaysService->getCountrylist();
        $form = $this->createForm(HolidayType::class, null, [
            'countries' => $countries,
            'method' => 'POST',
        ]);   
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            $holidayArr = $request->request->get('holiday');

            if ($holidayArr && $holidayArr['year'] && $holidayArr['country']) {
                if ($holidayArr['year'] < 3000) {
                    $holidaysInfo = $holidaysService->getHolidaysInfo($holidayArr);
                    $currentDayStatus = $holidaysService->getCurrentDayStatus($holidayArr['country']);

                    if (array_key_exists('error', $holidaysInfo)) {
                        $error = $holidaysInfo['error'];
                        $holidaysInfo = [];
                    } else {
                        $maxFreeDays = $holidaysService->getMaxFreeDays($holidaysInfo);
                    }
                } else {
                    $error = $translator->trans('future date');
                }
            }
        }

        return $this->render('holiday.html.twig', [
            'countries' => $countries, 
            'form' => $form->createView(),
            'holidaysInfo' => $holidaysInfo,
            'maxFreeDays' => $maxFreeDays,
            'currentDayStatus' => $currentDayStatus,
            'error' => $error
        ]);
    }
   
}
