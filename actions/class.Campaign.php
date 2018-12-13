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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               2012-2018 (original work) Open Assessment Technologies SA;
 *
 */

use oat\tao\model\TaoOntology;

/**
 * Campaign Controller provide actions performed from url resolution
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @package taoCampaign

 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 */

class taoCampaign_actions_Campaign extends tao_actions_SaSModule
{
    protected function getClassService()
    {
        if (is_null($this->service)) {
            $this->service = taoCampaign_models_classes_CampaignService::singleton();
        }
        return $this->service;
    }

	/**
	 * Render json data to populate the campaign tree
	 * 'modelType' must be in the request parameters
	 */
	public function getCampaigns()
    {
		if(!$this->isXmlHttpRequest()){
			throw new Exception("wrong request mode");
		}

		$options = array(
			'instances' => true,
			'chunk' => false
		);

		if($this->hasRequestParameter('classUri')){
			$clazz = $this->getCurrentClass();
			$options['chunk'] = true;
		}
		else{
			$clazz = $this->getClassService()->getRootClass();
		}

		$this->returnJson( $this->getClassService()->toTree($clazz , $options));
	}

	/**
	 * Edit a delviery instance
	 * @return void
	 */
	public function editCampaign()
    {
        $this->defaultData();

		$clazz = $this->getCurrentClass();

		$campaign = $this->getCurrentInstance();

		$formContainer = new tao_actions_form_Instance($clazz, $campaign);
		$myForm = $formContainer->getForm();

		if($myForm->isSubmited()){
			if($myForm->isValid()){

				$binder = new tao_models_classes_dataBinding_GenerisFormDataBinder($campaign);
				$campaign = $binder->bind($myForm->getValues());

		        $this->setData("selectNode", tao_helpers_Uri::encode($campaign->getUri()));
				$this->setData('message', __('Campaign saved'));
				$this->setData('reload', true);
			}
		}

		//get the deliveries related to this delivery campaign
		$prop = $this->getProperty(TAO_DELIVERY_CAMPAIGN_PROP);
		$tree = tao_helpers_form_GenerisTreeForm::buildReverseTree($campaign, $prop);
		$this->setData('deliveryTree', $tree->render());

		$this->setData('formTitle', __('Edit Campaign'));
		$this->setData('myForm', $myForm->render());
		$this->setView('form_campaign.tpl');
	}

	/**
	 * Add a campaign instance
	 * @return void
	 */
	public function addCampaign()
    {
		if(!$this->isXmlHttpRequest()){
			throw new Exception("wrong request mode");
		}
		$clazz = $this->getCurrentClass();
		$campaign = $this->getClassService()->createInstance($clazz, $this->getClassService()->createUniqueLabel($clazz));
		if(!is_null($campaign) && $campaign instanceof core_kernel_classes_Resource){
			$this->returnJson(array(
				'label'	=> $campaign->getLabel(),
				'uri' 	=> tao_helpers_Uri::encode($campaign->getUri())
			));
		}
	}

	/**
	 * Add a campaign subclass
	 * @return void
	 */
	public function addCampaignClass()
    {
		if(!$this->isXmlHttpRequest()){
			throw new Exception("wrong request mode");
		}
		$clazz = $this->getClassService()->createCampaignClass($this->getCurrentClass());
		if(!is_null($clazz) && $clazz instanceof core_kernel_classes_Class){
			$this->returnJson(array(
				'label'	=> $clazz->getLabel(),
				'uri' 	=> tao_helpers_Uri::encode($clazz->getUri())
			));
		}
	}

	/**
	 * Delete a campaign or a campaign class
	 * @return void
	 */
	public function delete()
    {
		if(!$this->isXmlHttpRequest()){
			throw new Exception("wrong request mode");
		}

		if($this->getRequestParameter('uri')){
			$deleted = $this->getClassService()->deleteCampaign($this->getCurrentInstance());
		}
		else{
			$deleted = $this->getClassService()->deleteCampaignClass($this->getCurrentClass());
		}

		$this->returnJson(array('deleted'	=> $deleted));
	}

	/**
	 * Duplicate a campaign instance
	 * @return void
	 */
	public function cloneCampaign()
    {
		if(!$this->isXmlHttpRequest()){
			throw new Exception("wrong request mode");
		}

		$campaign = $this->getCurrentInstance();
		$clazz = $this->getCurrentClass();

		$clone = $this->getClassService()->createInstance($clazz);
		if(!is_null($clone)){

			foreach($clazz->getProperties() as $property){
				foreach($campaign->getPropertyValues($property) as $propertyValue){
					$clone->setPropertyValue($property, $propertyValue);
				}
			}
			$clone->setLabel($campaign->getLabel()."'");
			$this->returnJson(array(
				'label'	=> $clone->getLabel(),
				'uri' 	=> tao_helpers_Uri::encode($clone->getUri())
			));
		}
	}

	/**
	 * Get the data to populate the tree of deliveries
	 * @return void
	 */
	public function getDeliveries()
    {
		if(!$this->isXmlHttpRequest()){
			throw new Exception("wrong request mode");
		}
		$options = array('chunk' => false);
		if($this->hasRequestParameter('classUri')) {
			$clazz = $this->getCurrentClass();
			$options['chunk'] = true;
		}
		else{
			$clazz = $this->getClass(TaoOntology::CLASS_URI_DELIVERY);
		}
		if($this->hasRequestParameter('selected')){
			$selected = $this->getRequestParameter('selected');
			if(!is_array($selected)){
				$selected = array($selected);
			}
			$options['browse'] = $selected;
		}
		$this->returnJson($this->getClassService()->toTree($clazz, $options));
	}
}
