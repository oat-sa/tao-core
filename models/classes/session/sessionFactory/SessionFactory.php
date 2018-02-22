<?php

namespace oat\tao\model\session\sessionFactory;

use oat\oatbox\service\ConfigurableService;

class SessionFactory extends ConfigurableService
{
    const SERVICE_ID = 'tao/sessionFactory';

    const OPTION_ADAPTER = 'adapters';

    public function createSessionFromRequest($request, $resolver)
    {
        /** @var SessionBuilder $builder */
        foreach ($this->getAdapters() as $builder) {
            if ($builder->isApplicable($request, $resolver)) {
                return $builder->getSession($request);
            }
        }

        return new \common_session_AnonymousSession();
    }

    /**
     * @return SessionBuilder[]
     */
    protected function getAdapters()
    {
        $adapters = is_array($this->getOption(self::OPTION_ADAPTER)) ? $this->getOption(self::OPTION_ADAPTER) : [];
        foreach ($adapters as $key => $adapter) {
            if (is_a($adapter, SessionBuilder::class, true)) {
                throw new \LogicException('Session adapter must implement interface "SessionBuilder".');
            }
            $adapters[$key] = new $adapter();
        }
        return $adapters;
    }
}