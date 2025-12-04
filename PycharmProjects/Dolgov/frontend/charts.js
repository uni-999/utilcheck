if (document.querySelector('.main-content')) {

    const socket = new WebSocket("ws://localhost:8000/ws/metrics");

    let latestMetrics = {
        bandwidth: 0,
        latency: 0,
        loss: 0,
        cpu: 0
    };

    socket.onopen = () => {
        console.log("WS CONNECTED");
    };

    socket.onerror = (e) => {
        console.error("WS ERROR:", e);
    };

    socket.onmessage = (event) => {
        console.log("WS DATA:", event.data);
        latestMetrics = JSON.parse(event.data);
    };

    // ------------------ Helpers -----------------------
    function generateLabels() {
        const labels = [];
        const now = new Date();
        for (let i = 10; i >= 0; i--) {
            const time = new Date(now - i * 60000);
            labels.push(time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }));
        }
        return labels;
    }

    // ------------------ CHARTS ------------------------

    const bw = new Chart(document.getElementById('bandwidthChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: generateLabels(),
            datasets: [{
                label: 'Mbps',
                data: Array(11).fill(0),
                borderColor: '#4facfe',
                backgroundColor: 'rgba(79,172,254,0.1)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { min: 0, max: 300 } },
            plugins: { legend: { display: false } }
        }
    });

    const lat = new Chart(document.getElementById('latencyChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: generateLabels(),
            datasets: [{
                label: 'ms',
                data: Array(11).fill(0),
                borderColor: '#00f2fe',
                tension: 0.3,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { min: 0, max: 200 } },
            plugins: { legend: { display: false } }
        }
    });

    const loss = new Chart(document.getElementById('lossChart').getContext('2d'), {
        type: 'bar',
        data: {
            labels: generateLabels(),
            datasets: [{
                label: '%',
                data: Array(11).fill(0),
                backgroundColor: '#f59e0b',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { min: 0, max: 10 } },
            plugins: { legend: { display: false } }
        }
    });

    const cpu = new Chart(document.getElementById('cpuChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: generateLabels(),
            datasets: [{
                label: '%',
                data: Array(11).fill(0),
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37,99,235,0.15)',
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { min: 0, max: 100 } },
            plugins: { legend: { display: false } }
        }
    });

    // ------------------ UPDATE LOOP ---------------------
    setInterval(() => {
        const t = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

        const upd = (chart, value) => {
            chart.data.labels.push(t);
            chart.data.labels.shift();
            chart.data.datasets[0].data.push(value);
            chart.data.datasets[0].data.shift();
            chart.update('none');
        };

        upd(bw, latestMetrics.bandwidth);
        upd(lat, latestMetrics.latency);
        upd(loss, latestMetrics.loss);
        upd(cpu, latestMetrics.cpu);

    }, 2000);
}
