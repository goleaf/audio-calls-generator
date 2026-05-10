<div class="audio-generator bg-white text-slate-950">
    <main class="audio-generator__shell">
        <div class="audio-generator__grid">
            <section class="audio-generator__main space-y-7 sm:space-y-8">
                @if ($errorMessage)
                    <p class="inline-flex items-start gap-2 text-sm text-red-600" wire:transition>
                        <x-icon name="circle-alert" class="mt-0.5" />
                        <span>{{ $errorMessage }}</span>
                    </p>
                @endif

                @if ($successMessage)
                    <p class="inline-flex items-start gap-2 text-sm text-emerald-700" wire:transition>
                        <x-icon name="check-circle" class="mt-0.5" />
                        <span>{{ $successMessage }}</span>
                    </p>
                @endif

                <form wire:submit="generate" class="space-y-4">
                    <div class="space-y-2">
                        <label for="selectedPromptTemplateId" class="inline-flex items-center gap-2 text-sm font-medium text-slate-900">
                            <x-icon name="notebook-pen" class="size-4 text-slate-500" />
                            <span>Prompt template</span>
                        </label>

                        <select
                            id="selectedPromptTemplateId"
                            wire:model="selectedPromptTemplateId"
                            wire:change="usePromptTemplate($event.target.value)"
                            class="min-h-11 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                        >
                            <option value="">Select a saved template</option>
                            @foreach ($promptTemplates as $template)
                                <option value="{{ $template['id'] }}">
                                    {{ $this->languageFlag($template['language_code']) }} {{ $template['title'] }}
                                </option>
                            @endforeach
                        </select>

                        @error('selectedPromptTemplateId')
                            <p class="text-sm text-red-600" wire:transition>{{ $message }}</p>
                        @enderror

                        @if ($promptTemplates === [])
                            <p class="text-sm text-slate-500">
                                Create templates on the
                                <x-button
                                    as="a"
                                    href="{{ route('audio.prompt-templates') }}"
                                    wire:navigate
                                    size="sm"
                                    class="!w-auto"
                                >
                                    <x-icon name="notebook-pen" class="size-3.5" />
                                    <span>Prompt templates</span>
                                </x-button>
                                page.
                            </p>
                        @endif
                    </div>

                    @if ($selectedTemplate)
                        <section class="space-y-3 border-y border-slate-200 py-4" wire:transition>
                            <div class="grid gap-3 sm:grid-cols-3">
                                <div class="space-y-1">
                                    <p class="inline-flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                        <x-icon name="notebook-pen" class="size-3.5" />
                                        <span>Template</span>
                                    </p>
                                    <p class="break-words text-sm font-medium text-slate-900">{{ $selectedTemplate['title'] }}</p>
                                </div>

                                <div class="space-y-1">
                                    <p class="inline-flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                        <x-icon name="mic" class="size-3.5" />
                                        <span>Voice</span>
                                    </p>
                                    <p class="break-words text-sm font-medium text-slate-900">{{ $selectedTemplate['tts_voice_label'] }}</p>
                                </div>

                                <div class="space-y-1">
                                    <p class="inline-flex items-center gap-1.5 text-xs font-medium uppercase tracking-wide text-slate-500">
                                        <x-icon name="languages" class="size-3.5" />
                                        <span>Language</span>
                                    </p>
                                    <p class="inline-flex items-center gap-1.5 break-words text-sm font-medium text-slate-900">
                                        @if ($selectedTemplate['language_code'] !== null && $this->languageFlag($selectedTemplate['language_code']) !== '')
                                            <span class="leading-none" role="img" aria-label="Language flag">{{ $this->languageFlag($selectedTemplate['language_code']) }}</span>
                                        @endif
                                        <span>{{ $selectedTemplate['language_label'] }}</span>
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-3">
                                <div class="space-y-2">
                                    <div class="audio-generator__label-row">
                                        <label for="masterPrompt" class="inline-flex items-center gap-2 text-sm font-medium text-slate-900">
                                            <x-icon name="message-square-text" class="size-4 text-slate-500" />
                                            <span>Master prompt</span>
                                        </label>
                                        <span class="text-xs text-slate-500">{{ mb_strlen($masterPrompt) }} / 2000</span>
                                    </div>

                                    <textarea
                                        id="masterPrompt"
                                        wire:model="masterPrompt"
                                        rows="4"
                                        maxlength="2000"
                                        class="w-full resize-y rounded-md border border-slate-300 px-3 py-2 text-sm leading-6 text-slate-900 outline-none placeholder:text-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                                    ></textarea>

                                    @error('masterPrompt')
                                        <p class="text-sm text-red-600" wire:transition>{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="space-y-2">
                                    <div class="audio-generator__label-row">
                                        <label for="promptText" class="inline-flex items-center gap-2 text-sm font-medium text-slate-900">
                                            <x-icon name="file-text" class="size-4 text-slate-500" />
                                            <span>Prompt text</span>
                                        </label>
                                        <span class="text-xs text-slate-500">{{ mb_strlen($text) }} / 5000</span>
                                    </div>

                                    <textarea
                                        id="promptText"
                                        wire:model="text"
                                        rows="8"
                                        maxlength="5000"
                                        class="w-full resize-y rounded-md border border-slate-300 px-3 py-2 text-sm leading-6 text-slate-900 outline-none placeholder:text-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                                    ></textarea>

                                    @error('text')
                                        <p class="text-sm text-red-600" wire:transition>{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </section>
                    @endif

                    <div class="audio-generator__actions">
                <x-button
                    type="submit"
                    size="lg"
                    wire:loading.attr="disabled"
                    wire:target="generate"
                >
                    <span wire:loading.remove wire:target="generate" class="audio-generator__button-content inline-flex items-center gap-2">
                        <x-icon name="audio-lines" />
                        <span>Generate audio</span>
                    </span>
                    <span wire:loading.flex wire:target="generate" class="audio-generator__button-content items-center gap-2">
                        <span class="audio-generator__button-spinner" aria-hidden="true"></span>
                        <span>Generating WAV</span>
                    </span>
                </x-button>

                        <div wire:loading.flex wire:target="generate" class="audio-generator__loading-card items-center gap-3 text-sm text-slate-600">
                            <span class="audio-generator__loading-icon" aria-hidden="true">
                                <x-icon name="audio-lines" class="size-3.5" />
                            </span>
                            <span class="font-medium text-slate-700">Creating WAV...</span>
                        </div>
                    </div>
                </form>

                @if ($wavUrl)
                    <section class="space-y-2" wire:transition>
                        <div class="audio-generator__label-row">
                            <h2 class="inline-flex items-center gap-2 text-sm font-medium text-slate-900">
                                <x-icon name="volume" class="text-slate-500" />
                                <span>WAV</span>
                            </h2>
                            <x-button
                                as="a"
                                href="{{ $wavUrl }}?download=1"
                                download
                                size="sm"
                                class="!w-auto"
                            >
                                <x-icon name="download" />
                                <span>Download WAV</span>
                            </x-button>
                        </div>

                        <audio controls class="w-full">
                            <source src="{{ $wavUrl }}" type="audio/wav">
                        </audio>
                    </section>
                @endif
            </section>

            <aside class="audio-generator__history space-y-3">
                <h2 class="inline-flex items-center gap-2 text-sm font-medium text-slate-900">
                    <x-icon name="history" class="text-slate-500" />
                    <span>Previous prompts</span>
                </h2>

                @if ($savedGenerations === [])
                    <p class="border-y border-slate-200 py-3 text-sm text-slate-500">No prompts yet.</p>
                @else
                    <div class="divide-y divide-slate-200 border-y border-slate-200">
                        @foreach ($savedGenerations as $generation)
                            <div wire:key="saved-generation-{{ $generation['id'] }}" class="space-y-3 py-3">
                                <div class="flex flex-col gap-2 min-[30rem]:flex-row min-[30rem]:items-center min-[30rem]:justify-between">
                                    <span class="text-xs text-slate-500">{{ str_replace('_', ' ', $generation['status']) }}</span>
                                    <div class="audio-generator__history-actions flex items-center gap-3">
                                        <x-button
                                            type="button"
                                            size="sm"
                                            wire:click="usePrompt({{ $generation['id'] }})"
                                        >
                                            <x-icon name="play" class="size-3.5" />
                                            <span>Use</span>
                                        </x-button>

                                        <x-button
                                            type="button"
                                            size="sm"
                                            wire:click="removePrompt({{ $generation['id'] }})"
                                            wire:loading.attr="disabled"
                                            wire:target="removePrompt({{ $generation['id'] }})"
                                        >
                                            <x-icon name="trash" class="size-3.5" />
                                            <span>Remove</span>
                                        </x-button>
                                    </div>
                                </div>

                                @if ($generation['master_prompt'])
                                    <p class="line-clamp-3 break-words text-xs leading-5 text-slate-500">{{ $generation['master_prompt'] }}</p>
                                @endif

                                @if ($generation['audio_file_name'])
                                    <p class="inline-flex items-center gap-1.5 break-words text-xs text-slate-500">
                                        <x-icon name="file-text" class="size-3.5 shrink-0" />
                                        <span>{{ $generation['audio_file_name'] }}</span>
                                    </p>
                                @endif

                                @if ($generation['tts_voice_label'])
                                    <p class="inline-flex items-center gap-1.5 break-words text-xs text-slate-500">
                                        <x-icon name="mic" class="size-3.5 shrink-0" />
                                        <span>{{ $generation['tts_voice_label'] }}</span>
                                    </p>
                                @endif

                                @if ($generation['tts_language_label'])
                                    <p class="inline-flex items-center gap-1.5 break-words text-xs text-slate-500">
                                        @if (($generation['tts_language_flag'] ?? '') !== '')
                                            <span class="leading-none" role="img" aria-label="Language flag">{{ $generation['tts_language_flag'] }}</span>
                                        @endif
                                        <x-icon name="languages" class="size-3.5 shrink-0" />
                                        <span>{{ $generation['tts_language_label'] }}</span>
                                    </p>
                                @endif

                                @if ($generation['error_message'])
                                    <p class="break-words text-xs text-red-600">{{ $generation['error_message'] }}</p>
                                @endif

                                @if ($generation['audio_url'])
                                    <div class="space-y-2">
                                        <audio controls preload="metadata" class="w-full">
                                            <source src="{{ $generation['audio_url'] }}" type="audio/wav">
                                        </audio>

                                        <x-button
                                            as="a"
                                            href="{{ $generation['audio_url'] }}?download=1"
                                            download
                                            size="sm"
                                            class="!w-auto"
                                        >
                                            <x-icon name="download" class="size-3.5" />
                                            <span>Download</span>
                                        </x-button>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </aside>
        </div>
    </main>
</div>
