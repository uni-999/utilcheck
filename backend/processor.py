from storage import timeseries
from eventbus import eventbus
import statistics


class KPIProcessor:

    def computeKPIs(self, data):
        """
        Производим нормальную агрегацию:
        - latency_avg: средняя задержка за последние N значений
        - cpu
        - bandwidth
        - packetLoss
        """

        history = timeseries.queryMetrics(device=data["device"], limit=10)

        latencies = [m["latency"] for m in history] + [data["latency"]]
        latency_avg = statistics.mean(latencies)

        return {
            "device": data["device"],
            "latency_avg": latency_avg,
            "cpu": data["cpu"],
            "bandwidth": data["bandwidth"],
            "packetLoss": data["packetLoss"]
        }

    def updateMetrics(self, kpi):
        timeseries.writeMetric(kpi)

    def handleTelemetry(self, data):
        kpi = self.computeKPIs(data)
        self.updateMetrics(kpi)
        eventbus.publish("kpi", kpi)


processor = KPIProcessor()
eventbus.subscribe("telemetry", processor.handleTelemetry)
