from eventbus import eventbus

class BottleneckDetector:
    def detectAnomalies(self, kpi):
        alerts = []
        if kpi["cpu"] > 80:
            alerts.append("CPU overload")
        if kpi["latency_avg"] > 100:
            alerts.append("High latency")
        if kpi["packetLoss"] > 5:
            alerts.append("Packet loss detected")
        return alerts

    def evaluateRules(self, kpi):
        alerts = self.detectAnomalies(kpi)
        if alerts:
            eventbus.publish("alert", {"device": kpi["device"], "alerts": alerts})


detector = BottleneckDetector()
eventbus.subscribe("kpi", detector.evaluateRules)
