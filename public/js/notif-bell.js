document.addEventListener('DOMContentLoaded', function () {
    const list = document.getElementById('notif-list');
    const badge = document.getElementById('notif-badge');
    if (!list) return;

    function bindTandaiDibaca() {
        document.getElementById('btn-tandai-dibaca')?.addEventListener('click', tandaiDibaca);
    }

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
                    list.innerHTML = '<div class="py-4">Tidak ada notifikasi.</div>';
                    return;
                }

                let html = data.items.slice(0, 5).map(item => `
                    <div class="notif-item px-3 py-2 border-bottom text-start">
                        <strong class="d-block text-dark">${item.judul}</strong>
                        <div class="text-muted" style="font-size:.75rem">${item.tanggal}</div>
                    </div>
                `).join('');

                list.innerHTML = html;
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
    bindTandaiDibaca();
});
