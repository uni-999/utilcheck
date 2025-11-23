import subprocess
import statistics
import requests
from pysnmp.hlapi import *
from eventbus import eventbus


class Collector:

    def pollSNMP(self, device):
        """
        Получаем реальные значения по SNMP:
        - CPU load
        - Interface in/out bytes
        """
        cpu_oid = "1.3.6.1.4.1.2021.11.10.0"  # примерный OID CPU idle
        bandwidth_oid = "1.3.6.1.2.1.2.2.1.10.1"  # inOctets

        cpu_value = self.snmp_get(device, cpu_oid)
        bandwidth_value = self.snmp_get(device, bandwidth_oid)

        if cpu_value is None:
            cpu_value = 0

        return {
            "device": device,
            "cpu": 100 - int(cpu_value),         # CPU load %
            "bandwidth": int(bandwidth_value or 0) / 1024
        }

    def snmp_get(self, device, oid):
        try:
            iterator = getCmd(
                SnmpEngine(),
                CommunityData('public'),
                UdpTransportTarget((device, 161), timeout=0.5, retries=1),
                ContextData(),
                ObjectType(ObjectIdentity(oid))
            )
            errorIndication, errorStatus, errorIndex, varBinds = next(iterator)
            if errorIndication or errorStatus:
                return None
            return varBinds[0][1]
        except:
            return None

    def sendPing(self, device):
        """
        Запуск 4 ping пакетов, считаем среднее время и потери
        """
        try:
            result = subprocess.run(
                ["ping", "-c", "4", device],
                stdout=subprocess.PIPE,
                stderr=subprocess.PIPE,
                text=True
            )

            output = result.stdout

            latencies = []
            for line in output.split("\n"):
                if "time=" in line:
                    ms = float(line.split("time=")[1].split(" ")[0])
                    latencies.append(ms)

            loss_line = [l for l in output.split("\n") if "packet loss" in l][0]
            loss = int(loss_line.split("%")[0].split()[-1])

            return {
                "device": device,
                "latency": statistics.mean(latencies) if latencies else 0,
                "packetLoss": loss
            }

        except:
            return {"device": device, "latency": 0, "packetLoss": 100}

    def queryHTTPAPI(self, device):
        """
        Например: http://router/api/metrics → {"bandwidth": 120, ...}
        """
        try:
            response = requests.get(f"http://{device}/api/metrics", timeout=0.5)
            if response.status_code == 200:
                return response.json()
        except:
            pass

        return {"device": device, "bandwidth": 0}

    def publishTelemetry(self, device):
        """
        Собираем все метрики от разных источников в один dict
        """
        snmp = self.pollSNMP(device)
        ping = self.sendPing(device)
        http = self.queryHTTPAPI(device)

        merged = {**snmp, **ping, **http}

        eve
