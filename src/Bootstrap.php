<?php
/**
 * PHP 7.0 compatible
 */
declare (strict_types=1);

/**
 * This class manages all bootstrap operations for the application.
 */

namespace Maleficarum\Proxy;

class Bootstrap {
    /* ------------------------------------ Class Constant START --------------------------------------- */

    const INITIALIZER_ERRORS = ['Maleficarum\Proxy\Basic\Initializer', 'setUpErrorHandling'];
    const INITIALIZER_SECURITY = ['Maleficarum\Proxy\Basic\Initializer', 'setUpSecurity'];
    const INITIALIZER_ROUTES = ['Maleficarum\Proxy\Basic\Initializer', 'setUpRoutes'];
    const INITIALIZER_CONTROLLER = ['Maleficarum\Proxy\Basic\Initializer', 'setUpController'];
    const INITIALIZER_DEBUG_LEVEL = ['Maleficarum\Proxy\Basic\Initializer', 'setUpDebugLevel'];

    /* ------------------------------------ Class Constant END ----------------------------------------- */

    /* ------------------------------------ Class Property START --------------------------------------- */

    /**
     * Internal storage for Proxy component initializers to run during bootstrap execution.
     *
     * @var array
     */
    private $initializers = [];

    /**
     * Internal storage for bootstrap initializer param container.
     *
     * @var array
     */
    private $paramContainer = [];

    /* ------------------------------------ Class Property END ----------------------------------------- */

    /**
     * Run all defined bootstrap initializers.
     * @return Bootstrap
     */
    public function initialize(): \Maleficarum\Proxy\Bootstrap {
        // register bootstrap as dependency for use in initializer steps
        \Maleficarum\Ioc\Container::registerShare('Maleficarum\Bootstrap', $this);

        // validate and execute initializers
        foreach ($this->getInitializers() as $key => $initializer) {
            if (!is_callable($initializer)) {
                throw new \LogicException(sprintf('Invalid initializer passed to the bootstrap initialization process. \%s::\%s()', static::class, __METHOD__));
            }
            $init_name = $initializer($this->getParamContainer());

            try {
                \Maleficarum\Ioc\Container::retrieveShare('Maleficarum\Profiler\Time')->addMilestone('initializer_' . $key, 'Initializer executed (' . $init_name . ').');
            } catch (\RuntimeException $e) {
            }
        }

        return $this;
    }

    /**
     * Perform any final maintenance actions. This will be called at the end of a request.
     *
     * @return \Maleficarum\Proxy\Bootstrap
     */
    public function conclude(): \Maleficarum\Proxy\Bootstrap {
        // complete profiling
        try {
            \Maleficarum\Ioc\Container::retrieveShare('Maleficarum\Profiler\Time')->end();
        } catch (\RuntimeException $e) {
        }

        // output any response data
        try {
            \Maleficarum\Ioc\Container::retrieveShare('Maleficarum\Response')->output();
        } catch (\RuntimeException $e) {
        }

        return $this;
    }

    /* ------------------------------------ Setters & Getters START ------------------------------------ */

    /**
     * @return array
     */
    protected function getInitializers(): array {
        return $this->initializers;
    }

    /**
     * @param array $initializers
     *
     * @return \Maleficarum\Proxy\Bootstrap
     */
    public function setInitializers(array $initializers): \Maleficarum\Proxy\Bootstrap {
        $this->initializers = $initializers;

        return $this;
    }

    /**
     * @return array
     */
    public function getParamContainer(): array {
        return $this->paramContainer;
    }

    /**
     * @param array $paramContainer
     *
     * @return \Maleficarum\Proxy\Bootstrap
     */
    public function setParamContainer(array $paramContainer = []): \Maleficarum\Proxy\Bootstrap {
        $this->paramContainer = $paramContainer;

        return $this;
    }

    /* ------------------------------------ Setters & Getters END -------------------------------------- */
}
