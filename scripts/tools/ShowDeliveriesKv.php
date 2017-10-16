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

namespace oat\taoDeliveryKv\scripts\tools;


use oat\oatbox\extension\AbstractAction;
use oat\taoDelivery\model\delivery\DeliveryInterface;
use oat\taoDelivery\model\delivery\DeliveryServiceInterface;


class ShowDeliveriesKv extends AbstractAction
{
    public function __invoke($params)
    {
        $report = new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Deliveries in KV');
        /** @var DeliveryServiceInterface $deliveryService */
        $deliveryService = $this->getServiceManager()->get(DeliveryServiceInterface::SERVICE_ID);
        $deliveries = $deliveryService->getDeliveriesByAccess(DeliveryInterface::DELIVERY_GUEST_ACCESS);
        $report->add(\common_report_Report::createInfo( 'Found ' . count($deliveries) . ' deliveries'));
        foreach ($deliveries as $delivery) {
            try {
                $report->add(new \common_report_Report(\common_report_Report::TYPE_INFO, $delivery->getIdentifier() . ' ' . $delivery->getLabel()));
            } catch ( \Exception $e ) {
                $report->add(new \common_report_Report(\common_report_Report::TYPE_ERROR, 'Not found ' . $delivery->getIdentifier()));
            }
        }

        if (in_array('--drop', $params)) {
            foreach ($deliveries as $delivery) {
                $id = $delivery->getIdentifier();
                $delivery->delete();
                $report->add(new \common_report_Report(\common_report_Report::TYPE_INFO, 'Delivery deleted: '.$id));
            }
        }


        return $report;
    }
}
