<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class HolidayControllerTest extends WebTestCase
{
    protected static $translation;

    public static function setUpBeforeClass() {
        $kernel = static::createKernel();
        $kernel->boot();
        self::$translation = $kernel->getContainer()->get('translator');
    }
    
    public function testIndex()
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }
    
    public function testSubmitForm()
    {
        $client = static::createClient();
        $formData = ['holiday' => ['year' => 2020, 'country' => 'ago']];
        $crawler = $client->request('POST', '/', $formData);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains(self::$translation->trans('Holidays List'), $crawler->html());
        $this->assertContains(self::$translation->trans('Total holidays'), $crawler->html());
        $this->assertContains(self::$translation->trans('maxFreeDays'), $crawler->html());
        $this->assertContains(self::$translation->trans('current day'), $crawler->html());
    }
    
    public function testFailForm()
    {
        $client = static::createClient();
        $formData = ['holiday' => ['year' => 2, 'country' => 'ago']];
        $crawler = $client->request('POST', '/', $formData);
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $this->assertContains(self::$translation->trans('try again'), $crawler->html());
        $formData = ['holiday' => ['year' => 3002, 'country' => 'ago']];
        $crawler = $client->request('POST', '/', $formData);
        $this->assertContains(self::$translation->trans('future date'), $crawler->html());
    }
}