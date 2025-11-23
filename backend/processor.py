from storage import timeseries
from eventbus import eventbus

class KPIProcessor:
    def computeKPIs(self, data):
        kpi = {
            "device": data["device"],
            "latency_avg": (data["latency"] + data["latency"]) / 2,
            "bandwidth": data.get("bandwidth", 0),
            "cpu": data.get("cpu", 0),
            "packetLoss": data.get("packetLoss", 0),
        }
        return kpi

    def updateMetrics(self, kpi):
        timeseries.writeMetric(kpi)

    def handleTelemetry(self, data):
        kpi = self.computeKPIs(data)
        self.updateMetrics(kpi)
        eventbus.publish("kpi", kpi)


processor = KPIProcessor()
eventbus.subscribe("telemetry", processor.handleTelemetry)
