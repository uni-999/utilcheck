import { getKPIs } from "./api.js";

function renderKPIs(data) {
    const container = document.getElementById("kpi-container");
    container.innerHTML = "";

    if (data.length === 0) {
        container.textContent = "No KPI data received";
        return;
    }

    data.forEach(k => {
        const div = document.createElement("div");
        div.className = "kpi-card";

        const cpuState = k.cpu > 80 ? "alert" : "ok";
        const latencyState = k.latency_avg > 120 ? "alert" : "ok";
        const lossState = k.packetLoss > 5 ? "alert" : "ok";

        div.innerHTML = `
            <strong>${k.device}</strong><br><br>
            <div>Latency: <span class="${latencyState}">${k.latency_avg} ms</span></div>
            <div>CPU: <span class="${cpuState}">${k.cpu} %</span></div>
            <div>Bandwidth: ${k.bandwidth} Mbps</div>
            <div>Packet Loss: <span class="${lossState}">${k.packetLoss} %</span></div>
        `;

        container.appendChild(div);
    });
}

async function refresh() {
    const data = await getKPIs();
    renderKPIs(data);
}

refresh();

setInterval(refresh, 5000);
