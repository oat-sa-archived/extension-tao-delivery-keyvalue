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
                $report->add($this->migrateDelivery($delivery));
            }
        }

        return $report;
    }

    public function migrateDelivery(\core_kernel_classes_Resource $delivery)
    {
        /** @var DeliveryServiceInterface $deliveryService */
        $deliveryService = $this->getServiceManager()->get(DeliveryServiceInterface::SERVICE_ID);

        $report = new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Move delivery ' . $delivery->getUri());

        // create delivery with that ID
        $kvDelivery = $deliveryService->createDelivery($delivery->getClass($delivery->getUri()), $delivery->getLabel());
        $report->add(\common_report_Report::createInfo('Delivery created: '. $kvDelivery->getLabel() . ' ' . $kvDelivery->getIdentifier()));

        // I need to move all deliveries properties just with predicate key from statements
        $props = $delivery->getPropertiesValues($deliveryService->getAllParams());

        // move runtime
        if ($this->paramExists($props, DeliveryInterface::PROPERTY_ASSEMBLED_DELIVERY_RUNTIME)) {
            $kvDelivery->setCompilationRuntime($props[DeliveryInterface::PROPERTY_ASSEMBLED_DELIVERY_RUNTIME][0]->getUri());
            $report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Runtime imported '.$kvDelivery->getCompilationRuntime()));
        }
        // move compilation time
        if ($this->paramExists($props, DeliveryInterface::PROPERTY_ASSEMBLED_DELIVERY_TIME)) {
            $kvDelivery->setCompilationDate($props[DeliveryInterface::PROPERTY_ASSEMBLED_DELIVERY_TIME][0]->literal);
            $report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Compilation date imported '.$kvDelivery->getCompilationRuntime()));
        }
        // move assembled delivery container //todo
        /*if ($this->paramExists($props, DeliveryInterface::)) {
            $kvDelivery->setCompilationRuntime($props[DeliveryInterface::ASSEMBLED_DELIVERY_TIME][0]->literal);
        }*/
        // move token todo
        // move custom label todo
        // move maxexec
        if ($this->paramExists($props, DeliveryInterface::PROPERTY_MAX_EXEC)) {
            $kvDelivery->setMaxExec($props[DeliveryInterface::PROPERTY_MAX_EXEC][0]->literal);
            $report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'MaxExec imported '.$kvDelivery->getCompilationRuntime()));
        }
        // move period start
        if ($this->paramExists($props, DeliveryInterface::PROPERTY_PERIOD_START)) {
            $kvDelivery->setPeriodStart($props[DeliveryInterface::PROPERTY_PERIOD_START][0]->literal);
            $report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Period Start imported '.$kvDelivery->getCompilationRuntime()));
        }
        // move period end
        if ($this->paramExists($props, DeliveryInterface::PROPERTY_PERIOD_END)) {
            $kvDelivery->setPeriodEnd($props[DeliveryInterface::PROPERTY_PERIOD_END][0]->literal);
            $report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Period End imported '.$kvDelivery->getCompilationRuntime()));
        }
        // move delivery result server
        if ($this->paramExists($props, DeliveryInterface::PROPERTY_RESULT_SERVER)) {
            $kvDelivery->setResultServer($props[DeliveryInterface::PROPERTY_RESULT_SERVER][0]->getUri());
            $report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Result server imported '.$kvDelivery->getCompilationRuntime()));
        }
        // move display order
        if ($this->paramExists($props, DeliveryInterface::PROPERTY_DISPLAY_ORDER)) {
            $kvDelivery->setDeliveryOrder($props[DeliveryInterface::PROPERTY_DISPLAY_ORDER][0]->literal);
            $report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Order imported '.$kvDelivery->getCompilationRuntime()));
        }
        // move access
        if ($this->paramExists($props, DeliveryInterface::PROPERTY_ACCESS_SETTINGS)) {
            $settings = $props[DeliveryInterface::PROPERTY_ACCESS_SETTINGS];
            $access = [];
            foreach ($settings as $resource) {
                $access[] = $resource->getUri();
            }

            $kvDelivery->setAccessSettings($access);
            $report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Access settings imported '.$kvDelivery->getCompilationRuntime()));
        }

        // move delivery ods version (but it exists only for the act) // todo

        // move origin
        if ($this->paramExists($props, DeliveryInterface::PROPERTY_ASSEMBLED_DELIVERY_ORIGIN)) {
            $kvDelivery->setDeliveryAssembledOrigin($props[DeliveryInterface::PROPERTY_ASSEMBLED_DELIVERY_ORIGIN][0]->getUri());
            $report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Origin delivery imported '.$kvDelivery->getDeliveryAssembledOrigin()));
        }

        // container
        if ($this->paramExists($props, DeliveryInterface::PROPERTY_ASSEMBLED_DELIVERY_CONTAINER)) {
            $kvDelivery->setAssembledContainer($props[DeliveryInterface::PROPERTY_ASSEMBLED_DELIVERY_CONTAINER][0]->literal);
            $report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Delivery container imported '.$kvDelivery->getAssembledContainer()));
        }

        // move directories
        if ($this->paramExists($props, DeliveryInterface::PROPERTY_ASSEMBLED_DELIVERY_DIRECTORY)) {
            $dirs = $props[DeliveryInterface::PROPERTY_ASSEMBLED_DELIVERY_DIRECTORY];
            $new_rec = [];
            foreach ($dirs as $src) {
                $s = '';
                if ($src instanceof \core_kernel_classes_Resource) {
                    $s = $src->getUri();
                } elseif ($src instanceof \core_kernel_classes_Literal) {
                    $s = $src->literal;
                }
                $new_rec[] = $s;
            }

            $kvDelivery->setCompilationDirectory($new_rec);
            $report->add(new \common_report_Report(\common_report_Report::TYPE_SUCCESS, 'Compilation directory imported '.$kvDelivery->getCompilationRuntime()));
        }

        return $report;
    }

    private function paramExists($props, $param) {
        return isset($props[$param]) && count($props[$param]);
    }
}
