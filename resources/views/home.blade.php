@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        @foreach ($barangs as $barang)
            <div class="col-md-4">
                <div class="card">
                    <img class="card-img-top" src="{{ url('uploads') }}/{{ $barang->gambar }}" alt="Card image cap">
                    <div class="card-body">
                      <h5 class="card-title">{{ $barang->nama_barang }}</h5>
                      <p class="card-text">
                          <strong>Harga :</strong> Rp. {{ number_format($barang->harga) }} <br>
                          <strong>Stok :</strong> {{ $barang->stok }} <br>
                          <hr>
                          <strong>Keterangan :</strong> <br>
                          {{ $barang->keterangan }}
                      </p>
                      <a href="{{ url('pesan') }}/{{ $barang->id }}" class="btn btn-primary"><i class="fa fa-shopping-cart"></i> Pesan</a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
