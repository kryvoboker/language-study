import tailwindcss      from '@tailwindcss/vite'
import laravel          from 'laravel-vite-plugin'
import { defineConfig } from 'vite'

export default defineConfig({
    build:   {
        sourcemap: true,
    },
    plugins: [
        laravel({
            input:   [
                'resources/assets/filament/css/admin/theme.css'
            ],
            refresh: [
                'resources/views/**',
                'app/Filament/**'
            ],
        }),
        tailwindcss(),
    ],
})