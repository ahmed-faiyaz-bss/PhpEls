#[php]
@php
    $phpSocket = "unix:/run/alt-php{$version}-fpm/php-fpm.sock";
    if ($site->isIsolated()) {
        $phpSocket = "unix:/run/alt-php{$version}-fpm/php-fpm-{$site->user}.sock";
    }
@endphp
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
location ~ \.php$ {
    fastcgi_pass {{ $phpSocket }};
    fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
    include fastcgi_params;
    fastcgi_hide_header X-Powered-By;
}
index index.php index.html;
error_page 404 /index.php;
#[/php]
