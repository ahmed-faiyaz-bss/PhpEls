<?php

namespace App\Vito\Plugins\AhmedFaiyazBss\PhpEls;

use App\Exceptions\SSHCommandError;
use App\Services\AbstractService;
use Illuminate\Support\Str;
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
        return 'alt-php'.$this->versionNumber().'-fpm';
    }

    public function creationRules(array $input): array
    {
        return [
            'version' => [
                'required',
                Rule::in(config('service.services.php-els.versions')),
                Rule::unique('services', 'version')
                    ->where('type', 'php-els')
                    ->where('server_id', $this->service->server_id),
            ],
        ];
    }

    public function install(): void
    {
        $server = $this->service->server;

        $server->ssh()->exec(
            view('php-els::ssh.install-els', [
                'version' => $this->versionNumber(),
                'user' => $server->getSshUser(),
            ]),
            'install-php-els-'.$this->service->version
        );

        $server->os()->cleanup();
    }

    public function uninstall(): void
    {
        $server = $this->service->server;

        $server->ssh()->exec(
            view('php-els::ssh.uninstall-els', [
                'version' => $this->versionNumber(),
            ]),
            'uninstall-php-els-'.$this->service->version
        );

        $server->os()->cleanup();
    }

    /**
     * @throws SSHCommandError
     */
    public function installExtension(string $name): void
    {
        $version = $this->versionNumber();

        $result = $this->service->server->ssh()->exec(
            view('php-els::ssh.install-extension', [
                'version' => $version,
                'name' => $name,
            ]),
            'install-php-els-extension-'.$name
        );

        $pos = strpos($result, '[PHP Modules]');
        if ($pos === false) {
            throw new SSHCommandError('Failed to install extension');
        }
        $result = Str::substr($result, $pos);
        if (! Str::contains($result, $name)) {
            throw new SSHCommandError('Failed to install extension');
        }
    }

    public function version(): string
    {
        $version = $this->versionNumber();

        $result = $this->service->server->ssh()->exec(
            '/opt/alt/php'.$version.'/usr/bin/php -r \'echo PHP_VERSION;\' 2>/dev/null'
        );

        if (preg_match('/(\d+\.\d+\.\d+)/', $result, $matches)) {
            return $matches[1];
        }

        return $this->service->version;
    }

    /**
     * Get the version number without dots (e.g., "7.3" → "73").
     */
    public function versionNumber(): string
    {
        return str_replace('.', '', $this->service->version);
    }
}
