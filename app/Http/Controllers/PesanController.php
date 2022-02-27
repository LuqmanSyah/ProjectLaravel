<?php

namespace App\Http\Controllers;
use Auth;
use App\User;
use App\barang;
use App\pesanan;
use Carbon\Carbon;
use App\pesananDetail;
use Illuminate\Http\Request;

class PesanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index($id)
    {
        $barang = barang::where('id', $id)->first();

        return view('pesan.index', compact('barang'));
    }

    public function pesan(Request $request, $id)
    {
        $barang = barang::where('id', $id)->first();
        $tanggal = Carbon::now();

        // validasi apakah melebihi stok
        if($request->jumlah_pesan > $barang->stok)
        {
            return redirect('pesan/'.$id);
        }
        // cek validasi
        $cek_pesanan = pesanan::where('user_id', Auth::user()->id)->where('status', 0)->first();

        // simpan ke database pesanan
        if(empty($cek_pesanan))
        {

            $pesanan = new pesanan;
            $pesanan->user_id = Auth::user()->id;
            $pesanan->tanggal = $tanggal;
            $pesanan->status = 0;
            $pesanan->jumlah_harga = 0;
            $pesanan->kode = mt_rand(100, 999);
            $pesanan->save();

        }
        // simpan ke database pesanan detail
        $pesanan_baru = pesanan::where('user_id', Auth::user()->id)->where('status', 0)->first();

        // cek pesanan detail
        $cek_pesanan_detail = pesananDetail::where('barang_id', $barang->id)->where('pesanan_id', $pesanan_baru->id)->first();
        if(empty($cek_pesanan_detail))
        {

            $pesanan_detail = new pesananDetail;
            $pesanan_detail->barang_id = $barang->id;
            $pesanan_detail->pesanan_id = $pesanan_baru->id;
            $pesanan_detail->jumlah = $request->jumlah_pesan;
            $pesanan_detail->jumlah_harga = $barang->harga*$request->jumlah_pesan;
            $pesanan_detail->save();

        }else
        {
            $pesanan_detail = pesananDetail::where('barang_id', $barang->id)->where('pesanan_id', $pesanan_baru->id)->first();
            $pesanan_detail->jumlah = $pesanan_detail->jumlah+$request->jumlah_pesan;

            // harga sekarang
            $harga_pesanan_detail_baru = $barang->harga*$request->jumlah_pesan;
            $pesanan_detail->jumlah_harga = $pesanan_detail->jumlah_harga+$harga_pesanan_detail_baru;
            $pesanan_detail->update();
        }

        // jumlah total
        $pesanan = pesanan::where('user_id', Auth::user()->id)->where('status', 0)->first();
        $pesanan ->jumlah_harga = $pesanan ->jumlah_harga+$barang->harga*$request->jumlah_pesan;
        $pesanan->update();


        \RealRashid\SweetAlert\Facades\Alert::success('Succsess', 'Berhasil Masuk Keranjang');
        return redirect('check-out');

    }

    public function check_out()
    {
        $pesanan = pesanan::where('user_id', Auth::user()->id)->where('status', 0)->first();
        if(empty($pesanan)) 
        {
            return view('pesan.check_out');
        }
        $pesanan_details = pesananDetail::where('pesanan_id', $pesanan->id)->get();
        return view('pesan.check_out', compact('pesanan', 'pesanan_details'));
    }

    public function delete($id)
    {
        $pesanan_detail = pesananDetail::where('id', $id)->first();

        $pesanan = pesanan::where('id', $pesanan_detail->pesanan_id)->first();
        $pesanan->jumlah_harga = $pesanan->jumlah_harga-$pesanan_detail->jumlah_harga;
        $pesanan->update();

        $pesanan_detail->delete();

        \RealRashid\SweetAlert\Facades\Alert::error('Hapus', 'Pesanan Sukses Dihapus');
        return redirect('check-out');
    }

    public function konfirmasi()
    {
        $user = User::where('id', Auth::user()->id)->first();

        if(empty($user->alamat))
        {
            \RealRashid\SweetAlert\Facades\Alert::error('Error', 'Identitas Harap dilengkapi');
            return redirect('profile');
        }

        if(empty($user->nohp))
        {
            \RealRashid\SweetAlert\Facades\Alert::error('Error', 'Identitas Harap dilengkapi');
            return redirect('profile');
        }

        $pesanan = pesanan::where('user_id', Auth::user()->id)->where('status', 0)->first();
        $pesanan_id = $pesanan->id;
        $pesanan->status = 1;
        $pesanan->update();

        $pesanan_details = pesananDetail::where('pesanan_id', $pesanan_id)->get();
        foreach($pesanan_details as $pesanan_detail)
        {
            $barang = barang::where('id', $pesanan_detail->barang_id)->first();
            $barang->stok = $barang->stok-$pesanan_detail->jumlah;
            $barang->update();
        }

        \RealRashid\SweetAlert\Facades\Alert::success('Success', 'Pesanan Sukses Check Out Silahkan Lanjutkan Proses Pembayaran');
        return redirect('history/'.$pesanan_id);
    }
}
