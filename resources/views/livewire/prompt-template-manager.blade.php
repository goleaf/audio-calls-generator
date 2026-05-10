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
                                <span>Save template</span>
                            </span>
                            <span wire:loading.flex wire:target="save" class="audio-generator__button-content items-center gap-2">
                                <span class="audio-generator__button-spinner" aria-hidden="true"></span>
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
            </section>

            <aside class="audio-generator__history space-y-3">
                <h2 class="inline-flex items-center gap-2 text-sm font-medium text-slate-900">
                    <x-icon name="history" class="text-slate-500" />
                    <span>Prompt templates</span>
                </h2>

                @if ($promptTemplates === [])
                    <p class="border-y border-slate-200 py-3 text-sm text-slate-500">No templates yet.</p>
                @else
                    <div class="divide-y divide-slate-200 border-y border-slate-200">
                        @foreach ($promptTemplates as $template)
                            <div wire:key="prompt-template-{{ $template['id'] }}" class="space-y-3 py-3">
                                <div class="flex items-start justify-between gap-3">
                                    <h3 class="break-words text-sm font-medium text-slate-900">{{ $template['title'] }}</h3>

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

                                <p class="line-clamp-4 break-words text-xs leading-5 text-slate-500">{{ $template['prompt_text'] }}</p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </aside>
        </div>
    </main>
</div>
