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
 * Copyright (c) 2019 (original work) Open Assessment Technologies SA;
 */

namespace oat\tao\scripts\tools;

use oat\oatbox\extension\AbstractAction;
use oat\tao\model\webhooks\WebhookEventsService;
use oat\taoDelivery\model\execution\OntologyService;
use oat\taoProctoring\model\event\DeliveryExecutionFinished;

class TestWebhook extends AbstractAction
{
    public function __invoke($params)
    {
        /** @var OntologyService $ontologyService */
        $ontologyService = $this->getServiceLocator()->get(OntologyService::class);
        $de = $ontologyService->getDeliveryExecution('https://nccersso.taocloud.org/borodanccer.rdf#i155559271531299');
        $event = new DeliveryExecutionFinished($de);
        /** @var WebhookEventsService $whService */
        $whService = $this->getServiceLocator()->get(WebhookEventsService::SERVICE_ID);
        $whService->handleEvent($event);
    }
}
