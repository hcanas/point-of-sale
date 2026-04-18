import { onMounted, onUnmounted } from 'vue';

export interface Keybind {
    key: string;
    ctrl?: boolean;
    alt?: boolean;
    shift?: boolean;
    meta?: boolean;
    handler: (event: KeyboardEvent) => void;
}

export function useKeybinds(keybinds: Keybind[]) {
    const matches = (event: KeyboardEvent, keybind: Keybind): boolean => {
        const key = event.key.toLowerCase();
        const targetKey = keybind.key.toLowerCase();

        return (
            key === targetKey &&
            !!keybind.ctrl === (event.ctrlKey || event.metaKey) &&
            !!keybind.alt === event.altKey &&
            !!keybind.shift === event.shiftKey
        );
    };

    const handleKeydown = (event: KeyboardEvent) => {
        if (event.key === 'Alt' && !event.ctrlKey && !event.shiftKey && !event.metaKey) {
            event.preventDefault();
            return;
        }

        for (const keybind of keybinds) {
            if (matches(event, keybind)) {
                event.preventDefault();
                keybind.handler(event);
                return;
            }
        }
    };

    onMounted(() => {
        document.addEventListener('keydown', handleKeydown);
    });

    onUnmounted(() => {
        document.removeEventListener('keydown', handleKeydown);
    });

    return {
        handleKeydown,
    };
}
