<div class="audio-generator bg-white text-slate-950">
    <main class="audio-generator__shell">
        <div class="audio-generator__grid">
            <section class="audio-generator__main space-y-7 sm:space-y-8">
                <form wire:submit="saveMasterPrompt" class="space-y-4">
                    <div class="space-y-2">
                        <div class="audio-generator__label-row">
                            <label for="masterPrompt" class="inline-flex items-center gap-2 text-sm font-medium text-slate-900">
                                <x-icon name="notebook-pen" class="size-4 text-slate-500" />
                                <span>Master prompt</span>
                            </label>
                            <span class="text-xs text-slate-500">{{ mb_strlen($masterPrompt) }} / 2000</span>
                        </div>

                        <textarea
                            id="masterPrompt"
                            wire:model="masterPrompt"
                            rows="5"
                            maxlength="2000"
                            class="w-full resize-y rounded-md border border-slate-300 px-3 py-2 text-sm leading-6 text-slate-900 outline-none placeholder:text-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                            placeholder="Write the main instruction for Gemini."
                        ></textarea>

                        @error('masterPrompt')
                            <p class="text-sm text-red-600" wire:transition>{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="audio-generator__actions">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="saveMasterPrompt"
                            class="audio-generator__button inline-flex min-h-11 items-center justify-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-900 hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-950 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="saveMasterPrompt" class="inline-flex items-center gap-2">
                                <x-icon name="save" />
                                <span>Save master prompt</span>
                            </span>
                            <span wire:loading wire:target="saveMasterPrompt" class="inline-flex items-center gap-2">
                                <x-icon name="loader-circle" class="animate-spin" />
                                <span>Saving...</span>
                            </span>
                        </button>
                    </div>
                </form>

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
                    <div class="audio-generator__field-row">
                        <div class="space-y-2">
                            <label for="selectedVoiceGender" class="inline-flex items-center gap-2 text-sm font-medium text-slate-900">
                                <x-icon name="users" class="size-4 text-slate-500" />
                                <span>Voice gender</span>
                            </label>

                            <select
                                id="selectedVoiceGender"
                                wire:model="selectedVoiceGender"
                                wire:change="selectVoiceGender($event.target.value)"
                                class="min-h-11 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                            >
                                @foreach ($voiceGenders as $gender)
                                    <option value="{{ $gender }}">{{ $gender }}</option>
                                @endforeach
                            </select>

                            @error('selectedVoiceGender')
                                <p class="text-sm text-red-600" wire:transition>{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label for="selectedVoice" class="inline-flex items-center gap-2 text-sm font-medium text-slate-900">
                                <x-icon name="mic" class="size-4 text-slate-500" />
                                <span>Voice generator</span>
                            </label>

                            <select
                                id="selectedVoice"
                                wire:key="voice-generator-{{ $selectedVoiceGender }}"
                                wire:model="selectedVoice"
                                wire:change="selectVoice($event.target.value)"
                                class="min-h-11 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                            >
                                @foreach ($voiceGenerators as $generator)
                                    <option value="{{ $generator['name'] }}">{{ $generator['name'] }}</option>
                                @endforeach
                            </select>

                            @error('selectedVoice')
                                <p class="text-sm text-red-600" wire:transition>{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="audio-generator__label-row">
                        <label for="text" class="inline-flex items-center gap-2 text-sm font-medium text-slate-900">
                            <x-icon name="file-text" class="size-4 text-slate-500" />
                            <span>Text</span>
                        </label>
                        <span class="text-xs text-slate-500">{{ mb_strlen($text) }} / 5000</span>
                    </div>

                    <textarea
                        id="text"
                        wire:model="text"
                        rows="12"
                        maxlength="5000"
                        class="w-full resize-y rounded-md border border-slate-300 px-3 py-2 text-sm leading-6 text-slate-900 outline-none placeholder:text-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                        placeholder="Enter text to generate audio."
                    ></textarea>

                    @error('text')
                        <p class="text-sm text-red-600" wire:transition>{{ $message }}</p>
                    @enderror

                    <div class="audio-generator__actions">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="generate"
                            class="audio-generator__button inline-flex min-h-11 items-center justify-center rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-950 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="generate" class="inline-flex items-center gap-2">
                                <x-icon name="audio-lines" />
                                <span>Generate audio</span>
                            </span>
                            <span wire:loading wire:target="generate" class="inline-flex items-center gap-2">
                                <x-icon name="loader-circle" class="animate-spin" />
                                <span>Generating...</span>
                            </span>
                        </button>

                        <span wire:loading wire:target="generate" class="inline-flex items-center gap-2 text-sm text-slate-500">
                            <x-icon name="audio-lines" />
                            Creating WAV...
                        </span>
                    </div>
                </form>

                @if ($wavUrl)
                    <section class="space-y-2" wire:transition>
                        <div class="audio-generator__label-row">
                            <h2 class="inline-flex items-center gap-2 text-sm font-medium text-slate-900">
                                <x-icon name="volume" class="text-slate-500" />
                                <span>WAV</span>
                            </h2>
                            <a href="{{ $wavUrl }}" download class="inline-flex items-center gap-2 text-sm text-slate-600 underline underline-offset-4 hover:text-slate-950">
                                <x-icon name="download" />
                                Download WAV
                            </a>
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
                                        <button
                                            type="button"
                                            wire:click="usePrompt({{ $generation['id'] }})"
                                            class="inline-flex min-h-8 items-center gap-1.5 text-sm text-slate-700 underline underline-offset-4 hover:text-slate-950 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-950"
                                        >
                                            <x-icon name="play" class="size-3.5" />
                                            <span>Use</span>
                                        </button>

                                        <button
                                            type="button"
                                            wire:click="removePrompt({{ $generation['id'] }})"
                                            wire:loading.attr="disabled"
                                            wire:target="removePrompt({{ $generation['id'] }})"
                                            class="inline-flex min-h-8 items-center gap-1.5 text-sm text-red-600 underline underline-offset-4 hover:text-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                                        >
                                            <x-icon name="trash" class="size-3.5" />
                                            <span>Remove</span>
                                        </button>
                                    </div>
                                </div>

                                @if ($generation['master_prompt'])
                                    <p class="line-clamp-3 break-words text-xs leading-5 text-slate-500">{{ $generation['master_prompt'] }}</p>
                                @endif

                                @if ($generation['audio_file_name'])
                                    <p class="truncate text-xs text-slate-500">{{ $generation['audio_file_name'] }}</p>
                                @endif

                                @if ($generation['tts_voice_label'])
                                    <p class="truncate text-xs text-slate-500">{{ $generation['tts_voice_label'] }}</p>
                                @endif

                                @if ($generation['error_message'])
                                    <p class="break-words text-xs text-red-600">{{ $generation['error_message'] }}</p>
                                @endif

                                @if ($generation['audio_url'])
                                    <a href="{{ $generation['audio_url'] }}" download class="inline-flex items-center gap-1.5 text-sm text-slate-600 underline underline-offset-4 hover:text-slate-950">
                                        <x-icon name="download" class="size-3.5" />
                                        <span>Download</span>
                                    </a>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </aside>
        </div>
    </main>
</div>
