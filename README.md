# Audio Calls Generator

Laravel and Livewire application for generating WAV audio files from text with the Google Gemini API.

## Stack

- PHP 8.4+
- Laravel 13
- Livewire 4
- Blade
- Tailwind CSS 4
- Vite 8
- SCSS
- Google Gemini API
- MySQL or SQLite

## Features

- Master prompt storage.
- Text-to-WAV generation through Gemini TTS.
- Voice gender and voice generator selection.
- Saved prompt and generation history.
- WAV playback and download from the page.
- Public audio storage through Laravel Storage.
- Shared-hosting ready public filesystem configuration.
- SCSS build pipeline with PostCSS browser compatibility transforms.
- Responsive interface from small mobile screens to desktop.

## Requirements

- PHP 8.4 or newer
- Composer
- Node.js and npm
- A Gemini API key
- Writable `storage` and `bootstrap/cache` directories

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

For local development:

```bash
npm run dev
```

Laravel Herd serves this project at:

```text
https://audio-calls-generator.test
```

## Environment

Minimum required values:

```env
APP_NAME="Audio Calls Generator"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://audio-calls-generator.prus.dev
ASSET_URL="${APP_URL}"

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

FILESYSTEM_DISK=public
PUBLIC_DISK_ROOT=public/storage
PUBLIC_DISK_URL="${APP_URL}/storage"
PUBLIC_STORAGE_LINK=public/storage

SESSION_DRIVER=database
SESSION_DOMAIN=audio-calls-generator.prus.dev
SESSION_SECURE_COOKIE=true

QUEUE_CONNECTION=database

GEMINI_API_KEY=
GEMINI_API_BASE_URL=https://generativelanguage.googleapis.com/v1beta
GEMINI_TTS_MODEL=gemini-3.1-flash-tts-preview
GEMINI_TTS_VOICE=Kore
```

Never commit a real Gemini API key.

## Audio Storage

Generated WAV files are saved through:

```php
Storage::disk('public')
```

Default local path:

```text
storage/app/public/audio
```

Shared-hosting path:

```text
public/storage/audio
```

If the hosting account supports symlinks, the normal Laravel command can be used:

```bash
php artisan storage:link
```

If symlinks are not available, set:

```env
PUBLIC_DISK_ROOT=public/storage
PUBLIC_STORAGE_LINK=public/storage
```

This writes public WAV files directly into the web-accessible storage directory.

## Shared Hosting

The production subdomain is:

```text
https://audio-calls-generator.prus.dev
```

The Apache configuration in `public/.htaccess` redirects requests to the canonical HTTPS subdomain.

Recommended deployment steps:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Make sure these directories are writable by the hosting account:

```text
bootstrap/cache
storage
public/storage
```

## Browser Support

The frontend is built from SCSS and Tailwind through Vite.

The build includes:

- Vite legacy chunks and polyfills.
- PostCSS preset-env transforms.
- Autoprefixer.
- Browser targets for Chrome, Edge, Firefox, Safari, iOS Safari, Android Browser, and Samsung Internet.
- Responsive layout checks for small mobile, tablet, laptop, and desktop widths.

Tailwind CSS 4 is modern-browser-first, so very old browsers such as Internet Explorer are not supported. The current build targets broad practical support without downgrading Tailwind.

## Useful Commands

```bash
php artisan test --compact
vendor/bin/pint --dirty --format agent
npm run build
composer validate --strict --no-interaction
```

## Main Files

- `app/Livewire/AudioGenerator.php`
- `app/Services/GeminiAudioService.php`
- `app/Services/AudioGenerationHistoryService.php`
- `app/Services/GeminiVoiceService.php`
- `app/Services/MasterPromptService.php`
- `resources/views/livewire/audio-generator.blade.php`
- `resources/scss/app.scss`
- `config/services.php`
- `config/filesystems.php`
