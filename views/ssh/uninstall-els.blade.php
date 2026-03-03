sudo systemctl stop alt-php{{ $version }}-fpm 2>/dev/null
sudo systemctl disable alt-php{{ $version }}-fpm 2>/dev/null

if ! sudo DEBIAN_FRONTEND=noninteractive apt-get remove -y alt-php{{ $version }}; then
    echo 'VITO_SSH_ERROR' && exit 1
fi
