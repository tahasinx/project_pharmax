import { computed, ref } from 'vue';

const storageKey = 'epharma-theme';
const preference = ref(typeof localStorage === 'undefined' ? 'system' : (localStorage.getItem(storageKey) || 'system'));
const systemDark = ref(typeof window !== 'undefined' && window.matchMedia('(prefers-color-scheme: dark)').matches);

const palettes = {
    light: {
        canvas: '#f8f8fb',
        card: '#ffffff',
        text: '#495057',
        muted: '#74788d',
        grid: '#f1f1f5',
    },
    dark: {
        canvas: '#171a21',
        card: '#20242d',
        text: '#d8dee9',
        muted: '#98a2b3',
        grid: '#303642',
    },
    night: {
        canvas: '#171b2a',
        card: '#222844',
        text: '#c9d2ea',
        muted: '#93a0c4',
        grid: '#343c5c',
    },
};

export function useTheme() {
    const appearance = computed(() => (preference.value === 'system' ? (systemDark.value ? 'dark' : 'light') : preference.value));
    const mode = computed(() => (appearance.value === 'light' ? 'light' : 'dark'));
    const palette = computed(() => palettes[appearance.value] || palettes.light);

    const setPreference = (value) => {
        preference.value = value;
        localStorage.setItem(storageKey, value);
    };

    const syncSystem = () => {
        systemDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    };

    return { preference, appearance, mode, palette, setPreference, syncSystem };
}
