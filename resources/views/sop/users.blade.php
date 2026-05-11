@extends('layouts.base')

@section('content')
<style>
    .app-title {
        background-color: #6797a385; 
        padding: 20px; 
        border-radius: 8px; 
        margin-bottom: 10px; 
    }
.app-wrapper {
        display: flex; 
        align-items: center; 
    }
.app-heading {
        display: flex; 
        align-items: center; 
    }
.app-bg-icon {
        display: flex; 
        align-items: center; 
        justify-content: center; 
        width: 40px; 
        height: 40px; 
        background-color: #c0c4c505; 
        border-radius: 10%; 
        margin-right: 15px; 
    }
.app-text_fount {
        font-size: 14px; 
        color: #373636; 
        margin-top: 15px; 
    }
    .custom-button {
        width: 200px; 
        float: right; /* Tempatkan tombol di sebelah kanan */
        margin: 20px; 
        padding: 10px; 
        text-align: center; 
        background-color: #343a40; 
        color: white; 
        border: none; 
        border-radius: 5px; 
        cursor: pointer; 
    }
    .custom-button:hover {
        background-color: #495057; 
    }  
    .header {
    display: flex;
    align-items: flex-start;
    }

    .header img {
        width: 150px;
        /* Tambahkan margin kanan untuk memberikan jarak antara gambar dan teks */
        margin-right: 15px; 
    }

    .header h3 {
        margin: 40px;
        margin-left:100px; 
    }

    </style>
    <div class="app-main__inner"><br>
        <div class="app-title">
            <div class="row">
                <div class="col-md-3">
                    <div class="position-relative mb-3">
                        <label class="form-label"><b>{{$kec->sebutan_level_1}}</b></label>
                        <input autocomplete="off" type="text" name="" id=""
                            class="form-control" value="{{$dir->namadepan}}" readonly>
                        <small class="text-danger" id=""></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="position-relative mb-3">
                        <label for="" class="form-label"><b>{{$kec->sebutan_level_2}}</b></label>
                        <input autocomplete="off" type="text" name="" id="" value="{{$seke->namadepan}}" class="form-control" readonly>
                        <small class="text-danger" id=""></small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="position-relative mb-3">
                        <label for="" class="form-label"><b>{{$kec->sebutan_level_3}}</b></label>
                        <input autocomplete="off" type="text" name="" id="" value="{{$bend->namadepan}}" class="form-control" readonly>
                        <small class="text-danger" id=""></small> 
                    </div>
                </div>
                @if ($manaj)
                <div class="col-md-3">
                    <div class="position-relative mb-3">
                        <label for="" class="form-label"><b>Manajer</b></label>
                        <input autocomplete="off" type="text" name="" id="" value="{{$manaj->namadepan}}" class="form-control" readonly>
                    <small class="text-danger" id=""></small>  
                    </div>
                </div>
                @else
                <div class="col-md-3">
                    <div class="position-relative mb-3">
                        <label for="" class="form-label"><b>Manajer</b></label>
                        <input autocomplete="off" type="text" name="" id="" value="" class="form-control" readonly>
                    <small class="text-danger" id=""></small>  
                    </div>
                </div>
                @endif
            </div> <br>
            <div class="header">
                <span><img src="/assets/img/upe.png" class="mt-n-xlg"></span>
                <h3 align="center"><b>Daftar Users Penggunaan Aplikasi LKM <br>
                    {{ $kec->nama_lembaga_sort }}</b>
                </h3>               
            </div>
        </div>        
    </div>
@endsection