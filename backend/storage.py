class TimeSeriesDB:
    def __init__(self):
        self.metrics = []

    def writeMetric(self, record):
        self.metrics.append(record)

    def queryMetrics(self):
        return self.metrics


timeseries = TimeSeriesDB()
