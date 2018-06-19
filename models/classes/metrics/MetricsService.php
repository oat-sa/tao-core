<?php
/**
 *
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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\tao\model\metrics;


use oat\oatbox\service\ConfigurableService;
use oat\tao\model\metadata\exception\InconsistencyConfigException;
use oat\tao\model\metrics\implementations\abstractMetrics;

class MetricsService extends ConfigurableService
{

    const SERVICE_ID = 'tao/metrics';
    const OPTION_METRICS = 'metrics';
    private $metrics = [];


    /**
     * @return abstractMetrics[]
     */
    protected function getMetrics()
    {
        if (!$this->metrics) {
            $metrics = $this->getOption(self::OPTION_METRICS);
            foreach ($metrics as $metric) {
                $this->propagate($metric);
            }
            $this->metrics = $metrics;
        }
        return $this->metrics;
    }

    public function collect()
    {
        foreach ($this->getMetrics() as $metric) {
            $metric->collect();
        }
    }

    /**
     * @param $metricName
     * @return null|abstractMetrics
     * @throws InconsistencyConfigException
     */
    public function getOneMetric($metricName)
    {
        $metrics = $this->getMetrics();
        if (array_key_exists($metricName, $metrics)) {
            $result = $metrics[$metricName];
        } else {
            throw new InconsistencyConfigException('Attempt to access unknown metric detected' . $metricName);
        }

        return $result;
    }

}