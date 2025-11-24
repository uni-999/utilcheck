class TimeSeriesDB:
    def __init__(self):
        self.metrics = []

    def writeMetric(self, record):
        self.metrics.append(record)

    def queryMetrics(self, device=None, limit=10):
        if not device:
            return self.metrics[-limit:]
        filtered = [m for m in self.metrics if m["device"] == device]
        return filtered[-limit:]


timeseries = TimeSeriesDB()
