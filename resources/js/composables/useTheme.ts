import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

type Theme = 'light' | 'dark' | 'system';

export function useTheme() {
    const page = usePage();
    const theme = ref<Theme>((localStorage.getItem('theme') as Theme) || 'system');

    const effectiveTheme = computed(() => {
        if (theme.value === 'system') {
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        return theme.value;
    });

    const isDark = computed(() => effectiveTheme.value === 'dark');

    const setTheme = (newTheme: Theme) => {
        theme.value = newTheme;
        localStorage.setItem('theme', newTheme);
        updateHtmlClass();
    };

    const toggleTheme = () => {
        setTheme(isDark.value ? 'light' : 'dark');
    };

    const updateHtmlClass = () => {
        const html = document.documentElement;
        if (isDark.value) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
    };

    if (typeof window !== 'undefined') {
        const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
        mediaQuery.addEventListener('change', () => {
            if (theme.value === 'system') {
                updateHtmlClass();
            }
        });
    }

    watch(effectiveTheme, updateHtmlClass, { immediate: true });

    return {
        theme,
        effectiveTheme,
        isDark,
        setTheme,
        toggleTheme,
    };
}
