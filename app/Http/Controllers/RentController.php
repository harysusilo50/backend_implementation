<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\RentBook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RentController extends Controller
{
    public function get_rent_book()
    {
        $data = RentBook::latest()
            ->paginate()
            ->withQueryString();
        if ($data->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Berhasil mendapatkan data Buku yang dipinjam ',
            'data' => $data
        ], 200);
    }
    public function get_user_rent_book($id)
    {
        $data = RentBook::with('user', 'book')->where('user_id', $id)
            ->latest()
            ->paginate()
            ->withQueryString();

        if ($data->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Berhasil mendapatkan data Buku yang dipinjam oleh user dengan id: ' . $id,
            'data' => $data
        ], 200);
    }

    public function user_rent_book(Request $request, $id)
    {
        $book = Book::find($id);
        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Data Buku tidak ditemukan',
            ], 404);
        }
        $check_if_exist = RentBook::where(['book_id' => $id, 'user_id' => Auth::id()])->first();
        if ($check_if_exist) {
            return response()->json([
                'success' => false,
                'message' => 'Buku sudah dipinjam pada : ' . $check_if_exist->rent_date,
            ], 402);
        }
        try {
            DB::beginTransaction();
            $data = new RentBook();
            $data->book_id = $book->id;
            $data->user_id = Auth::id();
            $data->rent_date = now();
            $data->save();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Berhasil melakukan peminjaman buku : ' .  $book->book_name,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 409);
        }
    }
}
