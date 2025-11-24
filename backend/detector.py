from eventbus import eventbus

class BottleneckDetector:

    def detectAnomalies(self, kpi):
        alerts = []

        if kpi["cpu"] > 85:
            alerts.append("CPU overload")

        if kpi["latency_avg"] > 200:
            alerts.append("Very high latency")

        if kpi["packetLoss"] > 10:
            alerts.append("Heavy packet loss")

        if kpi["bandwidth"] < 5:
            alerts.append("Low bandwidth")

        return alerts

    def evaluateRules(self, kpi):
        alerts = self.detectAnomalies(kpi)
        if alerts:
            eventbus.publish("alert", {
                "device": kpi["device"],
                "alerts": alerts
            })


detector = BottleneckDetector()
eventbus.subscribe("kpi", detector.evaluateRules)
