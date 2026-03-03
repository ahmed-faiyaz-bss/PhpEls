<?php

namespace App\Vito\Plugins\AhmedFaiyazBss\PhpEls;

use App\Plugins\AbstractPlugin;
use App\Plugins\RegisterServerFeature;
use App\Plugins\RegisterServerFeatureAction;
use App\Plugins\RegisterServiceType;
use App\Plugins\RegisterViews;
use App\Vito\Plugins\Tuxcare\PhpEls\Actions\InstallExtension;
use App\Vito\Plugins\Tuxcare\PhpEls\Actions\SetupRepository;

class Plugin extends AbstractPlugin
{
    protected string $name = 'PHP ELS';

    protected string $description = 'TuxCare Extended Lifecycle Support for PHP - install security-patched EOL PHP versions.';

    public function boot(): void
    {
        RegisterViews::make('php-els')
            ->path(__DIR__.'/views')
            ->register();

        RegisterServiceType::make(PhpEls::id())
            ->type(PhpEls::type())
            ->label('PHP ELS')
            ->handler(PhpEls::class)
            ->versions([
                '8.3',
                '8.2',
                '8.1',
                '8.0',
                '7.4',
                '7.3',
                '7.2',
                '7.1',
                '7.0',
                '5.6',
            ])
            ->register();

        RegisterServerFeature::make('php-els')
            ->label('PHP ELS')
            ->description('TuxCare Extended Lifecycle Support for PHP')
            ->register();

        RegisterServerFeatureAction::make('php-els', 'setup-repo')
            ->label('Setup Repository')
            ->handler(SetupRepository::class)
            ->register();

        RegisterServerFeatureAction::make('php-els', 'install-extension')
            ->label('Install Extension')
            ->handler(InstallExtension::class)
            ->register();
    }
}
