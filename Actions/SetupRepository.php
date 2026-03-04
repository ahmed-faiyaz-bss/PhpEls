<?php

namespace App\Vito\Plugins\Tuxcare\PhpEls\Actions;

use App\DTOs\DynamicField;
use App\DTOs\DynamicForm;
use App\ServerFeatures\Action;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SetupRepository extends Action
{
    public function name(): string
    {
        return 'Setup Repository';
    }

    public function active(): bool
    {
        return true;
    }

    public function form(): DynamicForm
    {
        if ($this->isRepoInstalled()) {
            return DynamicForm::make([
                DynamicField::make('alert')
                    ->alert()
                    ->options(['type' => 'success'])
                    ->description('PHP ELS repository is already set up on this server. You can install PHP ELS versions from the Services page.'),
                DynamicField::make('license_key')
                    ->text()
                    ->label('License Key')
                    ->placeholder('XXX-XXXXXXXXXXXX')
                    ->description('Re-enter your license key only if you need to re-setup the repository'),
            ]);
        }

        return DynamicForm::make([
            DynamicField::make('alert')
                ->alert()
                ->options(['type' => 'info'])
                ->link('TuxCare PHP ELS Docs', 'https://docs.tuxcare.com/els-for-runtimes/php/')
                ->description('Enter your TuxCare license key to set up the PHP ELS repository. This only needs to be done once per server.'),
            DynamicField::make('license_key')
                ->text()
                ->label('License Key')
                ->placeholder('XXX-XXXXXXXXXXXX')
                ->description('Your TuxCare PHP ELS license key'),
        ]);
    }

    public function handle(Request $request): void
    {
        Validator::make($request->all(), [
            'license_key' => ['required', 'string'],
        ])->validate();

        $this->server->ssh()->exec(
            view('php-els::ssh.setup-repo', [
                'licenseKey' => $request->input('license_key'),
            ]),
            'setup-php-els-repo'
        );

        $request->session()->flash('success', 'PHP ELS repository set up successfully!');
    }

    private function isRepoInstalled(): bool
    {
        try {
            $result = $this->server->ssh()->exec(
                'ls /etc/apt/sources.list.d/*alt-php*.list 2>/dev/null && echo "REPO_EXISTS" || echo "REPO_MISSING"'
            );

            return str_contains($result, 'REPO_EXISTS');
        } catch (\Throwable) {
            return false;
        }
    }
}
