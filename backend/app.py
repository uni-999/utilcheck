import time
from collector import Collector
from eventbus import eventbus

collector = Collector()

def handleAlert(alert):
    print("\n ALERT for", alert["device"])
    for item in alert["alerts"]:
        print(" •", item)
    print()

eventbus.subscribe("alert", handleAlert)

while True:
    collector.publishTelemetry("router-1")   # реальный девайс/IP
    time.sleep(5)
