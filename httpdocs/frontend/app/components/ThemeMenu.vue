<script setup lang="ts">
const { preference, apply } = useTheme();
const options = [
    { value: 'system' as const, label: 'System', icon: 'icon-[tabler--device-desktop]' },
    { value: 'light' as const, label: 'Light', icon: 'icon-[tabler--sun-high]' },
    { value: 'dark' as const, label: 'Dark', icon: 'icon-[tabler--moon]' },
];
</script>

<template>
    <div class="dropdown relative inline-flex [--placement:bottom-end]">
        <button type="button" class="dropdown-toggle btn btn-square btn-text btn-sm"
                :aria-label="$t('theme.toggle')"
                :title="$t('theme.toggle')">
            <span class="theme-switch__to-system icon-[tabler--device-desktop] size-5" />
            <span class="theme-switch__to-light icon-[tabler--sun-high] size-5" />
            <span class="theme-switch__to-dark icon-[tabler--moon] size-5" />
        </button>
        <ul class="dropdown-menu dropdown-open:opacity-100 hidden min-w-40" role="menu">
            <li v-for="item in options" :key="item.value">
                <button type="button" class="dropdown-item flex items-center justify-between" @click="apply(item.value)">
                    <span class="flex items-center gap-2"><span :class="[item.icon, 'size-5']" />{{ item.label }}</span>
                    <span v-if="preference === item.value" class="icon-[tabler--check] size-5" />
                </button>
            </li>
        </ul>
    </div>
</template>