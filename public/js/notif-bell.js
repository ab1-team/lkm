document.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('notif-list');
    const badge = document.getElementById('notif-badge');
    if (!list) return;

    function muatNotifikasi() {
        fetch('/notifikasi/dropdown')
            .then(res => res.json())
            .then(data => {
                if (data.unread_count > 0) {
                    badge.textContent = data.unread_count;
                    badge.classList.remove('d-none');
                } else {
                    badge.classList.add('d-none');
                }

                if (data.items.length === 0) {
                    list.innerHTML = '<div class="text-center text-muted small py-3">Belum ada update</div>';
                    return;
                }

                let html = data.items.slice(0, 5).map(item => `
                    <div class="small border-bottom py-2">
                        <strong>${item.judul}</strong>
                        <div class="text-muted" style="font-size:.75rem">${item.tanggal}</div>
                    </div>
                `).join('');

                html += `
                    <div class="d-flex justify-content-between mt-2">
                        <a href="/notifikasi/timeline" class="small">Lihat semua</a>
                        <button type="button" class="btn btn-sm btn-link small p-0" id="btn-tandai-dibaca">Tandai sudah dibaca</button>
                    </div>
                `;

                list.innerHTML = html;

                document.getElementById('btn-tandai-dibaca')?.addEventListener('click', tandaiDibaca);
            });
    }

    function tandaiDibaca() {
        fetch('/notifikasi/tandai-dibaca', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        }).then(() => muatNotifikasi());
    }

    muatNotifikasi();
});