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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 * 
 */
namespace oat\taoDeliveryKv\model;


use oat\oatbox\user\User;
use oat\taoDelivery\model\Assignment;
use oat\taoDelivery\model\delivery\DeliveryInterface;
use oat\taoDelivery\model\execution\ServiceProxy;
use tao_helpers_Date;

/**
 * todo move to the taoDelivery extension
 * Class AssignmentFactory
 * @package oat\taoDeliveryKv\model
 */
class AssignmentFactory
{
    /**
     * @var DeliveryInterface
     */
    protected $delivery;

    /**
     * @var User
     */
    private $user;
    
    private $startable;
    
    public function __construct(DeliveryInterface $delivery, User $user, $startable)
    {
        $this->delivery = $delivery;
        $this->user = $user;
        $this->startable = $startable;
    }
    
    public function getDeliveryId()
    {
        return $this->delivery->getIdentifier();
    }
    
    protected function getUserId()
    {
        return $this->user->getIdentifier();
    }
    
    protected function getLabel()
    {
        return $this->delivery->getLabel();    
    }
    
    protected function getDescription()
    {
        $startTime = $this->delivery->getPeriodStart();
        $endTime = $this->delivery->getPeriodEnd();
        $maxExecs = $this->delivery->getMaxExec();

        // todo all resources should be replaced by DeliveryInterface object
        // but for the quick test we have to mock that
        $resource = new \core_kernel_classes_Resource($this->delivery->getIdentifier());
        $countExecs = count(ServiceProxy::singleton()->getUserExecutions($resource, $this->getUserId()));
        
        return $this->buildDescriptionFromData($startTime, $endTime, $countExecs, $maxExecs);
    }
    
    protected function getStartable()
    {
        return $this->startable;
    }
    
    public function getStartTime()
    {
        return $this->delivery->getPeriodStart();
    }
    
    public function getDeliveryOrder()
    {
        return $this->delivery->getDeliveryOrder();
    }
    
    protected function buildDescriptionFromData($startTime, $endTime, $countExecs, $maxExecs)
    {
        $descriptions = array();
        if (!empty($startTime) && !empty($endTime)) {
            $descriptions[] = __('Available from %1$s to %2$s',
                tao_helpers_Date::displayeDate($startTime)
                ,tao_helpers_Date::displayeDate($endTime)
            );
        } elseif (!empty($startTime) && empty($endTime)) {
            $descriptions[] = __('Available from %s', tao_helpers_Date::displayeDate($startTime));
        } elseif (!empty($endTime)) {
            $descriptions[] = __('Available until %s', tao_helpers_Date::displayeDate($endTime));
        }
         
        if ($maxExecs !== 0) {
            if ($maxExecs === 1) {
                $descriptions[] = __('Attempt %1$s of %2$s'
                    ,$countExecs
                    ,!empty($maxExecs)
                    ? $maxExecs
                    : __('unlimited'));
            } else {
                $descriptions[] = __('Attempts %1$s of %2$s'
                    ,$countExecs
                    ,!empty($maxExecs)
                    ? $maxExecs
                    : __('unlimited'));
        
            }
        }
        return $descriptions;
    }
    
    public function toAssignment()
    {
        return new Assignment(
            $this->getDeliveryId(),
            $this->getUserId(),
            $this->getLabel(),
            $this->getDescription(),
            $this->getStartable()
            // $this->getDeliveryOrder()
        );
    }
    
    public function __equals(AssignmentFactory $factory)
    {
        return $this->getDeliveryId() == $factory->getDeliveryId();
    }
}
