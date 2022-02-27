<?php

namespace App\Http\Controllers;
use Auth;
use App\User;
use App\barang;
use App\pesanan;
use Carbon\Carbon;
use App\pesananDetail;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $pesanans = pesanan::where('user_id', Auth::user()->id)->where('status', '!=',0)->get();

        return view('history.index', compact('pesanans'));
    }

    public function detail($id)
    {
        $pesanan = pesanan::where('id', $id)->first();
        $pesanan_details = pesananDetail::where('pesanan_id', $pesanan->id)->get();

        return view('history.detail', compact('pesanan', 'pesanan_details'));
    }
}
