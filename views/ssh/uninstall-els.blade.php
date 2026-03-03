if ! sudo DEBIAN_FRONTEND=noninteractive apt-get remove -y alt-php{{ $version }}; then
    echo 'VITO_SSH_ERROR' && exit 1
fi
