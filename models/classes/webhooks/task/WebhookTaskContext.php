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

namespace oat\tao\model\webhooks\task;

use oat\tao\model\webhooks\configEntity\WebhookInterface;

class WebhookTaskContext
{
    /** @var string|null */
    private $taskId;

    /** @var WebhookTaskParams|null */
    private $webhookTaskParams;

    /** @var WebhookInterface|null */
    private $webhookConfig;

    /**
     * @return string|null
     */
    public function getTaskId()
    {
        return $this->taskId;
    }

    /**
     * @param string|null $taskId
     * @return $this
     */
    public function setTaskId($taskId)
    {
        $this->taskId = $taskId;
        return $this;
    }

    /**
     * @return WebhookTaskParams|null
     */
    public function getWebhookTaskParams()
    {
        return $this->webhookTaskParams;
    }

    /**
     * @param WebhookTaskParams|null $webhookTaskParams
     * @return $this
     */
    public function setWebhookTaskParams($webhookTaskParams)
    {
        $this->webhookTaskParams = $webhookTaskParams;
        return $this;
    }

    /**
     * @return WebhookInterface|null
     */
    public function getWebhookConfig()
    {
        return $this->webhookConfig;
    }

    /**
     * @param WebhookInterface|null $webhookConfig
     * @return $this
     */
    public function setWebhookConfig($webhookConfig)
    {
        $this->webhookConfig = $webhookConfig;
        return $this;
    }
}
