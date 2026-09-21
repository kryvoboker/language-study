import { getLocalStorageItem, removeLocalStorageItem, setLocalStorageItem, windowMatchMedia } from '~/utils/helpers';

export type ThemePreference = 'system' | 'light' | 'dark';

export const useTheme = () => {
    const preference = useState<ThemePreference>('theme-preference', () => 'system');

    const apply = (value: ThemePreference) => {
        preference.value = value;
        if (!import.meta.client) {
            return;
        }

        if (value === 'system') {
            // If no stored theme, check system preference
            value = windowMatchMedia('(prefers-color-scheme: dark)') ? 'dark' : 'light';
            document.documentElement.setAttribute('data-is-system-theme', '1');

            setLocalStorageItem('native-lens-theme', 'system');
        } else {
            document.documentElement.setAttribute('data-is-system-theme', '0');
            setLocalStorageItem('native-lens-theme', value);
        }

        document.documentElement.setAttribute('data-theme', value);
    };

    const init = () => {
        if (!import.meta.client) {
            return;
        }

        const stored = getLocalStorageItem('native-lens-theme');
        apply(stored === 'light' || stored === 'dark' ? stored : 'system');
    };

    return { preference, apply, init };
};