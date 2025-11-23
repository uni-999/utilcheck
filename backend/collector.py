from eventbus import eventbus

class Collector:
    def pollSNMP(self, device):
        return {"device": device, "latency": 10, "cpu": 40}

    def sendPing(self, device):
        return {"device": device, "packetLoss": 0, "latency": 15}

    def queryHTTPAPI(self, device):
        return {"device": device, "bandwidth": 120}

    def publishTelemetry(self, device):
        data = {
            **self.pollSNMP(device),
            **self.sendPing(device),
            **self.queryHTTPAPI(device),
        }
        eventbus.publish("telemetry", data)
