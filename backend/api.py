from http.server import BaseHTTPRequestHandler, HTTPServer
import json
from storage import timeseries

class Handler(BaseHTTPRequestHandler):
    def do_GET(self):
        if self.path == "/kpi":
            data = timeseries.queryMetrics(limit=20)
            self.send_response(200)
            self.send_header("Content-Type", "application/json")
            self.send_header("Access-Control-Allow-Origin", "*")
            self.end_headers()
            self.wfile.write(json.dumps(data).encode())
        else:
            self.send_error(404)

server = HTTPServer(("0.0.0.0", 8000), Handler)
print("API server running on http://localhost:8000")
server.serve_forever()
