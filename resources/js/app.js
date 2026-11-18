import Alpine from 'alpinejs';
import {
    Chart,
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
    BarController,
    LineController,
    DoughnutController,
} from 'chart.js';

// Register Chart.js components we need
Chart.register(
    CategoryScale,
    LinearScale,
    BarElement,
    LineElement,
    PointElement,
    ArcElement,
    Title,
    Tooltip,
    Legend,
    Filler,
    BarController,
    LineController,
    DoughnutController,
);

// Shared Chart.js defaults — restrained institutional style
Chart.defaults.font.family = "'Inter', ui-sans-serif, system-ui, sans-serif";
Chart.defaults.font.size   = 12;
Chart.defaults.color       = '#64748B';
Chart.defaults.plugins.legend.labels.boxWidth  = 12;
Chart.defaults.plugins.legend.labels.padding   = 16;
Chart.defaults.plugins.tooltip.padding         = 10;
Chart.defaults.plugins.tooltip.cornerRadius    = 8;
Chart.defaults.plugins.tooltip.backgroundColor = '#0F172A';
Chart.defaults.plugins.tooltip.titleColor      = '#F8FAFC';
Chart.defaults.plugins.tooltip.bodyColor       = '#CBD5E1';

window.Alpine = Alpine;
window.Chart  = Chart;

// -------------------------------------------------------------------------
// Connection status component
// -------------------------------------------------------------------------
Alpine.data('connectionStatus', () => ({
    status : 'online',
    message: 'Online',
    init() {
        this.updateStatus();
        window.addEventListener('online',  () => this.setOnline());
        window.addEventListener('offline', () => this.setOffline());
    },
    updateStatus() {
        this.status  = navigator.onLine ? 'online' : 'offline';
        this.message = navigator.onLine ? 'Online' : 'Offline';
    },
    setOnline()  { this.status = 'online';  this.message = 'Online'; },
    setOffline() { this.status = 'offline'; this.message = 'Offline'; },
}));

// -------------------------------------------------------------------------
// Sidebar
// -------------------------------------------------------------------------
Alpine.data('sidebar', () => ({
    open      : window.innerWidth >= 1024,
    mobileOpen: false,
    toggle() {
        if (window.innerWidth >= 1024) { this.open = !this.open; }
        else { this.mobileOpen = !this.mobileOpen; }
    },
    closeMobile() { this.mobileOpen = false; this.$nextTick(() => this.$refs.mobileNavToggle.focus()); },
    trapFocus(event) {
        const controls = [...document.getElementById('sidebar').querySelectorAll('a[href], button:not([disabled])')]
            .filter(element => element.getClientRects().length && getComputedStyle(element).visibility !== 'hidden');
        const first = controls[0];
        const last = controls[controls.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    },
}));

// -------------------------------------------------------------------------
// Collapsible nav group (session-persisted)
// -------------------------------------------------------------------------
Alpine.data('navGroup', (key, initialActive = false) => ({
    expanded: initialActive || (sessionStorage.getItem('nav_' + key) === 'true'),
    toggle() {
        this.expanded = !this.expanded;
        sessionStorage.setItem('nav_' + key, this.expanded);
    },
}));

// -------------------------------------------------------------------------
// Dropdown
// -------------------------------------------------------------------------
Alpine.data('dropdown', () => ({
    open  : false,
    toggle() { this.open = !this.open; },
    close()  { this.open = false; },
}));

// -------------------------------------------------------------------------
// Modal
// -------------------------------------------------------------------------
Alpine.data('modal', () => ({
    show : false,
    open()  { this.show = true;  document.body.classList.add('overflow-hidden'); },
    close() { this.show = false; document.body.classList.remove('overflow-hidden'); },
}));

// -------------------------------------------------------------------------
// Alert auto-dismiss
// -------------------------------------------------------------------------
Alpine.data('alert', (duration = 5000) => ({
    visible: true,
    init() {
        if (duration > 0) { setTimeout(() => { this.visible = false; }, duration); }
    },
    dismiss() { this.visible = false; },
}));

// -------------------------------------------------------------------------
// Dashboard — reporting period switcher + Chart.js initialization
// -------------------------------------------------------------------------
Alpine.data('dashboard', (periodsJson, facilitiesJson) => {
    const periods    = JSON.parse(periodsJson);
    const facilities = JSON.parse(facilitiesJson);

    const charts = {};

    function destroyChart(key) {
        if (charts[key]) {
            charts[key].destroy();
            delete charts[key];
        }
    }

    return {
        currentPeriod: 'this_month',

        get period() { return periods[this.currentPeriod]; },

        // Computed stats
        get activeInmates()    { return this.period.active_inmates; },
        get totalPersonnel()   { return this.period.total_personnel; },
        get pendingTransfers() { return this.period.pending_transfers; },
        get visits()           { return this.period.visits; },
        get newAdmissions()    { return this.period.new_admissions; },
        get discharges()       { return this.period.discharges; },

        // Gender — computed with percentages
        get genderStats() {
            const g    = this.period.gender;
            const tot  = g.male + g.female + (g.unspecified || 0);
            const pct  = (n) => tot > 0 ? Math.round((n / tot) * 1000) / 10 : 0;
            return {
                male       : g.male,
                female     : g.female,
                unspecified: g.unspecified || 0,
                total      : tot,
                malePct    : pct(g.male),
                femalePct  : pct(g.female),
                unspecPct  : pct(g.unspecified || 0),
            };
        },

        // Custody — computed with percentages
        get custodyStats() {
            const items = this.period.custody;
            const tot   = items.reduce((s, i) => s + i.count, 0);
            return items.map(i => ({
                ...i,
                pct: tot > 0 ? Math.round((i.count / tot) * 1000) / 10 : 0,
            }));
        },

        // Facility data (not period-specific)
        get facilityStats() { return facilities; },

        // Format helpers
        fmt(n) { return n.toLocaleString(); },

        // ----------------------------------------------------------------
        // Chart initialization & update
        // ----------------------------------------------------------------
        initCharts() {
            this.$nextTick(() => {
                this.initAdmissionsChart();
                this.initGenderChart();
                this.initFacilityChart();
            });
        },

        initAdmissionsChart() {
            const canvas = document.getElementById('chart-admissions');
            if (!canvas) { return; }
            destroyChart('admissions');
            const p = this.period;
            charts['admissions'] = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels  : p.months,
                    datasets: [
                        {
                            label          : 'Admissions',
                            data           : p.admissions_chart,
                            backgroundColor: 'rgba(37, 99, 235, 0.75)',
                            borderColor    : '#2563EB',
                            borderWidth    : 1,
                            borderRadius   : 4,
                        },
                        {
                            label          : 'Discharges',
                            data           : p.discharges_chart,
                            backgroundColor: 'rgba(22, 163, 74, 0.6)',
                            borderColor    : '#16A34A',
                            borderWidth    : 1,
                            borderRadius   : 4,
                        },
                    ],
                },
                options: {
                    responsive       : true,
                    maintainAspectRatio: false,
                    interaction      : { mode: 'index', intersect: false },
                    plugins          : {
                        legend : { position: 'top', align: 'end' },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ` ${ctx.dataset.label}: ${ctx.raw}`,
                            },
                        },
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: { color: '#F1F5F9' },
                            ticks: { stepSize: 10 },
                        },
                        x: { grid: { display: false } },
                    },
                },
            });
        },

        initGenderChart() {
            const canvas = document.getElementById('chart-gender');
            if (!canvas) { return; }
            destroyChart('gender');
            const g = this.period.gender;
            const hasUnspec = (g.unspecified || 0) > 0;
            const labels = ['Male', 'Female', ...(hasUnspec ? ['Unspecified'] : [])];
            const data   = [g.male, g.female, ...(hasUnspec ? [g.unspecified] : [])];
            charts['gender'] = new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels,
                    datasets: [{
                        data,
                        backgroundColor: ['#2563EB', '#8B5CF6', '#94A3B8'],
                        borderColor    : '#FFFFFF',
                        borderWidth    : 3,
                        hoverOffset    : 6,
                    }],
                },
                options: {
                    responsive       : true,
                    maintainAspectRatio: false,
                    cutout           : '65%',
                    plugins: {
                        legend : { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const tot = ctx.dataset.data.reduce((s, v) => s + v, 0);
                                    const pct = tot > 0 ? ((ctx.raw / tot) * 100).toFixed(1) : 0;
                                    return ` ${ctx.label}: ${ctx.raw.toLocaleString()} (${pct}%)`;
                                },
                            },
                        },
                    },
                },
            });
        },

        initFacilityChart() {
            const canvas = document.getElementById('chart-facility');
            if (!canvas) { return; }
            destroyChart('facility');
            charts['facility'] = new Chart(canvas, {
                type: 'bar',
                data: {
                    labels  : facilities.map(f => f.name),
                    datasets: [
                        {
                            label          : 'Current Population',
                            data           : facilities.map(f => f.count),
                            backgroundColor: 'rgba(37, 99, 235, 0.75)',
                            borderColor    : '#2563EB',
                            borderWidth    : 1,
                            borderRadius   : 4,
                        },
                        {
                            label          : 'Capacity (fictional)',
                            data           : facilities.map(f => f.capacity),
                            backgroundColor: 'rgba(226, 232, 240, 0.6)',
                            borderColor    : '#CBD5E1',
                            borderWidth    : 1,
                            borderRadius   : 4,
                        },
                    ],
                },
                options: {
                    indexAxis        : 'y',
                    responsive       : true,
                    maintainAspectRatio: false,
                    interaction      : { mode: 'index', intersect: false },
                    plugins          : {
                        legend : { position: 'top', align: 'end' },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => ` ${ctx.dataset.label}: ${ctx.raw.toLocaleString()}`,
                            },
                        },
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: '#F1F5F9' },
                        },
                        y: { grid: { display: false } },
                    },
                },
            });
        },

        // Called when period selector changes
        changePeriod(period) {
            this.currentPeriod = period;
            this.$nextTick(() => {
                this.initAdmissionsChart();
                this.initGenderChart();
                // Facility chart doesn't change with period
            });
        },

        init() {
            this.initCharts();
        },
    };
});

Alpine.start();
