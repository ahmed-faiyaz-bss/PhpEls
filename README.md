# PHP ELS Plugin for Vito

A [Vito](https://github.com/vitodeploy/vito) plugin that integrates [TuxCare Extended Lifecycle Support (ELS) for PHP](https://docs.tuxcare.com/els-for-runtimes/php/), allowing you to install and manage security-patched end-of-life PHP versions on your servers.

## What It Does

Standard PHP versions reach end-of-life and stop receiving security patches. TuxCare PHP ELS provides continued security updates for these EOL versions. This plugin brings that support directly into Vito, letting you:

- **Set up the TuxCare ELS repository** on your servers using your license key
- **Install PHP ELS versions** as managed services (5.6, 7.0 - 7.4, 8.0 - 8.3)
- **Install PHP extensions** for any installed ELS version (e.g. `mysqlnd`, `xml`, `gd`)
- **Create sites** that run on PHP ELS versions with full Nginx and Caddy support
- **Manage FPM pools** with isolated per-site configurations for security
- **Start, stop, restart** PHP ELS FPM services like any other Vito service

PHP ELS versions are installed under `/opt/alt/phpXY/` and run as `alt-phpXY-fpm` systemd services, completely separate from any system PHP installation.

## Supported PHP Versions

| Version | Package |
|---------|---------|
| 8.3 | `alt-php83` |
| 8.2 | `alt-php82` |
| 8.1 | `alt-php81` |
| 8.0 | `alt-php80` |
| 7.4 | `alt-php74` |
| 7.3 | `alt-php73` |
| 7.2 | `alt-php72` |
| 7.1 | `alt-php71` |
| 7.0 | `alt-php70` |
| 5.6 | `alt-php56` |

## Prerequisites

- A Vito instance with the plugin installed
- A TuxCare PHP ELS license key ([get one here](https://docs.tuxcare.com/els-for-runtimes/php/))
- A Debian/Ubuntu-based server managed by Vito

## Installation

Install the plugin from Vito's admin panel:

1. Go to **Admin > Plugins**
2. Click **Install from GitHub**
3. Enter the repository URL for this plugin
4. Enable the plugin after installation

## Usage (Web UI)

### 1. Set Up the TuxCare Repository

Before installing any PHP ELS version, you must register your license key:

1. Navigate to **Servers > {Your Server} > Features**
2. Find the **PHP ELS** feature
3. Click **Setup Repository**
4. Enter your TuxCare license key (format: `XXX-XXXXXXXXXXXX`)
5. Submit — this downloads and runs the official TuxCare repository installer

### 2. Install a PHP ELS Version

Once the repository is set up, install PHP ELS as a service:

1. Navigate to **Servers > {Your Server} > Services**
2. Select **PHP ELS** as the service type
3. Choose the desired version (e.g. `7.4`)
4. Click install — this installs the `alt-phpXY` package along with MySQL extensions (`alt-phpXY-mysql80`, `alt-phpXY-mysqlnd`), enables MySQL modules (`mysqlnd`, `mysqli`, `pdo_mysql`), creates a default FPM pool, and starts the `alt-phpXY-fpm` service

### 3. Install PHP Extensions

1. Navigate to **Servers > {Your Server} > Features**
2. Find the **PHP ELS** feature
3. Click **Install Extension**
4. Select the PHP ELS version and enter the extension name (e.g. `gd`, `xml`, `mysqlnd`)
5. Submit — installs `alt-phpXY-{extension}` and restarts FPM

### 4. Create a Site Using PHP ELS

1. Navigate to **Servers > {Your Server} > Sites**
2. Click **Create Site**
3. Select **PHP ELS Blank** as the site type
4. Choose the PHP ELS version (only installed versions are shown)
5. Optionally set a web directory (e.g. `public`)
6. Submit — this creates an isolated user, configures an FPM pool, and generates the web server vhost

### 5. Manage PHP ELS Services

Installed PHP ELS versions appear in **Servers > {Your Server} > Services** where you can:

- **Start** / **Stop** / **Restart** / **Reload** the FPM service
- **Uninstall** the PHP ELS version (removes the package and disables the service)

## Usage (API)

The plugin integrates with Vito's REST API. All API requests require a Sanctum token with the appropriate ability (`read` or `write`).

### Authentication

Include your API token in the `Authorization` header:

```
Authorization: Bearer {your-api-token}
```

### Install PHP ELS as a Service

```http
POST /api/projects/{project}/servers/{server}/services
Content-Type: application/json

{
  "type": "php-els",
  "version": "7.4"
}
```

### List Services (including PHP ELS)

```http
GET /api/projects/{project}/servers/{server}/services
```

PHP ELS services appear with `type: "php-els"` in the response.

### View a Specific Service

```http
GET /api/projects/{project}/servers/{server}/services/{service}
```

### Start / Stop / Restart / Reload a PHP ELS Service

```http
POST /api/projects/{project}/servers/{server}/services/{service}/start
POST /api/projects/{project}/servers/{server}/services/{service}/stop
POST /api/projects/{project}/servers/{server}/services/{service}/restart
POST /api/projects/{project}/servers/{server}/services/{service}/reload
```

All return `204 No Content` on success.

### Enable / Disable a PHP ELS Service

```http
POST /api/projects/{project}/servers/{server}/services/{service}/enable
POST /api/projects/{project}/servers/{server}/services/{service}/disable
```

### Uninstall a PHP ELS Service

```http
DELETE /api/projects/{project}/servers/{server}/services/{service}
```

This stops the FPM service, removes the `alt-phpXY` packages, and deletes the service record.

### Create a Site Using PHP ELS

```http
POST /api/projects/{project}/servers/{server}/sites
Content-Type: application/json

{
  "type": "php-els-blank",
  "domain": "example.com",
  "els_php_version": "7.4",
  "web_directory": "public"
}
```

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| `type` | string | Yes | Must be `php-els-blank` |
| `domain` | string | Yes | The site domain |
| `els_php_version` | string | Yes | An installed PHP ELS version (e.g. `7.4`) |
| `web_directory` | string | No | Relative path from `/home/vito/{domain}/` (e.g. `public`) |

### List / View / Delete Sites

```http
GET  /api/projects/{project}/servers/{server}/sites
GET  /api/projects/{project}/servers/{server}/sites/{site}
DELETE /api/projects/{project}/servers/{server}/sites/{site}
```

When a PHP ELS site is deleted, its isolated FPM pool configuration is automatically cleaned up.

## Web Server Support

The plugin generates vhost configurations for both **Nginx** and **Caddy**.

### Nginx

Requests are proxied to the PHP ELS FPM socket:

- Shared: `unix:/run/alt-phpXY-fpm/php-fpm.sock`
- Isolated: `unix:/run/alt-phpXY-fpm/php-fpm-{user}.sock`

### Caddy

Uses `php_fastcgi` with the same socket paths as Nginx.

## Site Isolation

When a site is created, the plugin:

1. Creates a dedicated OS user for the site
2. Generates an isolated FPM pool with `open_basedir` restrictions to `/home/{user}/`
3. Routes PHP requests through a per-user socket (`php-fpm-{user}.sock`)
4. Cleans up the FPM pool automatically when the site is deleted

## File Structure

```
PhpEls/
├── Plugin.php              # Plugin entry point — registers services, site types, features
├── PhpEls.php              # Service handler — install/uninstall, FPM pool management
├── PhpElsBlank.php         # Site type — creates sites running on PHP ELS
├── Actions/
│   ├── SetupRepository.php # Server feature action — registers TuxCare repo
│   └── InstallExtension.php# Server feature action — installs PHP extensions
└── views/
    ├── ssh/
    │   ├── setup-repo.blade.php         # Shell script: TuxCare repo setup
    │   ├── install-els.blade.php        # Shell script: install alt-phpXY
    │   ├── uninstall-els.blade.php      # Shell script: remove alt-phpXY
    │   ├── install-extension.blade.php  # Shell script: install extension package
    │   └── fpm-pool-isolated.blade.php  # FPM pool config for isolated sites
    └── vhost/
        ├── nginx-php.blade.php          # Nginx PHP location block
        └── caddy-php.blade.php          # Caddy PHP fastcgi block
```

## License

This plugin follows the same license as the Vito project (AGPL-3.0).
