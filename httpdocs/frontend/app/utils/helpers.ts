type BrowserStorageType = 'local' | 'session';

const getBrowserStorage = (type: BrowserStorageType): Storage | null => {
    if (typeof window === 'undefined') {
        return null;
    }

    try {
        return type === 'local' ? window.localStorage : window.sessionStorage;
    } catch {
        return null;
    }
};

const getStorageItem = (type: BrowserStorageType, key: string): string | null => {
    try {
        return getBrowserStorage(type)?.getItem(key) ?? null;
    } catch {
        return null;
    }
};

const setStorageItem = (type: BrowserStorageType, key: string, value: string): boolean => {
    try {
        const storage = getBrowserStorage(type);
        if (!storage) {
            return false;
        }

        storage.setItem(key, value);
        return true;
    } catch {
        return false;
    }
};

const removeStorageItem = (type: BrowserStorageType, key: string): boolean => {
    try {
        const storage = getBrowserStorage(type);
        if (!storage) {
            return false;
        }

        storage.removeItem(key);
        return true;
    } catch {
        return false;
    }
};

export const getLocalStorageItem = (key: string): string | null => getStorageItem('local', key);

export const setLocalStorageItem = (key: string, value: string): boolean => setStorageItem('local', key, value);

export const removeLocalStorageItem = (key: string): boolean => removeStorageItem('local', key);

export const getSessionStorageItem = (key: string): string | null => getStorageItem('session', key);

export const setSessionStorageItem = (key: string, value: string): boolean => setStorageItem('session', key, value);

export const removeSessionStorageItem = (key: string): boolean => removeStorageItem('session', key);

export const windowMatchMedia     = (query : string) : boolean => matchMedia(`(${query.replace(/^\(+/, '').replace(/\)+$/, '')})`).matches;

export function arrayFrom<T>(values: ArrayLike<T> | Iterable<T> | null | undefined): T[];
export function arrayFrom<T, U>(
    values: ArrayLike<T> | Iterable<T> | null | undefined,
    mapValue: (value: T, index: number) => U,
): U[];
export function arrayFrom<T, U>(
    values: ArrayLike<T> | Iterable<T> | null | undefined,
    mapValue?: (value: T, index: number) => U,
): T[] | U[] {
    if (values === null || values === undefined) {
        return [];
    }

    if (mapValue) {
        return Array.from(values, mapValue);
    }

    return Array.from(values);
}

export const isArray = (value: unknown): value is unknown[] => Array.isArray(value);

export const isRecord = (value: unknown): value is Record<string, unknown> =>
    typeof value === 'object' && value !== null && !isArray(value);

export const parseJson = <T>(value: string | null, isExpectedValue: (value: unknown) => value is T): T | null => {
    if (value === null) {
        return null;
    }

    try {
        const parsedValue: unknown = JSON.parse(value);
        return isExpectedValue(parsedValue) ? parsedValue : null;
    } catch {
        return null;
    }
};

export const stringifyJson = (value: unknown): string | null => {
    try {
        const serializedValue = JSON.stringify(value);
        return typeof serializedValue === 'string' ? serializedValue : null;
    } catch {
        return null;
    }
};

export const getSessionStorageJson = <T>(key: string, isExpectedValue: (value: unknown) => value is T): T | null =>
    parseJson(getSessionStorageItem(key), isExpectedValue);

export const setSessionStorageJson = (key: string, value: unknown): boolean => {
    const serializedValue = stringifyJson(value);
    return serializedValue !== null && setSessionStorageItem(key, serializedValue);
};

export const getSafeInternalPath = (value: string | null, origin: string): string | null => {
    if (value === null || !value.startsWith('/') || value.startsWith('//') || value.includes('\\')) {
        return null;
    }

    try {
        const parsedUrl = new URL(value, origin);
        if (parsedUrl.origin !== origin) {
            return null;
        }

        return `${parsedUrl.pathname}${parsedUrl.search}${parsedUrl.hash}`;
    } catch {
        return null;
    }
};