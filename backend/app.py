from collector import Collector
from eventbus import eventbus

collector = Collector()

def handleAlert(alert):
    print("ALERT:", alert)

eventbus.subscribe("alert", handleAlert)

collector.publishTelemetry("router-1")
