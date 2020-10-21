<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Entity\Holiday;

class HolidaysService
{
    /**
     * @var HttpClientInterface
     */
    private $client;
    
    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(HttpClientInterface $client, EntityManagerInterface $entityManager)
    {
        $this->client = $client;
        $this->em = $entityManager;
    }
    
    /**
     * getting list of searchable countries from API or from cache
     *
     * @return array
     */
    public function getCountrylist()
    {
        $cachePool = new FilesystemAdapter('', 0, "cache");
        
        if ($cachePool->hasItem('countries')) {
            $countries = $cachePool->getItem('countries')->get();
        } else {
            $countries = $this->client->request(
            'GET',
            'https://kayaposoft.com/enrico/json/v2.0?action=getSupportedCountries'
            )->toArray();
            $countriesCache = $cachePool->getItem('countries');
            
            if (!$countriesCache->isHit()) {
                $countriesCache->set($countries);
                $countriesCache->expiresAfter(60*60*24);
                $cachePool->save($countriesCache);
            }
        }

        return $countries;
    }
    
    /**
     * getting status of current day by country
     *
     * @return string
     */
    public function getCurrentDayStatus($country)
    {
        $isPublicHoliday = $this->client->request(
            'GET',
            'https://kayaposoft.com/enrico/json/v2.0?action=isPublicHoliday&date='.date("d-m-Y").'&country='.$country
            )->toArray()['isPublicHoliday'];
        $isWorkDay = $this->client->request(
            'GET',
            'https://kayaposoft.com/enrico/json/v2.0?action=isWorkDay&date='.date("d-m-Y").'&country='.$country
            )->toArray()['isWorkDay'];

        return $isPublicHoliday ? 'publicHoliday' : ($isWorkDay ? 'isWorkDay' : 'freeDay');
    }
    
    /**
     * creates holidays in db
     *
     * @param array  $requestHolidays
     * @param string $country
     *
     * @return array
     */
    public function createHolidays($requestHolidays, $country)
    {
        foreach ($requestHolidays as $holidayArr) {
            $holiday = new Holiday();
            $holiday->setCountry($country);
            $holiday->setName($holidayArr['name'][1]['text']);
            $holiday->setYear($holidayArr['date']['year']);
            $holiday
                ->setDate(new \DateTime($holidayArr['date']['year'].'/'.$holidayArr['date']['month'].'/'.$holidayArr['date']['day']));
            $this->em->persist($holiday);
            $holidays[$holidayArr['date']['month']][] = $holiday; 
        }
        
        $this->em->flush();

        return $holidays;
    }
    
    /**
     * gets list of holidays to display
     *
     * @param array $holidayArr
     *
     * @return array
     */
    public function getHolidaysInfo($holidayArr)
    {
        $holidays = [];
        $holidays = $this->em->getRepository(Holiday::class)
            ->findBy(['year' => $holidayArr['year'], 'country' => $holidayArr['country']]);
        
        if (!empty($holidays)) {
            foreach ($holidays as $holiday) {
                $holidaysRes[intval($holiday->getDate()->format('n'))][] = $holiday;
            }
        } else {
            $requestHolidays = $this->client->request(
                'GET',
                'https://kayaposoft.com/enrico/json/v2.0?action=getHolidaysForYear&year='
                .$holidayArr['year'].'&country='.$holidayArr['country']
                .'&holidayType=public_holiday'
            )->toArray();
            
            $holidaysRes = array_key_exists('error', $requestHolidays) ? $requestHolidays
                : $this->createHolidays($requestHolidays, $holidayArr['country']); 
        }

        return $holidaysRes;
    }
    
    /**
     * returns maximum free days in a row for current year and country
     *
     * @param array  $holidaysRes
     *
     * @return integer
     */
    public function getMaxFreeDays($holidaysRes)
    {
        $previousDate = null;
        $count = 1;
        $daysInRow = [];
        $startCount = true;
        
        foreach ($holidaysRes as $holidaysArr) {
            foreach ($holidaysArr as $holiday) {
                $currentDate = $holiday->getDate();
                
                if ($startCount) {
                    $startCount = false;
                    $previousDate = $currentDate;
                } else {
                    if ($previousDate->diff($currentDate)->days == 1) {
                        $count++;
                        $daysInRow[] = $count; // not in else below, in case last cycle it will not reach else
                    } else {
                        $count = 1;
                    }
                    
                    $previousDate = $currentDate;
                }
            }
        }

        return !empty($daysInRow) ? max($daysInRow) : 0;
    }
}