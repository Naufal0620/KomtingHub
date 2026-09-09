import Alpine from 'alpinejs';

document.addEventListener('alpine:init', () => {
    Alpine.data('searchableSelect', (config = {}) => ({
        multiple: config.multiple ?? false,
        options: config.options ?? [],
        selected: config.selected ?? [],
        placeholder: config.placeholder || 'Pilih...',
        open: false,
        query: '',

        get filteredOptions() {
            const q = this.query.trim().toLowerCase();
            if (!q) return this.options;

            return this.options.filter((opt) =>
                String(opt.label).toLowerCase().includes(q) ||
                String(opt.value).toLowerCase().includes(q)
            );
        },

        get selectedLabels() {
            return this.options
                .filter((opt) => this.selected.includes(opt.value))
                .map((opt) => opt.label);
        },

        get isSelectedEmpty() {
            return this.selected.length === 0;
        },

        toggle() {
            this.open = !this.open;
            if (this.open) {
                this.query = '';
                this.$nextTick(() => this.$refs.search && this.$refs.search.focus());
            }
        },

        close() {
            this.open = false;
        },

        isSelected(value) {
            return this.selected.includes(value);
        },

        select(value) {
            if (this.multiple) {
                if (this.selected.includes(value)) {
                    this.selected = this.selected.filter((v) => v !== value);
                } else {
                    this.selected = [...this.selected, value];
                }
            } else {
                this.selected = [value];
                this.open = false;
            }
        },

        toggleValue(value) {
            this.select(value);
        },

        labelOf(value) {
            const found = this.options.find((opt) => opt.value === String(value));
            return found ? found.label : value;
        },
    }));
});