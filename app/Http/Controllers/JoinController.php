<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class MainController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    //use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function invoices(Request $request)
    {

        $fromDate = Carbon::parse($request->from);
        $toDate = Carbon::parse($request->to);

        $invoices = DB::table('users as u1')
            ->join('users as u2', 'u1.referred_by', '=', 'u2.id')
            ->Join('orders', 'orders.purchaser_id', '=', 'u1.id')
            ->Join('user_category as Ucat', 'Ucat.user_id', '=', 'u1.referred_by')
            ->Join('categories as cat', 'cat.id', '=', 'Ucat.category_id')
            ->select(
                DB::raw("CONCAT(u1.first_name,' ',u1.last_name) as Purchaser"),
                DB::raw("CONCAT(u2.first_name,' ',u2.last_name) as Distributor"),
                'orders.*',
                'cat.name as referrer_category'
            )

            ->when(!empty($request->search), function ($query) use ($request) {
                return $query->where('u2.last_name', 'like', "%{$request->search}%")
                    ->orWhere('u2.first_name', 'like', "%{$request->search}%");
            })

            ->whereBetween('orders.order_date', [$fromDate, $toDate])

            ->get();

        return ['data' => $invoices, 'count' => sizeof($invoices)];
    }

    public function invoiceDetails($id)
    {
        $details = DB::table('order_items as ord')
            ->where('order_id', $id)
            ->Join('orders', 'orders.id', '=', 'ord.order_id')
            ->join('products as prd', 'prd.id', '=', 'ord.product_id')
            ->select(
                'orders.invoice_number as InvoiceNumber',
                'prd.sku as SHKU',
                'prd.name as ProductName',
                'prd.price as Price',
                'ord.qantity as Quantity'
            )
            ->get();

        return ['data' => $details, 'count' => sizeof($details)];
    }


    public function topHundred()
    {
        $invoices = DB::table('users as u1')
            ->join('users as u2', 'u1.referred_by', '=', 'u2.id')
            ->Join('orders', 'orders.purchaser_id', '=', 'u1.id')
            ->Join('user_category as Ucat', 'Ucat.user_id', '=', 'u1.referred_by')
            ->Join('categories as cat', 'cat.id', '=', 'Ucat.category_id')
            ->select(
                DB::raw("CONCAT(u2.first_name,' ',u2.last_name) as Distributor"),
                'cat.name as referrer_category'
            )
            ->get();

        //use collection library
        $collection = collect($invoices);


        //filter out only specified category
        $filtered = $collection->filter(function ($value, $key) {
            return $value->referrer_category == 'Distributor';
        });


        // get only the value name
        $namesArray = $filtered->pluck('Distributor');

        //get to appear once in new array
        $unique = $namesArray->unique();

        //create new obj for registering num of apperances
        $newObj = array();
        foreach ($unique as $key => $value) {
            array_push($newObj, (object)[
                'name' => $value,
                'count' => 0
            ]);
        }

        // register num of apperances
        for ($i = 0; $i < sizeof($namesArray); $i++) {
            for ($j = 0; $j < sizeof($newObj); $j++) {
                if ($newObj[$j]->name == $namesArray[$i]) {
                    $newObj[$j]->count += 1;
                }
            }
        }

        // sort by count 'DESC'
        usort($newObj, function ($a, $b) {
            return $a->count < $b->count;
        });

        $firstHundred = array_splice($newObj, 0, 100);

        return $firstHundred;
    }
}
