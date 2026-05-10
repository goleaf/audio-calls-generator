<div class="audio-generator bg-white text-slate-950">
    <main class="audio-generator__shell">
        <div class="audio-generator__grid">
            <section class="audio-generator__main space-y-7 sm:space-y-8">
                <form wire:submit="save" class="space-y-4">
                    <div class="space-y-2">
                        <label for="templateTitle" class="inline-flex items-center gap-2 text-sm font-medium text-slate-900">
                            <x-icon name="notebook-pen" class="size-4 text-slate-500" />
                            <span>Template title</span>
                        </label>

                        <input
                            id="templateTitle"
                            type="text"
                            wire:model="title"
                            maxlength="120"
                            class="min-h-11 w-full rounded-md border border-slate-300 px-3 py-2 text-sm text-slate-900 outline-none placeholder:text-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                            placeholder="Warm welcome"
                        >

                        @error('title')
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
                            wire:model="selectedLanguageCode"
                            class="min-h-11 w-full rounded-md border border-slate-300 bg-white px-3 py-2 text-sm text-slate-900 outline-none focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                        >
                            @foreach ($languageGroups as $readiness => $languages)
                                <optgroup label="{{ $readiness }}">
                                    @foreach ($languages as $language)
                                        <option value="{{ $language['code'] }}">{{ $language['label'] }}</option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>

                        @error('selectedLanguageCode')
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
                            rows="5"
                            maxlength="2000"
                            class="w-full resize-y rounded-md border border-slate-300 px-3 py-2 text-sm leading-6 text-slate-900 outline-none placeholder:text-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                            placeholder="Write the reusable instruction for Gemini."
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
                            <span class="text-xs text-slate-500">{{ mb_strlen($promptText) }} / 5000</span>
                        </div>

                        <textarea
                            id="promptText"
                            wire:model="promptText"
                            rows="12"
                            maxlength="5000"
                            class="w-full resize-y rounded-md border border-slate-300 px-3 py-2 text-sm leading-6 text-slate-900 outline-none placeholder:text-slate-400 focus:border-slate-900 focus:ring-1 focus:ring-slate-900"
                            placeholder="Write the reusable prompt text."
                        ></textarea>

                        @error('promptText')
                            <p class="text-sm text-red-600" wire:transition>{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="audio-generator__actions">
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            wire:target="save"
                            class="audio-generator__button inline-flex min-h-11 items-center justify-center rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-950 disabled:cursor-not-allowed disabled:opacity-60"
                        >
                            <span wire:loading.remove wire:target="save" class="audio-generator__button-content inline-flex items-center gap-2">
                                <x-icon name="save" />
                                <span>{{ $editingTemplateId === null ? 'Save template' : 'Update template' }}</span>
                            </span>
                            <span wire:loading.flex wire:target="save" class="audio-generator__button-content items-center gap-2">
                                <span class="audio-generator__button-spinner" aria-hidden="true"></span>
                                <span>{{ $editingTemplateId === null ? 'Saving...' : 'Updating...' }}</span>
                            </span>
                        </button>

                        @if ($editingTemplateId !== null)
                            <button
                                type="button"
                                wire:click="cancelEdit"
                                class="audio-generator__button inline-flex min-h-11 items-center justify-center rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-900 hover:bg-slate-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-950"
                            >
                                <x-icon name="x" />
                                <span>Cancel</span>
                            </button>
                        @endif
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
            </section>

            <aside class="audio-generator__history space-y-3">
                <h2 class="inline-flex items-center gap-2 text-sm font-medium text-slate-900">
                    <x-icon name="history" class="text-slate-500" />
                    <span>Prompt templates</span>
                </h2>

                <div class="overflow-x-auto border-y border-slate-200">
                    <table class="w-full min-w-[58rem] text-left text-sm">
                        <thead class="border-b border-slate-200 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th scope="col" class="px-3 py-3 font-medium">Title</th>
                                <th scope="col" class="px-3 py-3 font-medium">Master prompt</th>
                                <th scope="col" class="px-3 py-3 font-medium">Prompt text</th>
                                <th scope="col" class="px-3 py-3 font-medium">Language</th>
                                <th scope="col" class="px-3 py-3 font-medium">Voice gender</th>
                                <th scope="col" class="px-3 py-3 font-medium">Voice generator</th>
                                <th scope="col" class="px-3 py-3 font-medium">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200">
                            @forelse ($promptTemplates as $template)
                                <tr wire:key="prompt-template-{{ $template['id'] }}" class="align-top">
                                    <td class="px-3 py-3 font-medium text-slate-900">
                                        <span class="line-clamp-2 break-words">{{ $template['title'] }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-slate-600">
                                        <span class="line-clamp-3 break-words">{{ $template['master_prompt'] }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-slate-600">
                                        <span class="line-clamp-3 break-words">{{ $template['prompt_text'] }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-slate-600">
                                        <span class="line-clamp-2 break-words">{{ $template['language_label'] }}</span>
                                    </td>
                                    <td class="px-3 py-3 text-slate-600">{{ $template['tts_voice_gender'] }}</td>
                                    <td class="px-3 py-3 text-slate-600">{{ $template['tts_voice_label'] }}</td>
                                    <td class="px-3 py-3">
                                        <div class="flex flex-wrap items-center gap-3">
                                            <button
                                                type="button"
                                                wire:click="edit({{ $template['id'] }})"
                                                class="inline-flex min-h-8 items-center gap-1.5 text-sm text-slate-700 underline underline-offset-4 hover:text-slate-950 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-950"
                                            >
                                                <x-icon name="pencil" class="size-3.5" />
                                                <span>Edit</span>
                                            </button>

                                            <button
                                                type="button"
                                                wire:click="remove({{ $template['id'] }})"
                                                wire:loading.attr="disabled"
                                                wire:target="remove({{ $template['id'] }})"
                                                class="inline-flex min-h-8 items-center gap-1.5 text-sm text-red-600 underline underline-offset-4 hover:text-red-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                                            >
                                                <x-icon name="trash" class="size-3.5" />
                                                <span>Remove</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-3 py-4 text-sm text-slate-500">No templates yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </aside>
        </div>
    </main>
</div>
