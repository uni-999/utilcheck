export async function getKPIs() {
    try {
        const response = await fetch("http://localhost:8000/kpi");
        return await response.json();
    } catch (err) {
        console.error("API error:", err);
        return [];
    }
}
