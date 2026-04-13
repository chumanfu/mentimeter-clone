import Chart from 'chart.js/auto';

export function registerPresentationSummary(Alpine) {
    Alpine.data('presentationSummary', () => ({
        open: false,
        loading: false,
        error: null,
        payload: null,
        charts: [],

        async loadSummary(url) {
            this.open = true;
            this.loading = true;
            this.error = null;
            this.payload = null;
            this.destroyCharts();

            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                const response = await fetch(url, {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token ?? '',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Could not load summary.');
                }

                this.payload = await response.json();
            } catch (e) {
                this.error = e.message ?? 'Something went wrong.';
            } finally {
                this.loading = false;
            }

            this.$nextTick(() => {
                requestAnimationFrame(() => this.buildCharts());
            });
        },

        destroyCharts() {
            this.charts.forEach((c) => c.destroy());
            this.charts = [];
        },

        buildCharts() {
            if (!this.payload?.questions?.length) {
                return;
            }

            const colors = [
                '#6366f1',
                '#8b5cf6',
                '#ec4899',
                '#f59e0b',
                '#10b981',
                '#06b6d4',
                '#ef4444',
                '#84cc16',
                '#f97316',
                '#14b8a6',
                '#a855f7',
                '#64748b',
            ];

            this.payload.questions.forEach((question) => {
                const canvas = document.getElementById(`pie-${question.id}`);
                if (!canvas || typeof canvas.getContext !== 'function') {
                    return;
                }

                const labels = question.totals.map((t) => t.label);
                const data = question.totals.map((t) => t.count);
                const total = data.reduce((a, b) => a + b, 0);

                if (total === 0) {
                    return;
                }

                const chart = new Chart(canvas, {
                    type: 'pie',
                    data: {
                        labels,
                        datasets: [
                            {
                                data,
                                backgroundColor: labels.map((_, i) => colors[i % colors.length]),
                            },
                        ],
                    },
                    options: {
                        plugins: {
                            legend: {
                                position: 'bottom',
                            },
                        },
                    },
                });

                this.charts.push(chart);
            });
        },

        closeModal() {
            this.destroyCharts();
            this.open = false;
            this.payload = null;
            this.error = null;
        },
    }));
}
