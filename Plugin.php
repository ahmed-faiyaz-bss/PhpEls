<?php

namespace App\Vito\Plugins\AhmedFaiyazBss\PhpEls;

use App\DTOs\DynamicField;
use App\DTOs\DynamicForm;
use App\Plugins\AbstractPlugin;
use App\Plugins\RegisterServiceType;
use App\Plugins\RegisterViews;

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
            ->form(DynamicForm::make([
                DynamicField::make('alert')
                    ->alert()
                    ->options(['type' => 'info'])
                    ->link('TuxCare PHP ELS Docs', 'https://docs.tuxcare.com/els-for-runtimes/php/')
                    ->description('You need a valid TuxCare license key to install PHP ELS.'),
                DynamicField::make('license_key')
                    ->text()
                    ->label('License Key')
                    ->placeholder('XXX-XXXXXXXXXXXX')
                    ->description('Your TuxCare PHP ELS license key'),
            ]))
            ->register();
    }
}
