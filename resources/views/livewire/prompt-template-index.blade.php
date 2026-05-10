<div class="audio-generator bg-white text-slate-950">
    <main class="audio-generator__shell">
        <section class="space-y-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="inline-flex items-center gap-2 text-base font-semibold text-slate-950">
                    <x-icon name="notebook-pen" class="size-4 text-slate-500" />
                    <span>Prompt templates</span>
                </h1>

                <x-button
                    as="a"
                    href="{{ route('audio.prompt-templates.create') }}"
                    wire:navigate
                    size="md"
                >
                    <x-icon name="plus" />
                    <span>Create template</span>
                </x-button>
            </div>

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

            <div class="audio-generator__table-scroll">
                <table class="audio-generator__table">
                    <thead>
                            <tr>
                                <th scope="col">
                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap">
                                        <x-icon name="notebook-pen" class="size-3.5" />
                                        <span>Title</span>
                                    </span>
                                </th>
                                <th scope="col">
                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap">
                                        <x-icon name="message-square-text" class="size-3.5" />
                                        <span>Master prompt</span>
                                    </span>
                                </th>
                                <th scope="col">
                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap">
                                        <x-icon name="file-text" class="size-3.5" />
                                        <span>Prompt text</span>
                                    </span>
                                </th>
                                <th scope="col">
                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap">
                                        <x-icon name="languages" class="size-3.5" />
                                        <span>Language</span>
                                    </span>
                                </th>
                                <th scope="col">
                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap">
                                        <x-icon name="users" class="size-3.5" />
                                        <span>Voice gender</span>
                                    </span>
                                </th>
                                <th scope="col">
                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap">
                                        <x-icon name="mic" class="size-3.5" />
                                        <span>Voice generator</span>
                                    </span>
                                </th>
                                <th scope="col">
                                    <span class="inline-flex items-center gap-1.5 whitespace-nowrap">
                                        <x-icon name="audio-lines" class="size-3.5" />
                                        <span>Actions</span>
                                    </span>
                                </th>
                            </tr>
                    </thead>
                    <tbody>
                        @forelse ($this->promptTemplates as $template)
                            <tr wire:key="prompt-template-{{ $template['id'] }}">
                                <td>
                                    <span class="line-clamp-2 break-words">{{ $template['title'] }}</span>
                                </td>
                                <td>
                                    <span class="line-clamp-3 break-words">{{ $template['master_prompt'] }}</span>
                                </td>
                                <td>
                                    <span class="line-clamp-3 break-words">{{ $template['prompt_text'] }}</span>
                                </td>
                                <td>
                                    <span class="inline-flex items-center gap-1.5 break-words">
                                        @if ($this->languageFlag($template['language_code']) !== '')
                                            <span class="shrink-0" role="img" aria-label="Language flag">{{ $this->languageFlag($template['language_code']) }}</span>
                                        @endif
                                        <span>{{ $template['language_label'] }}</span>
                                    </span>
                                </td>
                                <td>{{ $template['tts_voice_gender'] }}</td>
                                <td>{{ $template['tts_voice_label'] }}</td>
                                <td>
                                    <div class="audio-generator__table-actions flex-nowrap">
                                        <x-button
                                            as="a"
                                            href="{{ route('audio.prompt-templates.edit', $template['id']) }}"
                                            wire:navigate
                                            class="!w-auto shrink-0 whitespace-nowrap"
                                            size="sm"
                                        >
                                            <x-icon name="pencil" class="size-3.5" />
                                            <span>Edit</span>
                                        </x-button>

                                        <x-button
                                            type="button"
                                            class="!w-auto shrink-0 whitespace-nowrap"
                                            size="sm"
                                            wire:click="remove({{ $template['id'] }})"
                                            wire:loading.attr="disabled"
                                            wire:target="remove({{ $template['id'] }})"
                                        >
                                            <x-icon name="trash" class="size-3.5" />
                                            <span>Remove</span>
                                        </x-button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="border-y border-slate-200 py-3 text-sm text-slate-500">No templates yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
