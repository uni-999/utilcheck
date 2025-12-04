# metrics/ping.py
import asyncio
import platform
import shutil
import re
from typing import Dict, Optional


def _ping_command_args(target: str, count: int = 4) -> list[str]:
    system = platform.system().lower()
    if system == "windows":
        return ["ping", "-n", str(count), target]
    else:
        return ["ping", "-c", str(count), target]


async def get_ping_stats(target: str, count: int = 4) -> Dict[str, Optional[float]]:
    if shutil.which("ping") is None:
        return {"latency_ms": None, "packet_loss_pct": None}

    args = _ping_command_args(target, count)
    proc = await asyncio.create_subprocess_exec(
        *args, stdout=asyncio.subprocess.PIPE, stderr=asyncio.subprocess.PIPE
    )
    stdout, stderr = await proc.communicate()
    out = stdout.decode(errors="ignore")
    if not out:
        return {"latency_ms": None, "packet_loss_pct": None}

    latency = None
    loss = None

    # Packet loss parsing
    try:
        if "packet loss" in out:
            m = re.search(r"(\d+(?:\.\d+)?)%\s*packet loss", out)
            if m:
                loss = float(m.group(1))
        elif "Lost = " in out:
            m = re.search(r"\((\d+(?:\.\d+)?)% loss\)", out)
            if m:
                loss = float(m.group(1))
    except Exception:
        loss = None

    try:
        m = re.search(r"rtt .* = .*?/([\d.]+?)/", out)
        if m:
            latency = float(m.group(1))
        else:
            m = re.search(r"round-trip .* = .*?/([\d.]+?)/", out)
            if m:
                latency = float(m.group(1))
            else:
                m = re.search(r"Average = (\d+)(?:ms)?", out)
                if m:
                    latency = float(m.group(1))
    except Exception:
        latency = None

    return {"latency_ms": latency, "packet_loss_pct": loss}