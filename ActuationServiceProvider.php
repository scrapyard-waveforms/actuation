<?php

namespace Waveforms\Actuation;

use Fabricate\Chassis\Exceptions\BindingResolutionException;
use Fabricate\Contracts\Core\Program;
use Fabricate\Core\Machine as ScrapyardIOMachine;
use Fabricate\NutsAndBolts\Contracts\DeferrableProvider;
use Fabricate\NutsAndBolts\ServiceProvider;
use Waveforms\Contracts\Actuation\ActuatorRegistry as RegistryContract;

class ActuationServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        $this->publishConfig();

        $this->container->singleton('actuator', fn (Program $program) => new ActuatorRegistry);
        $this->container->alias('actuator', ActuatorRegistry::class);
        $this->container->alias('actuator', RegistryContract::class);
    }

    public function boot(): void {}

    /**
     * @throws BindingResolutionException
     */
    protected function publishConfig(): void
    {
        $actuators = dirname(__DIR__, 3).'/config/actuators.php';
        $waveforms = dirname(__DIR__, 3).'/config/waveforms.php';

        if ($this->container instanceof ScrapyardIOMachine && $this->container->runningInConsole()) {
            $this->publishes(
                [$actuators => $this->container->configPath('actuators.php')],
                'waveforms-actuators-config',
            );
            $this->publishes(
                [$waveforms => $this->container->configPath('waveforms.php')],
                'waveforms-config',
            );
        }

        $this->mergeConfigFrom($actuators, 'actuators');
        $this->mergeConfigFrom($waveforms, 'waveforms');
    }

    /**
     * @return array<int, string>
     */
    public function provides(): array
    {
        return ['actuator'];
    }
}
