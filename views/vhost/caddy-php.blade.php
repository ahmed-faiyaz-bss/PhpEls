#[php]
root * {{ $site->getWebDirectoryPath() }}
@php
    $phpSocket = "unix//run/alt-php{{ $version }}-fpm/php-fpm.sock";
    if ($site->isIsolated()) {
        $phpSocket = "unix//run/alt-php{{ $version }}-fpm/php-fpm-{$site->user}.sock";
    }
@endphp
try_files {path} {path}/ /index.php?{query}
php_fastcgi {{ $phpSocket }}
file_server
#[/php]
