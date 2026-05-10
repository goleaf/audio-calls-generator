<div class="audio-generator bg-white text-slate-950">
    <main class="audio-generator__shell">
        <section class="space-y-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="inline-flex items-center gap-2 text-base font-semibold text-slate-950">
                    <x-icon name="notebook-pen" class="size-4 text-slate-500" />
                    <span>{{ $editingTemplateId === null ? 'Create prompt template' : 'Edit prompt template' }}</span>
                </h1>

                <x-button
                    as="a"
                    href="{{ route('audio.prompt-templates') }}"
                    wire:navigate
                    size="md"
                >
                    <x-icon name="arrow-left" />
                    <span>Back to templates</span>
                </x-button>
            </div>

            @if ($errorMessage)
                <p class="inline-flex items-start gap-2 text-sm text-red-600" wire:transition>
                    <x-icon name="circle-alert" class="mt-0.5" />
                    <span>{{ $errorMessage }}</span>
                </p>
            @endif

            <form wire:submit="save" class="space-y-4">
                <div class="space-y-2">
                    <label for="templateTitle" class="inline-flex items-center gap-2 text-sm font-medium text-slate-900">
                        <x-icon name="notebook-pen" class="size-4 text-slate-500" />
                        <span>Template title</span>
                    </label>

                    <input
                        id="templateTitle"
                        type="text"
                        wire:model="form.title"
                        maxlength="120"
                        class="min-h-11 w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                        placeholder="Warm welcome"
                    >

                    @error('form.title')
                        <p class="text-sm text-red-600" wire:transition>{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <label for="selectedLanguageCode" class="inline-flex items-center gap-2 text-sm font-medium text-slate-900">
                        <x-icon name="languages" class="size-4 text-slate-500" />
                        <span>Language</span>
                    </label>

                    <select
                        id="selectedLanguageCode"
                        wire:model="form.selectedLanguageCode"
                        class="min-h-11 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                    >
                        @foreach ($this->languageGroups as $readiness => $languages)
                            <optgroup label="{{ $readiness }}">
                                @foreach ($languages as $language)
                                    <option value="{{ $language['code'] }}">{{ $language['label'] }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>

                    @error('form.selectedLanguageCode')
                        <p class="text-sm text-red-600" wire:transition>{{ $message }}</p>
                    @enderror
                </div>

                <div class="audio-generator__field-row">
                    <div class="space-y-2">
                        <label for="selectedVoiceGender" class="inline-flex items-center gap-2 text-sm font-medium text-slate-900">
                            <x-icon name="users" class="size-4 text-slate-500" />
                            <span>Voice gender</span>
                        </label>

                        <select
                            id="selectedVoiceGender"
                            wire:model="form.selectedVoiceGender"
                            wire:change="selectVoiceGender($event.target.value)"
                            class="min-h-11 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                        >
                            @foreach ($this->voiceGenders as $gender)
                                <option value="{{ $gender }}">{{ $gender }}</option>
                            @endforeach
                        </select>

                        @error('form.selectedVoiceGender')
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
                            wire:key="voice-generator-{{ $form->selectedVoiceGender }}"
                            wire:model="form.selectedVoice"
                            class="min-h-11 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                        >
                            @foreach ($this->voiceGenerators as $generator)
                                <option value="{{ $generator['name'] }}">{{ $generator['name'] }}</option>
                            @endforeach
                        </select>

                        @error('form.selectedVoice')
                            <p class="text-sm text-red-600" wire:transition>{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="space-y-2">
                    <div class="audio-generator__label-row">
                        <label for="masterPrompt" class="inline-flex items-center gap-2 text-sm font-medium text-slate-900">
                            <x-icon name="message-square-text" class="size-4 text-slate-500" />
                            <span>Master prompt</span>
                        </label>
                        <span class="text-xs text-slate-500">{{ mb_strlen($form->masterPrompt) }} / 2000</span>
                    </div>

                    <textarea
                        id="masterPrompt"
                        wire:model="form.masterPrompt"
                        rows="5"
                        maxlength="2000"
                        class="w-full resize-y rounded-md border border-slate-300 px-3 py-2 text-sm leading-6 text-slate-900 outline-none placeholder:text-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                        placeholder="Write the reusable instruction for Gemini."
                    ></textarea>

                    @error('form.masterPrompt')
                        <p class="text-sm text-red-600" wire:transition>{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-2">
                    <div class="audio-generator__label-row">
                        <label for="promptText" class="inline-flex items-center gap-2 text-sm font-medium text-slate-900">
                            <x-icon name="file-text" class="size-4 text-slate-500" />
                            <span>Prompt text</span>
                        </label>
                        <span class="text-xs text-slate-500">{{ mb_strlen($form->promptText) }} / 5000</span>
                    </div>

                    <textarea
                        id="promptText"
                        wire:model="form.promptText"
                        rows="12"
                        maxlength="5000"
                        class="w-full resize-y rounded-md border border-slate-300 px-3 py-2 text-sm leading-6 text-slate-900 outline-none placeholder:text-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                        placeholder="Write the reusable prompt text."
                    ></textarea>

                    @error('form.promptText')
                        <p class="text-sm text-red-600" wire:transition>{{ $message }}</p>
                    @enderror
                </div>

                <div class="audio-generator__actions">
                    <x-button
                        type="submit"
                        wire:loading.attr="disabled"
                        wire:target="save"
                        size="lg"
                    >
                        <span wire:loading.remove wire:target="save" class="audio-generator__button-content inline-flex items-center gap-2">
                            <x-icon name="save" />
                            <span>{{ $editingTemplateId === null ? 'Save template' : 'Update template' }}</span>
                        </span>
                        <span wire:loading.flex wire:target="save" class="audio-generator__button-content items-center gap-2">
                            <span class="audio-generator__button-spinner" aria-hidden="true"></span>
                            <span>{{ $editingTemplateId === null ? 'Saving...' : 'Updating...' }}</span>
                        </span>
                    </x-button>

                    <x-button
                        type="button"
                        wire:click="cancelEdit"
                        size="lg"
                    >
                        <x-icon name="x" />
                        <span>Cancel</span>
                    </x-button>
                </div>
            </form>
        </section>
    </main>
</div>
