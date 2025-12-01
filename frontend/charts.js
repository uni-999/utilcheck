if (document.querySelector('.main-content')) {
    function generateLabels() {
        const labels = [];
        const now = new Date();
        for (let i = 10; i >= 0; i--) {
            const time = new Date(now - i * 60000);
            labels.push(time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
        }
        return labels;
    }

    function rand(base, range) {
        return base + (Math.random() * range - range / 2);
    }

    const bw = new Chart(document.getElementById('bandwidthChart').getContext('2d'), {
        type: 'line',
        data: { labels: generateLabels(), datasets: [{ label: 'Mbps', data: Array(11).fill().map(() => rand(150, 80)), borderColor: '#4facfe', backgroundColor: 'rgba(79,172,254,0.1)', tension: 0.3, fill: true }] },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { min: 0, max: 300 } }, plugins: { legend: { display: false } } }
    });

    const lat = new Chart(document.getElementById('latencyChart').getContext('2d'), {
        type: 'line',
        data: { labels: generateLabels(), datasets: [{ label: 'ms', data: Array(11).fill().map(() => rand(30, 20)), borderColor: '#00f2fe', tension: 0.3, fill: false }] },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { min: 0, max: 100 } }, plugins: { legend: { display: false } } }
    });

    const loss = new Chart(document.getElementById('lossChart').getContext('2d'), {
        type: 'bar',
        data: { labels: generateLabels(), datasets: [{ label: '%', data: Array(11).fill().map(() => Math.max(0, rand(1.5, 2).toFixed(1))), backgroundColor: '#f59e0b', borderRadius: 4 }] },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { min: 0, max: 5 } }, plugins: { legend: { display: false } } }
    });

    const cpu = new Chart(document.getElementById('cpuChart').getContext('2d'), {
        type: 'line',
        data: { labels: generateLabels(), datasets: [{ label: '%', data: Array(11).fill().map(() => rand(50, 40)), borderColor: '#2563eb', backgroundColor: 'rgba(37,99,235,0.15)', tension: 0.3, fill: true }] },
        options: { responsive: true, maintainAspectRatio: false, scales: { y: { min: 0, max: 100 } }, plugins: { legend: { display: false } } }
    });

    setInterval(() => {
        const t = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        const upd = (c, v) => {
            c.data.labels.push(t); c.data.labels.shift();
            c.data.datasets[0].data.push(v); c.data.datasets[0].data.shift();
            c.update('none');
        };
        upd(bw, rand(150, 100));
        upd(lat, Math.max(5, rand(30, 40)));
        upd(loss, Math.max(0, rand(1.5, 2).toFixed(1)));
        upd(cpu, Math.min(100, Math.max(0, rand(50, 60))));
    }, 2000);
}