<?php

namespace App\Vito\Plugins\Tuxcare\PhpEls;

use App\Services\AbstractService;
use Illuminate\Validation\Rule;

class PhpEls extends AbstractService
{
    public static function id(): string
    {
        return 'php-els';
    }

    public static function type(): string
    {
        return 'php-els';
    }

    public function unit(): string
    {
        return '';
    }

    public function creationRules(array $input): array
    {
        return [
            'license_key' => [
                'required',
                'string',
            ],
            'version' => [
                'required',
                Rule::in(config('service.services.php-els.versions')),
                Rule::unique('services', 'version')
                    ->where('type', 'php-els')
                    ->where('server_id', $this->service->server_id),
            ],
        ];
    }

    public function creationData(array $input): array
    {
        return [
            'license_key' => $input['license_key'],
        ];
    }

    public function install(): void
    {
        $server = $this->service->server;
        $version = str_replace('.', '', $this->service->version);

        $server->ssh()->exec(
            view('php-els::ssh.install-els', [
                'licenseKey' => $this->service->type_data['license_key'],
                'version' => $version,
            ]),
            'install-php-els-'.$this->service->version
        );

        $server->os()->cleanup();
    }

    public function uninstall(): void
    {
        $server = $this->service->server;
        $version = str_replace('.', '', $this->service->version);

        $server->ssh()->exec(
            view('php-els::ssh.uninstall-els', [
                'version' => $version,
            ]),
            'uninstall-php-els-'.$this->service->version
        );

        $server->os()->cleanup();
    }

    public function version(): string
    {
        $version = str_replace('.', '', $this->service->version);

        $result = $this->service->server->ssh()->exec(
            '/opt/alt/php'.$version.'/usr/bin/php -r \'echo PHP_VERSION;\' 2>/dev/null'
        );

        if (preg_match('/(\d+\.\d+\.\d+)/', $result, $matches)) {
            return $matches[1];
        }

        return $this->service->version;
    }
}
