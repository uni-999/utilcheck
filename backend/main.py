# main.py
from fastapi import FastAPI, WebSocket
from fastapi.middleware.cors import CORSMiddleware
from ws.metrics import websocket_metrics_endpoint

app = FastAPI(title="NetGuardian Metrics WS")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)


@app.websocket("/ws/metrics")
async def websocket_metrics(ws: WebSocket):
    await websocket_metrics_endpoint(ws)


@app.get("/")
async def root():
    return {"message": "NetGuardian metrics websocket server. Connect to /ws/metrics"}