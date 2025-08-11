<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\Fnb_order;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    //
    public function index()
    {
        $cart = DB::table('fnb_cart')
            ->join('fnb_menu', 'fnb_cart.fnb_menu_id', '=', 'fnb_menu.id')
            ->where('fnb_cart.user_id', Auth::id())
            ->select('fnb_cart.id', 'fnb_menu.name', 'fnb_menu.price', 'fnb_cart.quantity')
            ->get();

        return response()->json(
            $cart
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'fnb_menu_id' => 'required|exists:fnb_menu,id',
            'quantity' => 'required|integer|min:1',
        ]);

        DB::table('fnb_cart')->updateOrInsert(
        [
            'user_id' => Auth::id(),
            'fnb_menu_id' => $request->fnb_menu_id,
        ],
        [
            'quantity' => DB::raw('COALESCE(quantity, 0) + '.$request->quantity),
            'updated_at' => now(),
            'created_at' => now(),
        ]
    );

        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        DB::table('fnb_cart')->where('id', $id)->where('user_id', Auth::id())->delete();
        return response()->json(['success' => true]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'booking_id' => 'required|exists:bookings,id',
        ]);

        $cartItems = DB::table('fnb_cart')
            ->where('user_id', Auth::id())
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

        DB::beginTransaction();
        try {

            $order = Fnb_order::create([
                'booking_id' => $request->booking_id,
                'user_id' => Auth::id(),
                'status' => 'pending',
            ]);


            foreach ($cartItems as $item) {
                $menu = \App\Models\Fnb_menu::find($item->fnb_menu_id);
                \App\Models\FnbOrderItem::create([
                    'fnb_order_id' => $order->id,
                    'fnb_menu_id' => $item->fnb_menu_id,
                    'quantity' => $item->quantity,
                    'price' => $menu->price,
                ]);
            }


            DB::table('fnb_cart')->where('user_id', Auth::id())->delete();

            DB::commit();


            return response()->json([
                'success' => true,
                'order_id' => $order->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


}
