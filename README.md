# Audio Calls Generator

Laravel and Livewire application for creating WAV audio files from reusable prompt templates with Google Gemini TTS.

## Stack

- PHP `^8.4` (local runtime: PHP 8.5)
- Laravel 13.8
- Livewire 4.3
- Blade
- Tailwind CSS 4
- Vite 8
- SCSS
- Pest 4
- Google Gemini API
- MySQL or SQLite

## Pages

| Page | Route | Purpose |
| --- | --- | --- |
| Audio generator | `/` | Select a saved prompt template, generate a WAV file, play it, download it, and reuse or remove previous generations. |
| Prompt templates | `/prompt-templates` | List all saved templates in a table with title, master prompt, prompt text, language, gender, voice, edit, and remove actions. |
| Create template | `/prompt-templates/create` | Create a reusable prompt template with title, language, voice gender, voice generator, master prompt, and prompt text. |
| Edit template | `/prompt-templates/{promptTemplate}/edit` | Update an existing prompt template. |
| WAV file playback | `/storage/audio/{fileName}` | Serves generated WAV files from `storage/app/public/audio` through a Laravel route. |

Local Herd URLs are:

```text
http://audio-calls-generator.test
http://audio-calls-generator.test/prompt-templates
```

Production domain:

```text
https://audio-calls-generator.prus.dev
```

## Feature Map

### Prompt Templates

- Full CRUD for reusable prompt templates.
- Each template stores:
  - title;
  - master prompt;
  - prompt text;
  - Gemini TTS language code, language name, and readiness;
  - voice gender;
  - voice generator name;
  - voice display label.
- The index page is a table, and the create/edit form is a separate page.
- The generator page only uses saved templates, so generation settings come from the selected template.
- Seed data creates Lithuanian starter templates for support greetings, billing reminders, and delivery updates.

### Audio Generation

- The main page requires a selected prompt template.
- Selecting a template loads its master prompt, prompt text, language, gender, and voice settings.
- Clicking `Generate audio` creates or updates a database draft, calls Gemini TTS, stores the WAV file, and marks the generation as generated or failed.
- The page shows:
  - selected template summary;
  - loading button state and `Creating WAV...` state;
  - success and error messages;
  - WAV player;
  - WAV download link;
  - previous prompt history.
- MP3 generation is intentionally removed. The app is WAV-only.

### Previous Prompts

- Previous prompts are created only when audio generation starts.
- Changing template fields does not create previous prompt history.
- Previous prompts can be reused in the generator.
- Previous prompts can be removed from the list.
- Removing a previous prompt also deletes the stored WAV file when one exists.
- Failed generations are stored with their error message so the user can see what happened.

### Gemini TTS

- Default model: `gemini-3.1-flash-tts-preview`.
- API credentials are read from `.env` through `config/services.php`.
- API logic lives in `App\Services\GeminiAudioService`.
- Gemini returns base64 PCM audio. The service wraps the PCM bytes in a WAV container and stores the final `.wav` file.
- Transport and API failures are converted into user-facing generation errors.
- Safe failure details are logged without exposing the API key.

### Voices

Voice selection is stored per prompt template.

Female voices:

```text
Achernar, Aoede, Autonoe, Callirrhoe, Despina, Erinome, Gacrux, Kore, Laomedeia, Leda, Pulcherrima, Sulafat, Vindemiatrix, Zephyr
```

Male voices:

```text
Achird, Algenib, Algieba, Alnilam, Charon, Enceladus, Fenrir, Iapetus, Orus, Puck, Rasalgethi, Sadachbia, Sadaltager, Schedar, Umbriel, Zubenelgenubi
```

The template form first selects gender, then refreshes the voice generator list for that gender.

### Languages

Prompt templates store the selected Gemini TTS language. The form groups supported languages by readiness:

- GA languages, such as English (United States), French (France), German (Germany), Spanish (Spain), and other stable options.
- Preview languages, such as Lithuanian, English (United Kingdom), Spanish (Mexico), Swedish, Urdu, and other preview options.

Language data lives in `App\Services\GeminiLanguageService`.

### Audio Storage

Generated WAV files are saved through Laravel Storage:

```php
Storage::disk('public')
```

Default storage path:

```text
storage/app/public/audio
```

Public route shape:

```text
/storage/audio/example.wav
```

The browser receives files through the named route `audio.files.show`, handled by `App\Livewire\AudioFile`.

## Database

Runtime data is stored in these application tables:

| Table | Purpose |
| --- | --- |
| `prompt_templates` | Reusable prompt templates with language and voice settings. |
| `audio_generations` | Prompt history, generation status, selected TTS metadata, WAV metadata, and errors. |
| `audio_voice_preferences` | Legacy current voice preference storage from the earlier generator flow. The current generator uses template-level voice settings. |
| `master_prompts` | Legacy single master prompt storage from the earlier generator flow. Master prompts now live on prompt templates. |

Laravel system tables include `users`, `sessions`, `cache`, `jobs`, `failed_jobs`, and `job_batches`.

## Architecture

The app keeps Livewire components thin and moves work into grouped actions and services.

Important files:

```text
app/Livewire/AudioGenerator.php
app/Livewire/AudioFile.php
app/Livewire/PromptTemplateIndex.php
app/Livewire/PromptTemplateFormPage.php
app/Livewire/Forms/PromptTemplateForm.php
app/Actions/AudioGenerations/
app/Actions/PromptTemplates/
app/Rules/AudioGenerations/
app/Rules/PromptTemplates/
app/Services/GeminiAudioService.php
app/Services/GeminiLanguageService.php
app/Services/GeminiVoiceService.php
app/Services/AudioGenerationHistoryService.php
app/Services/PromptTemplateService.php
resources/views/livewire/audio-generator.blade.php
resources/views/livewire/prompt-template-index.blade.php
resources/views/livewire/prompt-template-form-page.blade.php
resources/scss/app.scss
```

Quality gates are covered by tests for:

- prompt template pages;
- audio generation persistence;
- Gemini audio payload handling;
- voice and language metadata;
- storage file route access;
- shared-hosting public path;
- no Livewire Volt usage;
- dependency injection instead of service-locator helpers in app source.

## Shared Hosting Layout

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

- redirects production traffic to `https://audio-calls-generator.prus.dev`;
- blocks direct access to Laravel internals such as `app`, `bootstrap`, `config`, `database`, `resources`, `routes`, `tests`, and `vendor`;
- serves `/storage/...` URLs from `storage/app/public/...`;
- sends all application requests to root `index.php`.

No `public/storage` symlink is required for the default shared-hosting layout.

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=PromptTemplateSeeder --no-interaction
npm run build
```

For local frontend development:

```bash
npm run dev
```

Laravel Herd serves the project locally; no `php artisan serve` command is needed for normal Herd usage.

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
GEMINI_TTS_LANGUAGE=en-US
GEMINI_API_TIMEOUT=60
GEMINI_API_CONNECT_TIMEOUT=10
GEMINI_API_RETRIES=2
GEMINI_API_RETRY_SLEEP_MS=300
GEMINI_TTS_SAMPLE_RATE=24000
GEMINI_TTS_CHANNELS=1
GEMINI_TTS_SAMPLE_WIDTH=2
```

Never commit a real Gemini API key.

## Deployment

Recommended production commands:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan db:seed --class=PromptTemplateSeeder --force --no-interaction
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

- Vite legacy chunks and polyfills;
- PostCSS preset-env transforms;
- Autoprefixer;
- Browser targets for Chrome, Edge, Firefox, Safari, iOS Safari, Android Browser, and Samsung Internet.

Tailwind CSS 4 is modern-browser-first, so Internet Explorer is not supported.

## Useful Commands

```bash
php artisan test --compact
vendor/bin/pint --dirty --format agent
npm run build
composer validate --strict --no-interaction
php artisan route:list --except-vendor --no-interaction
php artisan db:seed --class=PromptTemplateSeeder --no-interaction
```

## Verification

Before deployment, run:

```bash
php artisan test --compact
npm run build
```

The shared-hosting tests verify that Laravel uses the project root as the public path, the legacy `public/` folder is not required, and Vite builds assets into root `build/`.
