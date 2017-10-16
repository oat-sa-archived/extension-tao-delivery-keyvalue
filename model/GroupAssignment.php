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

namespace oat\taoDeliveryKv\model;


use oat\oatbox\service\ConfigurableService;
use oat\oatbox\user\User;
use oat\taoDelivery\model\AssignmentService;
use oat\taoDelivery\model\delivery\DeliveryInterface;
use oat\taoDelivery\model\delivery\DeliveryServiceInterface;
use oat\taoDeliveryRdf\model\guest\GuestTestUser;

class GroupAssignment extends ConfigurableService implements AssignmentService
{
    public function getAssignments(User $user)
    {
        $assignments = array();
        foreach ($this->getAssignmentFactories($user) as $factory) {
            $assignments[] = $factory->toAssignment();
        }

        return $assignments;
    }

    public function getAssignmentFactories(User $user)
    {
        $assignments = array();

        //$assignmentFactory = new AssignmentFactory();
        if ($user instanceof GuestTestUser) {
            foreach ($this->getServiceManager()->get(DeliveryServiceInterface::SERVICE_ID)->getDeliveriesByAccess(DeliveryInterface::DELIVERY_GUEST_ACCESS) as $delivery) {
                $startable = $this->verifyTime($delivery);
                $assignments[] = new AssignmentFactory($delivery, $user, $startable);
            }
        } /*else {

             * todo as a quick solution will be used only guest access to compare performance with rdf
             * foreach ($this->deliveryService->getDeliveriesByUser($user) as $delivery) {
                $startable = $this->verifyTime($delivery) && $this->verifyToken($delivery, $user);
                $assignments[] = new AssignmentFactory($delivery, $user, $startable);
            }
        }*/
        return $assignments;
    }

    public function getRuntime($deliveryId)
    {
        // TODO: Implement getRuntime() method.
    }

    public function isDeliveryExecutionAllowed($deliveryIdentifier, User $user)
    {
        return true;
    }

    public function getAssignedUsers($deliveryId)
    {
        // TODO: Implement getAssignedUsers() method.
    }

    private function verifyTime(DeliveryInterface $delivery)
    {

        $startDate  =    date_create('@'.$delivery->getPeriodStart());
        $endDate    =    date_create('@'.$delivery->getPeriodEnd());

        if (!$this->areWeInRange($startDate, $endDate)) {
            \common_Logger::d("Attempt to start the compiled delivery ".$delivery->getIdentifier(). " at the wrong date");
            return false;
        }
        return true;
    }

    private function areWeInRange($startDate, $endDate)
    {
        return (empty($startDate) || date_create() >= $startDate)
        && (empty($endDate) || date_create() <= $endDate);
    }

}
