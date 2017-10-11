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
use oat\taoDeliveryRdf\model\DeliveryAssemblyService;

/**
 * Move all deliveries to KV from RDF storage
 *
 * php index.php "oat\taoDeliveryKv\scripts\tools\MigrateDeliveryToKv"
 *
 * Class MigrateDeliveryToKv
 * @package oat\taoDeliveryKv\scripts\tools
 */
class MigrateDeliveryToKv extends AbstractAction
{
    /**
     * @var \core_kernel_classes_Class
     */
    private $deliveryClass;

    public function __invoke($params)
    {
        // Load needed constants
        \common_ext_ExtensionsManager::singleton()->getExtensionById('taoDelivery');
        $extensionManager = $this->getServiceManager()->get(\common_ext_ExtensionsManager::SERVICE_ID);
        if (!$extensionManager->isInstalled('taoDeliveryRdf')) {
            return new \common_report_Report(\common_report_Report::TYPE_ERROR, 'Extension taoDeliveryRdf should be installed to move deliveries between storages');
        }
        $extensionManager->getExtensionById('taoDeliveryRdf');
        $report = new \common_report_Report(\common_report_Report::TYPE_INFO);
        $noDryRun = in_array('--no-dry-run', $params);

        // get All RDF deliveries
        $deliveryService = DeliveryAssemblyService::singleton();
        $this->deliveryClass = $deliveryService->getRootClass();

        $cnt = $this->deliveryClass->countInstances();
        if (!$noDryRun) {
            $report->add(new \common_report_Report(\common_report_Report::TYPE_INFO, $cnt . ' delivery(ies) will be migrated'));
        } else {
            $deliveries = $this->deliveryClass->getInstances(true);
            foreach ($deliveries as $delivery) {
                $this->migrateDelivery($delivery);
            }
        }

        return $report;
    }

    public function migrateDelivery(\core_kernel_classes_Resource $delivery)
    {
        // create delivery with that ID


        // I need to move all deliveries properties just with predicate key from statements

        // move runtime
        // move compilation time
        // move assembled delivery container
        // move token
        // move label
        // move custom label
        // move maxexec
        // move period start
        // move period end
        // move delivery result server
        // move display order
        // move delivery ods version (but it exists only for the act)
        // move proctor accessible

        // move origin
        // move directories

        var_dump($delivery);die;
    }
}
