import asyncio
import json
from fastapi import WebSocket, WebSocketDisconnect
from metrics.collector import MetricsProducer
from config import WS_SEND_INTERVAL, PING_TARGET


async def websocket_metrics_endpoint(ws: WebSocket):
    await ws.accept()
    producer = MetricsProducer()

    try:
        while True:
            metrics = await producer.gather_all(PING_TARGET, WS_SEND_INTERVAL)
            payload = {
                "bandwidth": metrics.get("bandwidth", 0.0),
                "latency": metrics.get("latency", 0.0) or 0.0,
                "loss": metrics.get("loss", 0.0) or 0.0,
                "cpu": metrics.get("cpu", 0.0),
                "memory": metrics.get("memory"),
            }
            await ws.send_text(json.dumps(payload))
            await asyncio.sleep(WS_SEND_INTERVAL)
    except WebSocketDisconnect:
        return
    except Exception:
        try:
            await ws.close()
        except Exception:
            pass
        return