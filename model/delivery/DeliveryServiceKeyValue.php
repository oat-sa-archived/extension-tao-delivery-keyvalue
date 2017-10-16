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
 * Copyright (c) 2017  (original work) Open Assessment Technologies SA;
 *
 * @author Alexander Zagovorichev <zagovorichev@1pt.com>
 */

namespace oat\taoDeliveryKv\model\delivery;


use oat\taoDelivery\model\delivery\AbstractDeliveryService;
use oat\taoDelivery\model\delivery\Delivery;
use oat\taoDelivery\model\delivery\DeliveryInterface;

class DeliveryServiceKeyValue extends AbstractDeliveryService
{
    const DELIVERY_PREFIX = 'tao:delivery:';

    /**
     * @return \common_persistence_Persistence|\common_persistence_KeyValuePersistence
     */
    protected function getPersistence()
    {
        if (is_null($this->persistence)) {
            $persistenceOption = $this->getOption(self::OPTION_PERSISTENCE);
            $this->persistence = (is_object($persistenceOption))
                ? $persistenceOption
                : \common_persistence_KeyValuePersistence::getPersistence($persistenceOption);
        }

        return $this->persistence;
    }

    private function getKey($id, $param = '')
    {
        return self::DELIVERY_PREFIX . $id . $param;
    }

    private function getAccessKey($access)
    {
        return self::DELIVERY_PREFIX . 'access:' . $access;
    }

    public function getParameterValue($id, $param = '')
    {
        return $this->getPersistence()->get($this->getKey($id, $param));
    }

    public function deliveryExists($id)
    {
        return $this->parameterExists($id, RDFS_LABEL);
    }

    public function parameterExists($id, $param)
    {
        $val = $this->getPersistence()->get($this->getKey($id, $param));
        return $val !== false;
    }

    public function setParameter($id, $param = '', $value)
    {
        $val = is_array($value) ? json_encode($value) : (string) $value;
        $this->getPersistence()->set($this->getKey($id, $param), $val);
    }

    public function deleteParameter($id, $param = '')
    {
        $this->getPersistence()->del($this->getKey($id, $param));
    }

    public function setParameters($id, array $params)
    {
        foreach ($params as $key => $value) {
            $this->setParameter($id, $key, $value);
        }
    }

    public function createDelivery(\core_kernel_classes_Class $deliveryClass, $label = '')
    {
        $uri = \common_Utils::getNewUri();
        $key = $this->getKey($uri);
        $delivery = new Delivery($key, $this);
        $delivery->setLabel($label);
        return $delivery;
    }

    public function setAccessSettings($id, array $val)
    {

        // set Delivery access settings
        $this->getPersistence()->set($this->getKey($id, DeliveryInterface::PROPERTY_ACCESS_SETTINGS), json_encode($val));
        $this->updateDeliveryInAccessGroups($id, $val);
    }

    private function updateDeliveryInAccessGroups($id, $deliveryAccessTypes)
    {
        // add new access
        $accessGroups = array_merge($this->getAccessGroups(), $deliveryAccessTypes);
        $this->setAccessGroups($accessGroups);

        foreach ($accessGroups as $accessGroup) {
            $deliveries = $this->getAccessDeliveries($accessGroup);
            if (in_array($accessGroup, $deliveryAccessTypes) && !in_array($id, $deliveries)) {
                $deliveries[] = $id;
            } elseif (!in_array($accessGroup, $deliveryAccessTypes) && in_array($id, $deliveries)) {
                unset($deliveries[$id]);
            }
            $this->setAccessDeliveries($accessGroup, $deliveries);
        }
    }

    private function getAccessDeliveries($group = '')
    {
        $deliveries = $this->getPersistence()->get($this->getAccessKey($group));
        if ($deliveries) {
            $deliveries = json_decode($deliveries);
        } else {
            $deliveries = [];
        }
        return $deliveries;
    }

    private function setAccessDeliveries($group = '', array $values = [])
    {
        if (count($values)) {
            $this->getPersistence()->set($this->getAccessKey($group), json_encode($values));
        } else {
            $this->getPersistence()->del($this->getAccessKey($group));
        }
    }

    private function setAccessGroups(array $groups)
    {
        if (count($groups)) {
            $this->getPersistence()->set($this->getAccessKey('list'), json_encode($groups));
        } else {
            $this->getPersistence()->del($this->getAccessKey('list'));
        }
    }

    private function getAccessGroups()
    {
        $accessTypes = $this->getPersistence()->get($this->getAccessKey('list'));
        if ($accessTypes) {
            $accessTypes = json_decode($accessTypes);
        } else {
            $accessTypes = [];
        }
        return $accessTypes;
    }

    public function getDeliveriesByAccess($access = '')
    {
        $deliveries = [];
        $deliveriesIds = $this->getPersistence()->get($this->getAccessKey($access));
        if ($deliveriesIds) {
            $deliveriesIds = json_decode($deliveriesIds);
            foreach ($deliveriesIds as $id) {
                $deliveries[] = new Delivery($id, $this);
            }
        }
        return $deliveries;
    }

    public function delete($id)
    {
        // delete params
        parent::delete($id);

        // delete access groups
        $groups = $this->getAccessGroups();
        foreach ($groups as $group) {
            $this->setAccessDeliveries($group, []);
        }
        $this->setAccessGroups([]);
    }
}
