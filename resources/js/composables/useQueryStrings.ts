import type { VisitOptions } from '@inertiajs/core';
import { router, usePage } from '@inertiajs/vue3';
import { watchDebounced } from '@vueuse/core';
import { computed, ref, watch } from 'vue';

export type QueryValue = string | number | boolean | undefined | null;

export interface QueryParams {
    [key: string]: QueryValue;
}

export interface RouterOptions {
    only?: string[];
    preserveState?: boolean;
    preserveScroll?: boolean;
    replace?: boolean;
}

function isEmpty(value: unknown): boolean {
    return value === null || value === undefined || value === '';
}

function buildQueryString(params: QueryParams): string {
    const searchParams = new URLSearchParams();
    Object.entries(params).forEach(([key, value]) => {
        if (!isEmpty(value)) {
            searchParams.set(key, String(value));
        }
    });
    const qs = searchParams.toString();
    return qs ? `?${qs}` : '';
}

export function useQueryStrings() {
    const page = usePage();
    const currentUrl = computed(() => new URL(page.url, window.location.origin));

    const query = ref<QueryParams>({});
    const routerOptions = ref<RouterOptions>({});

    const syncFromUrl = () => {
        const params: QueryParams = {};
        currentUrl.value.searchParams.forEach((v, k) => {
            if (!isEmpty(v)) params[k] = v;
        });

        const currentKeys = Object.keys(query.value);
        const newKeys = Object.keys(params);
        const hasChanged = currentKeys.length !== newKeys.length || newKeys.some((k) => query.value[k] !== params[k]);

        if (hasChanged) {
            query.value = params;
        }
    };

    syncFromUrl();

    watch(() => page.url, syncFromUrl, { flush: 'post' });

    const get = (key: string): string | undefined | null => query.value[key] as string | undefined | null;

    const set = (key: string, value: QueryValue) => {
        if (isEmpty(value)) {
            delete query.value[key];
        } else {
            query.value[key] = value;
        }

        const resetPageKeys = ['search', 'sort', 'sort_direction'];
        if (resetPageKeys.includes(key)) {
            delete query.value.page;
        }
    };

    const remove = (key: string) => {
        delete query.value[key];
    };

    const setRouterOptions = (options: RouterOptions) => {
        routerOptions.value = options;
    };

    const navigate = () => {
        const path = currentUrl.value.pathname;
        const targetUrl = path + buildQueryString(query.value);

        if (targetUrl === page.url) return;

        const options: VisitOptions = {
            data: query.value,
            preserveState: routerOptions.value.preserveState ?? true,
            preserveScroll: routerOptions.value.preserveScroll ?? true,
            replace: routerOptions.value.replace ?? true,
        };
        if (routerOptions.value.only) {
            options.only = routerOptions.value.only;
        }
        router.visit(path, options);
    };

    const buildDetailUrl = (detailPath: string, paramName = 'return_to'): string => {
        const state = currentUrl.value.pathname + currentUrl.value.search;
        if (!state || state === '/') return detailPath;
        const sep = detailPath.includes('?') ? '&' : '?';
        return `${detailPath}${sep}${paramName}=${encodeURIComponent(state)}`;
    };

    const getReturnUrl = (paramName = 'return_to', fallbackUrl = ''): string => {
        const captured = currentUrl.value.searchParams.get(paramName);
        return captured ? decodeURIComponent(captured) : fallbackUrl;
    };

    watchDebounced(query, () => navigate(), { deep: true, debounce: 300, maxWait: 1000 });

    return {
        query: computed(() => query.value),
        queryString: computed(() => buildQueryString(query.value)),
        get,
        set,
        remove,
        setRouterOptions,
        navigate,
        buildDetailUrl,
        getReturnUrl,
    };
}
