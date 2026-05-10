# Audio Calls Generator

Laravel and Livewire application for generating WAV audio files from text with the Google Gemini API.

## Stack

- PHP `^8.4` (current local runtime: PHP 8.5)
- Laravel 13.8
- Livewire 4.3
- Blade
- Tailwind CSS 4
- Vite 8
- SCSS
- Pest 4
- Google Gemini API
- MySQL or SQLite

## Features

- Single master prompt stored in the database.
- Text-to-WAV generation through Gemini TTS.
- Voice gender and voice generator selection.
- Selected voice preference stored separately from prompt history.
- Previous prompts and generated audio metadata stored in the database.
- WAV playback and download on the page.
- Generated WAV files saved to `storage/app/public/audio`.
- Shared-hosting document root support without Laravel's `public/` folder.
- Vite production assets built into root `build/`.
- Minimal responsive Blade and Tailwind interface.

## Application Flow

The main page is available at `/`.

1. Save the master prompt. This becomes the reusable instruction for future audio ideas.
2. Choose `Voice gender`. The voice generator list refreshes to show only voices for that gender.
3. Choose `Voice generator`. The selected gender and generator are saved immediately as the current voice preference.
4. Enter the text that should become audio.
5. Generate audio. The app saves the prompt, sends the text and selected voice to Gemini, stores the WAV metadata, and shows the player and download link.

Changing the voice gender or voice generator does not create a previous prompt. A previous prompt is created only when audio generation starts.

## Project Layout For Shared Hosting

This project is organized for shared hosting where the subdomain document root is the project root `/`.

Required public-entry files live in the root:

```text
index.php
.htaccess
favicon.ico
robots.txt
build/
```

The Laravel `public/` folder is intentionally not used. The application sets Laravel's public path to the project root in `bootstrap/app.php`.

The root `.htaccess` file:

- redirects to `https://audio-calls-generator.prus.dev`;
- blocks direct access to Laravel internals such as `app`, `bootstrap`, `config`, `database`, `resources`, `routes`, `tests`, and `vendor`;
- serves `/storage/...` URLs from `storage/app/public/...`;
- sends all application requests to root `index.php`.

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm run build
```

For local frontend development:

```bash
npm run dev
```

Laravel Herd serves this project locally at:

```text
https://audio-calls-generator.test
```

## Environment

Minimum production values:

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

SESSION_DRIVER=database
SESSION_DOMAIN=audio-calls-generator.prus.dev
SESSION_SECURE_COOKIE=true

FILESYSTEM_DISK=public
PUBLIC_DISK_ROOT=storage/app/public
PUBLIC_DISK_URL="${APP_URL}/storage"
PUBLIC_STORAGE_LINK=

QUEUE_CONNECTION=database
CACHE_STORE=database

GEMINI_API_KEY=
GEMINI_API_BASE_URL=https://generativelanguage.googleapis.com/v1beta
GEMINI_TTS_MODEL=gemini-3.1-flash-tts-preview
GEMINI_TTS_VOICE=Kore
GEMINI_API_TIMEOUT=60
GEMINI_API_CONNECT_TIMEOUT=10
GEMINI_API_RETRIES=2
GEMINI_API_RETRY_SLEEP_MS=300
GEMINI_TTS_SAMPLE_RATE=24000
GEMINI_TTS_CHANNELS=1
GEMINI_TTS_SAMPLE_WIDTH=2
```

Never commit a real Gemini API key.

## Audio Storage

Generated WAV files are saved through:

```php
Storage::disk('public')
```

Default path:

```text
storage/app/public/audio
```

Public URL shape:

```text
https://audio-calls-generator.prus.dev/storage/audio/example.wav
```

No `public/storage` symlink is required for the default shared-hosting layout. The root `.htaccess` maps `/storage/...` requests to `storage/app/public/...`.

## Database Storage

The app stores runtime state in database tables:

- `master_prompts` stores the single reusable master prompt.
- `audio_voice_preferences` stores the current voice gender, generator name, and label.
- `audio_generations` stores prompt text, selected voice metadata, generation status, WAV metadata, and errors.

Changing `Voice gender` or `Voice generator` updates only `audio_voice_preferences`. It does not create a `Previous prompts` item.

## Deployment

Recommended build and deployment commands:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Upload the project so these files and folders are in the subdomain document root:

```text
index.php
.htaccess
app/
bootstrap/
build/
config/
database/
resources/
routes/
storage/
vendor/
```

Make sure these directories are writable by the hosting account:

```text
bootstrap/cache
storage
storage/app/public
```

If the shared host does not allow running Composer or npm, build locally and upload `vendor/` and `build/` with the rest of the project.

## Browser Support

The frontend is built from SCSS and Tailwind through Vite.

The build includes:

- Vite legacy chunks and polyfills.
- PostCSS preset-env transforms.
- Autoprefixer.
- Browser targets for Chrome, Edge, Firefox, Safari, iOS Safari, Android Browser, and Samsung Internet.

Tailwind CSS 4 is modern-browser-first, so Internet Explorer is not supported.

## Useful Commands

```bash
php artisan test --compact
vendor/bin/pint --dirty --format agent
npm run build
composer validate --strict --no-interaction
```

## Verification

Before deployment, run:

```bash
php artisan test --compact
npm run build
```

The shared-hosting tests verify that Laravel uses the project root as the public path, the legacy `public/` folder is not required, and Vite builds assets into root `build/`.

## Main Files

- `index.php`
- `.htaccess`
- `bootstrap/app.php`
- `app/Livewire/AudioGenerator.php`
- `app/Services/GeminiAudioService.php`
- `app/Services/GeminiVoiceService.php`
- `app/Services/AudioGenerationHistoryService.php`
- `app/Services/AudioVoicePreferenceService.php`
- `app/Services/MasterPromptService.php`
- `resources/views/livewire/audio-generator.blade.php`
- `resources/scss/app.scss`
- `config/services.php`
- `config/filesystems.php`
- `database/migrations/`
