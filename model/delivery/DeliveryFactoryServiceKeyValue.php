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


use oat\taoDelivery\model\delivery\DeliveryFactoryServiceInterface;
use oat\taoDelivery\model\delivery\DeliveryInterface;

class DeliveryFactoryServiceKeyValue implements DeliveryFactoryServiceInterface
{
    function create(\core_kernel_classes_Class $deliveryClass, \core_kernel_classes_Resource $test, $label = '', DeliveryInterface $delivery = null)
    {

    }
}