if ! sudo sed -i 's/alt-php{{ $oldVersion }}/alt-php{{ $newVersion }}/g' {{ $configPath }}; then
    echo 'VITO_SSH_ERROR' && exit 1
fi

if ! sudo service {{ $webservice }} restart; then
    echo 'VITO_SSH_ERROR' && exit 1
fi

echo "PHP ELS Version Changed to {{ $newVersion }}"
