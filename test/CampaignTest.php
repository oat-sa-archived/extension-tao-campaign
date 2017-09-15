<?php
/**  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */

namespace oat\taoCampaign\test;

use oat\tao\model\TaoOntology;
use oat\tao\test\TaoPhpUnitTestRunner;
use \taoCampaign_models_classes_CampaignService;
use \core_kernel_classes_Class;
use \core_kernel_classes_Resource;

/**
 *
 * @author Patrick plichart, <patrick@taotesting.com>
 * @package taoResults
 *         
 */
class CampaignTestCase extends TaoPhpUnitTestRunner
{

    /**
     *
     * @var taoResults_models_classes_CampaignService
     */
    private $campaignService = null;

    /**
     *
     * @var the class used to store the tested campaigns and remvoed afterwards
     */
    public $campaignClass = null;

    /**
     *
     * @var the campaign being tested
     */
    private $campaign = null;

    /**
     *
     * @var core_kernel_classes_Class
     */
    private $delivery = null;

    /**
     * tests initialization
     */
    public function setUp()
    {
        TaoPhpUnitTestRunner::initTest();
        $this->campaignService = taoCampaign_models_classes_CampaignService::singleton();
        
        $rootClass = new core_kernel_classes_Class(TAO_DELIVERY_CAMPAIGN_CLASS);
        $this->campaignClass = $this->campaignService->createCampaignClass($rootClass, "My Campaign Class");
        
        $this->campaign = $this->campaignClass->createInstance("MyCampaign");
        
        $deliveryClass = new core_kernel_classes_Class(TaoOntology::DELIVERY_CLASS);
        $this->delivery = $deliveryClass->createInstance("MyDelivery");
        
        $this->campaignService->setRelatedDeliveries($this->campaign, array(
            $this->delivery->getUri()
        ));
    }

    /**
     * Test the campaign service implementation
     *
     * @see taoResults_models_classes_CampaignService::__construct
     */
    public function testService()
    {
        $this->assertInstanceOf('taoCampaign_models_classes_CampaignService', $this->campaignService);
    }

    public function testCreateCampaignClass()
    {
        $this->assertIsA($this->campaignClass, "core_kernel_classes_Class");
    }

    /**
     * used to create a campaign and test it further
     */
    public function testCreateCampaign()
    {
        $this->assertIsA($this->campaign, "core_kernel_classes_Resource");
    }

    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testGetRelatedDeliveries()
    {
        $deliveries = $this->campaignService->getRelatedDeliveries($this->campaign);
        $this->assertEquals(count($deliveries), 1);
        $delivery = new core_kernel_classes_Resource(array_pop($deliveries));
        $this->assertEquals($delivery->getLabel(), "MyDelivery");
    }

    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testIsCampaignClass()
    {
        $this->assertEquals($this->campaignService->isCampaignClass(new core_kernel_classes_Class(TAO_DELIVERY_CAMPAIGN_CLASS)), true);
        $this->assertEquals($this->campaignService->isCampaignClass($this->campaignClass), true);
    }

    /**
     *
     * @author Lionel Lecaque, lionel@taotesting.com
     */
    public function testgetRootClass()
    {
        $this->assertEquals($this->campaignService->getRootClass()
            ->getUri(), TAO_DELIVERY_CAMPAIGN_CLASS);
    }

    /**
     * (non-PHPdoc)
     * 
     * @see PHPUnit_Framework_TestCase::tearDown()
     */
    public function tearDown()
    {
        $this->assertTrue($this->campaignService->deleteCampaignClass($this->campaignClass));
        $this->assertTrue($this->campaignService->deleteCampaign($this->campaign));
        $this->assertTrue($this->delivery->delete());
    }
}