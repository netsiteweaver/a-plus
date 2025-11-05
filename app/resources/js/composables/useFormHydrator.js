import { watch } from 'vue';

export function useFormHydrator(getSource, resetFn, options = {}) {
    const { immediate = false } = options;

    watch(
        getSource,
        (value) => {
            if (! value) {
                return;
            }

            resetFn(value);
        },
        { immediate }
    );
}
