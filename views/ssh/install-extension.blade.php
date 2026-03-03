if ! sudo DEBIAN_FRONTEND=noninteractive apt-get install -y alt-php{{ $version }}-{{ $name }}; then
    echo 'VITO_SSH_ERROR' && exit 1
fi

sudo systemctl restart alt-php{{ $version }}-fpm 2>/dev/null

/opt/alt/php{{ $version }}/usr/bin/php -m
