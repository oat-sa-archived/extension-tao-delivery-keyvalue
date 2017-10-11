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
        return self::DELIVERY_PREFIX . $param . $id;
    }

    public function getParameter($id, $param = '')
    {
        return $this->getPersistence()->get($this->getKey($id, $param));
    }

    public function setParameter($id, $param = '', $value)
    {
        $val = is_array($value) ? json_encode($value) : (string) $value;
        $this->getPersistence()->set($this->getKey($id, $param), $val);
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
}
