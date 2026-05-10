<div class="audio-generator bg-white text-slate-950">
    <main class="audio-generator__shell">
        <section class="space-y-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <h1 class="inline-flex items-center gap-2 text-base font-semibold text-slate-950">
                    <x-icon name="notebook-pen" class="size-4 text-slate-500" />
                    <span>Prompt templates</span>
                </h1>

                <a
                    href="{{ route('audio.prompt-templates.create') }}"
                    wire:navigate
                    class="audio-generator__button inline-flex min-h-10 items-center justify-center gap-2 rounded-md bg-slate-950 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-950"
                >
                    <x-icon name="plus" />
                    <span>Create template</span>
                </a>
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
                            <th scope="col">Title</th>
                            <th scope="col">Master prompt</th>
                            <th scope="col">Prompt text</th>
                            <th scope="col">Language</th>
                            <th scope="col">Voice gender</th>
                            <th scope="col">Voice generator</th>
                            <th scope="col">Actions</th>
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
                                    <span class="line-clamp-2 break-words">{{ $template['language_label'] }}</span>
                                </td>
                                <td>{{ $template['tts_voice_gender'] }}</td>
                                <td>{{ $template['tts_voice_label'] }}</td>
                                <td>
                                    <div class="audio-generator__table-actions">
                                        <a
                                            href="{{ route('audio.prompt-templates.edit', $template['id']) }}"
                                            wire:navigate
                                            class="inline-flex min-h-8 items-center gap-1.5 text-sm text-slate-700 underline underline-offset-4 hover:text-slate-950 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-950"
                                        >
                                            <x-icon name="pencil" class="size-3.5" />
                                            <span>Edit</span>
                                        </a>

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
                                <td colspan="7">No templates yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>
