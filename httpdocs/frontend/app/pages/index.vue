<template>
    <section class="home-section">
        <div class="container">
            <div class="mb-2">
                <div class="mb-3 inline-flex items-center gap-2 rounded-full border border-primary bg-primary-content px-3 py-1.5 text-xs font-medium text-primary">
                    <span class="icon-[tabler--sparkles] size-4"/>
                    {{ $t('hero.badge') }}
                </div>
                <h1 class="text-4xl font-semibold tracking-[-0.035em] sm:text-5xl">{{ $t('hero.title') }}</h1>
                <p class="mt-4 max-w-2xl text-base leading-7 text-base-content/60 sm:text-lg">{{ $t('hero.subtitle') }}</p>
            </div>

            <TranslationWorkbench/>

            <div class="mt-5 flex flex-wrap gap-x-6 gap-y-2 text-xs text-base-content/40">
                <div class="flex items-center gap-1.5">
                    <span class="flex items-center gap-1.5">
                        <span class="icon-[tabler--bolt] size-4"/>
                        {{ $t('benefits.fast') }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="icon-[tabler--brain] size-4"/>
                        {{ $t('benefits.learn') }}
                    </span>
                </div>
            </div>
        </div>
    </section>
</template>