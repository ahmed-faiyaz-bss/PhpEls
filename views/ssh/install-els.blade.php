if ! sudo DEBIAN_FRONTEND=noninteractive apt-get install -y alt-php{{ $version }}; then
    echo 'VITO_SSH_ERROR' && exit 1
fi

sudo systemctl enable alt-php{{ $version }}-fpm
sudo systemctl start alt-php{{ $version }}-fpm
