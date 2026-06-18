<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $domains = glob(app_path('Domains/*'), GLOB_ONLYDIR);
        foreach ($domains as $domain) {
            $contracts = glob($domain . '/Repositories/Contracts/*Interface.php');
            foreach ($contracts as $contractPath) {
                $contractName = basename($contractPath, '.php');
                $implementationName = str_replace('Interface', '', $contractName);
                $domainName = basename($domain);
                $contractClass = "App\\Domains\\{$domainName}\\Repositories\\Contracts\\{$contractName}";
                $implementationClass = "App\\Domains\\{$domainName}\\Repositories\\Eloquent\\{$implementationName}";
                
                if (class_exists($implementationClass) && interface_exists($contractClass)) {
                    $this->app->bind($contractClass, $implementationClass);
                }
            }
        }

        // Manually bind the AI Gateway Interface
        $this->app->bind(
            \App\Domains\AiGateway\Integrations\Contracts\AiGatewayInterface::class,
            \App\Domains\AiGateway\Integrations\OpenAIService::class
        );

        // Manually bind the Mangopay Gateway Interface
        $this->app->bind(
            \App\Domains\Payments\Integrations\Contracts\MangopayGatewayInterface::class,
            \App\Domains\Payments\Integrations\MangopayService::class
        );

        // Manually bind ReadModels
        $this->app->bind(
            \App\Domains\Marketplace\ReadModels\Contracts\DemandFeedReadModelInterface::class,
            \App\Domains\Marketplace\ReadModels\Redis\RedisDemandFeedReadModel::class
        );
    }

    public function boot(): void
    {
        \Illuminate\Database\Eloquent\Factories\Factory::guessFactoryNamesUsing(function (string $modelName) {
            $class = class_basename($modelName);
            return "Database\\Factories\\{$class}Factory";
        });

        \App\Domains\Marketplace\Entities\Demand::observe(\App\Domains\Marketplace\Projectors\DemandStateProjector::class);
    }
}
