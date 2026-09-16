<script setup lang="ts">
const {
  sourceText, sourceLanguage, targetLanguage, current, isBusy, errorMessage, schedule, translate,
} = useTranslationWorkbench()

const languages = [
  { code: 'en', label: 'English' }, { code: 'uk', label: 'Ukrainian' }, { code: 'ru', label: 'Russian' },
  { code: 'de', label: 'German' }, { code: 'pl', label: 'Polish' }, { code: 'es', label: 'Spanish' }, { code: 'fr', label: 'French' },
]

const swapLanguages = () => {
  const source = sourceLanguage.value
  sourceLanguage.value = targetLanguage.value
  targetLanguage.value = source
  void schedule()
}
</script>

<template>
  <section class="overflow-hidden rounded-3xl border border-base-content/10 bg-base-100 shadow-2xl shadow-base-content/10">
    <div class="flex flex-wrap items-center gap-2 border-b border-base-content/10 px-4 py-3 sm:px-5">
      <select v-model="sourceLanguage" class="select select-sm w-40" @change="schedule"><option v-for="lang in languages" :key="lang.code" :value="lang.code">{{ lang.label }}</option></select>
      <button class="btn btn-circle btn-text btn-sm" type="button" aria-label="Swap languages" @click="swapLanguages"><span class="icon-[tabler--arrows-exchange] size-5" /></button>
      <select v-model="targetLanguage" class="select select-sm w-40" @change="schedule"><option v-for="lang in languages" :key="lang.code" :value="lang.code">{{ lang.label }}</option></select>
      <div class="ms-auto flex items-center gap-2 text-xs text-base-content/55"><span v-if="isBusy" class="loading loading-spinner loading-xs" />{{ isBusy ? $t('translator.analyzing') : $t('translator.ready') }}</div>
    </div>

    <div class="workspace-grid">
      <div class="text-panel border-b border-base-content/10 p-5 lg:border-e lg:border-b-0 sm:p-7">
        <div class="mb-4 flex items-center justify-between"><div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-base-content/45">{{ $t('translator.source') }}</p><h2 class="mt-1 text-lg font-medium">{{ $t('translator.write') }}</h2></div><span class="badge badge-soft badge-neutral">{{ sourceText.length }}/12000</span></div>
        <textarea v-model="sourceText" maxlength="12000" class="textarea h-72 w-full resize-none border-0 bg-transparent px-0 text-xl leading-relaxed focus:outline-none" :placeholder="$t('translator.placeholder')" @input="schedule" />
        <div class="mt-4 flex items-center justify-between border-t border-base-content/10 pt-4"><p class="max-w-xl text-xs leading-5 text-base-content/50">{{ $t('translator.privacyHint') }}</p><button type="button" class="btn btn-primary btn-sm" :disabled="!sourceText.trim() || isBusy" @click="translate"><span class="icon-[tabler--sparkles] size-4" />{{ $t('translator.translate') }}</button></div>
      </div>

      <div class="text-panel p-5 sm:p-7">
        <div class="mb-4 flex items-center justify-between"><div><p class="text-xs font-semibold uppercase tracking-[0.18em] text-primary">{{ $t('translator.translation') }}</p><h2 class="mt-1 text-lg font-medium">{{ $t('translator.result') }}</h2></div><button v-if="current?.translation" type="button" class="btn btn-circle btn-text btn-sm" @click="navigator.clipboard.writeText(current.translation ?? '')"><span class="icon-[tabler--copy] size-4" /></button></div>
        <div v-if="isBusy" class="space-y-3 pt-3"><div class="skeleton h-6 w-11/12"/><div class="skeleton h-6 w-9/12"/><div class="skeleton h-6 w-10/12"/></div>
        <p v-else-if="current?.translation" class="whitespace-pre-wrap text-xl leading-relaxed">{{ current.translation }}</p>
        <div v-else class="grid h-64 place-items-center text-center text-base-content/35"><div><span class="icon-[tabler--text-recognition] mx-auto mb-3 size-8"/><p>{{ $t('translator.empty') }}</p></div></div>
        <div v-if="errorMessage" class="alert alert-soft alert-error mt-4">{{ errorMessage }}</div>
      </div>
    </div>

    <div v-if="current?.natural_version || current?.issues?.length" class="border-t border-base-content/10 bg-base-200/20 p-4 sm:p-6">
      <div class="grid gap-4 xl:grid-cols-[0.9fr_1.1fr]">
        <article v-if="current?.natural_version" class="rounded-2xl border border-primary/20 bg-primary/5 p-5">
          <div class="mb-3 flex items-center gap-2 text-primary"><span class="icon-[tabler--message-language] size-5"/><h3 class="font-semibold">{{ $t('coach.natural') }}</h3></div>
          <p class="text-base leading-7">{{ current.natural_version }}</p>
        </article>
        <article class="rounded-2xl border border-base-content/10 bg-base-100 p-5">
          <div class="mb-4 flex items-center justify-between"><div class="flex items-center gap-2"><span class="icon-[tabler--school] size-5 text-secondary"/><h3 class="font-semibold">{{ $t('coach.title') }}</h3></div><span class="badge badge-soft badge-secondary">{{ current?.issues?.length ?? 0 }} {{ $t('coach.notes') }}</span></div>
          <div v-if="current?.source_corrected" class="mb-4 rounded-xl bg-base-200/45 p-3"><p class="text-xs text-base-content/50">{{ $t('coach.corrected') }}</p><p class="mt-1 text-sm">{{ current.source_corrected }}</p></div>
          <div v-if="current?.issues?.length" class="space-y-3">
            <div v-for="(issue, index) in current.issues" :key="`${issue.original}-${index}`" class="rounded-xl border border-base-content/10 p-3">
              <div class="mb-2 flex flex-wrap items-center gap-2"><span class="badge badge-sm" :class="issue.severity === 'error' ? 'badge-error' : issue.severity === 'warning' ? 'badge-warning' : 'badge-info'">{{ issue.type }}</span><span class="text-sm text-error line-through">{{ issue.original }}</span><span class="icon-[tabler--arrow-right] size-3 text-base-content/35"/><span class="text-sm text-success">{{ issue.correction }}</span></div>
              <p class="text-sm leading-6 text-base-content/65">{{ issue.explanation }}</p>
            </div>
          </div>
          <p v-else class="text-sm text-success"><span class="icon-[tabler--circle-check] me-1 inline size-4"/>{{ $t('coach.clean') }}</p>
        </article>
      </div>
    </div>
  </section>
</template>
