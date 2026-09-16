import tailwindcss from '@tailwindcss/vite';

export default defineNuxtConfig({
    compatibilityDate: '2026-09-01',
    devtools:          { enabled: true },
    css:               ['~/assets/css/app.css'],
    modules:           ['@nuxtjs/i18n'],
    vite:              {
        plugins: [tailwindcss()],
        server: {
            allowedHosts: ['dev.language-study.com']
        }
    },
    // typescript:        {
    //     strict:    true,
    //     typeCheck: true,
    //     tsConfig:  {
    //         compilerOptions: {
    //             noUncheckedIndexedAccess:   true,
    //             exactOptionalPropertyTypes: true,
    //         },
    //     },
    // },
    runtimeConfig:     {
        apiInternalBase: process.env.NUXT_API_INTERNAL_BASE ?? 'http://nginx/api/v1',
        public:          {
            apiBase:             process.env.NUXT_PUBLIC_API_BASE ?? '/api/v1',
            backendOrigin:       process.env.NUXT_PUBLIC_BACKEND_ORIGIN ?? 'http://localhost:8080',
            passportClientId:    process.env.NUXT_PUBLIC_PASSPORT_CLIENT_ID ?? '',
            passportRedirectUri: process.env.NUXT_PUBLIC_PASSPORT_REDIRECT_URI ?? 'http://localhost:8080/auth/callback',
        },
    },
    i18n:              {
        defaultLocale: 'en',
        strategy:      'prefix_except_default',
        locales:       [
            { code: 'en', name: 'English', file: 'en.json' },
            { code: 'uk', name: 'Українська', file: 'uk.json' },
            { code: 'ru', name: 'Русский', file: 'ru.json' },
        ],
        langDir:       '../i18n/locales',
    },
    app:               {
        head: {
            title:  'NativeLens — Translate. Understand. Sound natural.',
            meta:   [
                { name: 'description', content: 'AI-assisted translation with corrections, explanations and natural native-like phrasing.' },
            ],
            script: [
                {
                    key:         'storefront-theme',
                    tagPosition: 'head',
                    innerHTML:
                                 "try{const theme=localStorage.getItem('nativelens.storefront.theme');if(theme==='light'||theme==='black'){document.documentElement.dataset.theme=theme}}catch{}",
                },
            ],
        },
    },
})