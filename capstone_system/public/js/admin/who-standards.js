function showStandardData(type, data) {
    const modal = new bootstrap.Modal(document.getElementById('standardModal'));
    const content = document.getElementById('standardDataContent');
    let title = type.replace('-', ' ').toUpperCase();
    document.getElementById('standardModalLabel').textContent = `WHO Standard: ${title}`;
    let html = '<div class="table-responsive"><table class="table table-striped table-sm">';
    html += '<thead><tr>';
    if (data.data && data.data.length > 0) {
        Object.keys(data.data[0]).forEach(key => {
            html += `<th>${key.replace('_', ' ').toUpperCase()}</th>`;
        });
        html += '</tr></thead><tbody>';
        data.data.slice(0, 50).forEach(row => {
            html += '<tr>';
            Object.values(row).forEach(value => {
                html += `<td>${value}</td>`;
            });
            html += '</tr>';
        });
        if (data.data.length > 50) {
            html += `<tr><td colspan="${Object.keys(data.data[0]).length}" class="text-center text-muted">
                <em>Showing first 50 of ${data.data.length} records</em>
            </td></tr>`;
        }
    } else {
        html += '<th>No Data</th></tr></thead><tbody>';
        html += '<tr><td>No standard data available</td></tr>';
    }
    html += '</tbody></table></div>';
    content.innerHTML = html;
    modal.show();
}
