@extends('layouts.base')

@section('content')
    <div class="app-main__inner">
        <div class="main-card mb-3 card">
            <div class="card-body p-3" id="SelectIndividu"></div>
        </div>
        <div class="main-card mb-3 card">
            <div class="mt-4 pt-1" id="RegisterProposal"></div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $.get('/daftar_individu?id_angg={{ $id_angg }}', async (result) => {
            await $('#SelectIndividu').html(result)

            var id_angg = $('#individu').val()
            formRegister(id_angg)
        })

        $(document).on('change', '#individu', function(e) {
            e.preventDefault()

            var id_angg = $(this).val()
            formRegister(id_angg)
        })

        $(document).on('click', '#SimpanProposal', function(e) {
            e.preventDefault()
            $('small').html('')

            var form = $('#FormRegisterProposal')

            var saPokok = $('#sistem_angsuran_pokok').val()
            var saJasa = $('#sistem_angsuran_jasa').val()

            function submitProposal() {
                $.ajax({
                    type: 'post',
                    url: form.attr('action'),
                    data: form.serialize(),
                    success: function(result) {
                        if (result.success === false) {
                            Swal.fire('Peringatan', result.msg || 'Terjadi kesalahan', 'warning')
                            return
                        }

                        Swal.fire('Berhasil', result.msg, 'success').then(() => {
                            window.location.href = '/detail_i/' + result.id
                        })
                    },
                    error: function(result) {
                        const respons = result.responseJSON;

                        if (respons && respons.msg && (respons.errors || []).length === 0 && respons.success === false) {
                            Swal.fire('Peringatan', respons.msg, 'warning')
                            return
                        }

                        Swal.fire('Error', 'Cek kembali input yang anda masukkan', 'error')
                        $.map(respons, function(res, key) {
                            $('#' + key).parent('.input-group.input-group-static').addClass(
                                'is-invalid')
                            $('#msg_' + key).html(res)
                        })
                    }
                })
            }

            if (saPokok && saJasa && saPokok !== saJasa) {
                var pok = $('#sistem_angsuran_pokok option:selected').text().trim()
                var jas = $('#sistem_angsuran_jasa option:selected').text().trim()
                Swal.fire({
                    title: 'Sistem Angsuran Berbeda',
                    html: 'Sistem angsuran pokok dan sistem angsuran jasa berbeda:<br><br>' +
                        '<b>Pokok:</b> ' + pok + '<br>' +
                        '<b>Jasa:</b> ' + jas + '<br><br>' +
                        'Apakah anda yakin akan menyimpan seperti ini?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, Simpan',
                    cancelButtonText: 'Batal',
                }).then((r) => {
                    if (r.isConfirmed) submitProposal()
                })
                return
            }

            submitProposal()
        })

        function formRegister(nia) {
            $.get('/register_proposal_i/' + nia, async (result) => {
                await $('#RegisterProposal').html(result)
            })
        }
    </script>
@endsection
