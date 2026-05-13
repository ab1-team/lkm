<style>
    .welcome-container {
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 50%, #f5f3ff 100%);
        border: 1px solid rgba(219, 234, 254, 0.7);
        border-radius: 20px;
        padding: 40px 32px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.05), 0 8px 10px -6px rgba(59, 130, 246, 0.03);
    }

    /* Decorative glowing spheres in background */
    .welcome-container::before {
        content: '';
        position: absolute;
        top: -10%;
        right: -10%;
        width: 150px;
        height: 150px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.15) 0%, rgba(59, 130, 246, 0) 70%);
        border-radius: 50%;
        z-index: 1;
    }

    .welcome-container::after {
        content: '';
        position: absolute;
        bottom: -15%;
        left: -5%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(139, 92, 246, 0.12) 0%, rgba(139, 92, 246, 0) 70%);
        border-radius: 50%;
        z-index: 1;
    }

    .welcome-content {
        position: relative;
        z-index: 2;
    }

    .welcome-icon-wrapper {
        width: 72px;
        height: 72px;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 24px;
        box-shadow: 0 8px 20px rgba(139, 92, 246, 0.25);
    }

    .welcome-icon-wrapper i {
        color: #ffffff;
        font-size: 1.8rem;
    }

    .welcome-title {
        font-family: 'Outfit', 'Inter', sans-serif;
        color: #1e293b;
        font-size: 1.5rem;
        font-weight: 800;
        line-height: 1.4;
        margin-bottom: 8px;
    }

    .welcome-subtitle {
        font-family: 'Outfit', 'Inter', sans-serif;
        color: #4f46e5;
        font-size: 1.15rem;
        font-weight: 700;
        margin-bottom: 16px;
    }

    .welcome-divider {
        width: 60px;
        height: 4px;
        background: linear-gradient(90deg, #3b82f6, #8b5cf6);
        border-radius: 2px;
        margin: 20px auto;
    }

    .welcome-instructions-list {
        list-style: none;
        padding-left: 0;
        margin: 24px auto;
        max-width: 520px;
        text-align: left;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .welcome-instructions-list li {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 0.925rem;
        color: #475569;
        line-height: 1.5;
    }

    .welcome-instructions-list li i {
        color: #10b981;
        font-size: 1rem;
        margin-top: 3px;
        background-color: rgba(16, 185, 129, 0.1);
        padding: 4px;
        border-radius: 50%;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .welcome-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background-color: rgba(59, 130, 246, 0.08);
        border: 1px solid rgba(59, 130, 246, 0.12);
        padding: 6px 14px;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 700;
        color: #2563eb;
        margin-top: 24px;
    }
</style>

<div class="active tab-pane" id="tab18">
    <div class="welcome-container text-center animated fadeIn">
        <div class="welcome-content">
            <!-- Icon Wrapper -->
            <div class="welcome-icon-wrapper">
                <i class="fa-solid fa-wand-magic-sparkles"></i>
            </div>

            <!-- Header -->
            <h2 class="welcome-title">Standar Operasional Prosedur (SOP)</h2>
            <div class="welcome-subtitle">{{ $kec->nama_lembaga_sort }}</div>

            <div class="welcome-divider"></div>

            <!-- Instructions -->
            <ul class="welcome-instructions-list">
                <li>
                    <i class="fa-solid fa-check"></i>
                    <span>Sebagai acuan pokok dan dasar wewenang penggunaan aplikasi LKM Anda secara profesional.</span>
                </li>
                <li>
                    <i class="fa-solid fa-check"></i>
                    <span>Silakan sesuaikan seluruh parameter SOP secara berkala menggunakan <strong>menu navigasi di
                            sebelah kiri Anda</strong>.</span>
                </li>
                <li>
                    <i class="fa-solid fa-check"></i>
                    <span>Gunakanlah wewenang akses administratif Anda secara bijaksana, tertib, dan penuh tanggung
                        jawab.</span>
                </li>
            </ul>

            <!-- Badge Signature -->
            {{-- <div class="welcome-badge">
                <i class="fa-solid fa-heart text-danger"></i>
                <span>Salam hangat, <strong></strong></span>
            </div> --}}
        </div>
    </div>
</div>
