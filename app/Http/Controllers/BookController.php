<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{

    private $id;
    private $role;

    public function __construct()
    {
        $this->role = Auth::guard('api')->user()->role ?? '-';
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        if (!$search) {
            $data = Book::where('book_name', $search)
                ->orWhere('author', 'LIKE', "%$search%")
                ->latest()
                ->paginate()
                ->withQueryString();
        }
        $data = Book::latest()
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
            'message' => 'Berhasil mendapatkan data Buku',
            'data' => $data
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($this->role != 'admin') {
            return response()->json(['success' => false, 'message' => 'Anda Tidak Diizinkan'], 403);
        }

        $validator = Validator::make($request->all(), [
            'book_name' => 'required',
            'category'     => 'required',
            'synopsis'     => 'required',
            'author'     => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();
            $book = new Book();
            $book->book_name = $request->book_name;
            $book->category = $request->category;
            $book->synopsis = $request->synopsis;
            $book->author = $request->author;
            $book->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Berhasil menambah buku : ' .  $request->book_name,
            ], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 409);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Book::find($id);
        if (!$data) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }
        return response()->json([
            'success' => true,
            'message' => 'Berhasil mendapatkan data Buku:' . $data->book_name,
            'data' => $data
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if ($this->role != 'admin') {
            return response()->json(['success' => false, 'message' => 'Anda Tidak Diizinkan'], 403);
        }

        try {
            DB::beginTransaction();
            $book = Book::find($id);

            if (!$book) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                ], 404);
            }

            $book->book_name = $request->book_name;
            $book->category = $request->category;
            $book->synopsis = $request->synopsis;
            $book->author = $request->author;
            $book->save();

            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Berhasil mengubah buku dengan id : ' .  $id,
                'data' => $book
            ], 201);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], 409);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if ($this->role != 'admin') {
            return response()->json(['success' => false, 'message' => 'Anda Tidak Diizinkan'], 403);
        }

        $book = Book::find($id);
        if (!$book) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan',
            ], 404);
        }
        try {
            DB::beginTransaction();
            $book->delete();
            DB::commit();
            return response()->json([
                'success' => true,
                'message' => 'Berhasil menghapus data buku',
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
