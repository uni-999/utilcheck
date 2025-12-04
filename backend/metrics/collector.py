# metrics/collector.py
import asyncio
import psutil
from typing import Optional, Dict
from .ping import get_ping_stats
from config import PING_TARGET, WS_SEND_INTERVAL


class MetricsProducer:
    def __init__(self):
        self._last_net = psutil.net_io_counters()
        self._last_time = asyncio.get_event_loop().time()

    def collect_cpu(self) -> float:
        try:
            return psutil.cpu_percent(interval=None)
        except Exception:
            return 0.0

    def collect_memory(self) -> float:
        try:
            return psutil.virtual_memory().percent
        except Exception:
            return 0.0

    def collect_bandwidth(self, elapsed: float) -> Optional[float]:
        try:
            now = psutil.net_io_counters()
            sent_delta = now.bytes_sent - self._last_net.bytes_sent
            recv_delta = now.bytes_recv - self._last_net.bytes_recv
            total_bytes = max(0, sent_delta + recv_delta)
            mbps = (total_bytes * 8) / 1_000_000 / max(elapsed, 1e-6)
            self._last_net = now
            self._last_time = asyncio.get_event_loop().time()
            return round(mbps, 2)
        except Exception:
            return None

    async def gather_all(self, ping_target: str = PING_TARGET, interval_seconds: float = WS_SEND_INTERVAL) -> Dict:
        cpu = self.collect_cpu()
        mem = self.collect_memory()
        now_t = asyncio.get_event_loop().time()
        elapsed = max(0.001, now_t - self._last_time)
        bw = self.collect_bandwidth(elapsed)
        ping = await get_ping_stats(ping_target, count=3)
        latency = ping.get("latency_ms")
        loss = ping.get("packet_loss_pct")
        return {
            "cpu": round(cpu, 1) if cpu is not None else None,
            "memory": round(mem, 1) if mem is not None else None,
            "bandwidth": bw if bw is not None else 0.0,
            "latency": round(latency, 1) if latency is not None else None,
            "loss": round(loss, 1) if loss is not None else None,
        }