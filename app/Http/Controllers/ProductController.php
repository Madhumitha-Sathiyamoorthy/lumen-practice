<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Products;
use App\Models\ProductCart;
use App\Models\ProductStock;
use App\Models\ProductUsers;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Str;
use App\Models\UserLogin;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redis;

class ProductController extends Controller
{
    /**use Carbon\Carbon;
     * Create a new controller instance.
     *
     * @return void
     */

    // public function __construct()
    // {
    //     $this->middleware('userLogin');
    // }
    public function getProducts(Request $request)
    {
        try {
            $start = $request['start'];
            $limit = $request['limit'];
            $validated = Validator::make($request->all(), [
                'start' => 'required',
                'limit' => 'required',
            ]);
            if ($validated->fails()) {
                return response()->json($validated->errors(), 400);
            }
            if (Arr::hasAny($request['search'][0], ['val', 'min', 'max'])) {
                //We can check isset of all values instead of has method.
                $search = Arr::has($request['search'][0], ['val']) ? $request['search'][0]['val'] : null;
                $min = Arr::has($request['search'][0], ['min']) ? $request['search'][0]['min'] : null;
                $max = Arr::has($request['search'][0], ['max']) ? $request['search'][0]['max'] : null;
                $query = Products::query();

                // Alternatively We can use the query directly:
                // $posts = Products::where('product_name','like',"%{$search}%")->whereBetween('price', [$min, $max])->skip($start)->limit($limit-$start)->get();

                if ($search) {
                    $query->where('product_name', 'like', "%{$search}%");
                }
                if ($min) {
                    $query->where('price', '>=', $min);
                }
                if ($max) {
                    $query->where('price', '<=', $max);
                }
                $posts = $query->skip($start)->limit($limit - $start)->get();
            } else {
                $posts = Products::select('products.*')->skip($start)->limit($limit - $start)->get();
            }

            $response['Status'] = 'success';
            $response['data'] = $posts;
        } catch (\Throwable $e) {
            \Log::error("Get Products Failed --->" . $e->getMessage());
        }
        return $response;
    }

    public function addToCart(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'productId' => 'required',
                'userId' => 'required'
            ]);
            if ($validated->fails()) {
                return response()->json($validated->errors(), 400);
            }
            $userCheck = ProductUsers::find($request->userId);
            if ($userCheck) {
                $checkUser = ProductCart::where('user_id', $request->userId)->where('product_id', $request->productId)->first();
                $checkStock = ProductStock::where('product_id', $request->productId)->first();
                $stockAvailable = ($checkStock['stock'] > 0) ? true : false;
                if ($stockAvailable) {
                    if ($checkUser != null) {
                        ProductCart::where('user_id', $request->userId)->where('product_id', $request->productId)->increment('quantity');
                    } else if ($checkUser == null) {
                        $cart = new ProductCart();
                        $cart->product_id = $request->productId;
                        $cart->user_id = $request->userId;
                        $cart->quantity = 1;
                        $cart->save();
                    }
                    ProductStock::where('product_id', $request->productId)->decrement('stock');
                    $response['Status'] = 'success';
                    $response['data'] = 'Product added to cart!';
                } else {
                    $response['Status'] = 'failed';
                    $response['data'] = 'Sorry :) This product is currently unavailable';
                }
            } else {
                $response['Status'] = 'failed';
                $response['data'] = 'User not found';
            }

        } catch (\Throwable $e) {
            \Log::error("Add to Cart Failed --->" . $e->getMessage());
        }
        return $response;
    }

    public function getUserDetails(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'userId' => 'required'
            ]);
            if ($validated->fails()) {
                return response()->json($validated->errors(), 400);
            }

            $getUserDetails = ProductUsers::where('id', $request->userId)->first(['name', 'email', 'mobile']);

            if ($getUserDetails) {
                $product = DB::table('product_cart')->select('products.*', 'product_cart.quantity')
                    ->join('products', 'products.id', '=', 'product_cart.product_id')
                    ->where('user_id', $request->userId)
                    ->get();

                // Another Method using foreach
                // $product = ProductCart::with('product')->where('user_id', $request->userId)->get();
                // $product=[];
                // foreach ($product as $productDetail) {
                //     array_push($product,$productDetail['product']);
                // }

                $cartCount = ProductCart::with('productUsers')->where('user_id', $request->userId)->sum('quantity');
                $response['Status'] = 'success';
                $response['data'] = $getUserDetails;
                $response['cartCount'] = $cartCount;
                $response['productDetails'] = $product;
            } else {
                $response['Status'] = 'failed';
                $response['Message'] = 'User does not exist!';
            }
        } catch (\Throwable $e) {
            \Log::error("Get User Details Failed --->" . $e->getMessage());
        }
        return $response;

    }


    public function createPayment(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'userId' => 'required'
            ]);
            if ($validated->fails()) {
                return response()->json($validated->errors(), 400);
            }

            $getUserDetails = ProductUsers::where('id', $request->userId)->first(['name', 'email', 'mobile']);
            if ($getUserDetails) {
                $product = DB::table('product_cart')->select('products.*', 'product_cart.quantity')
                    ->join('products', 'products.id', '=', 'product_cart.product_id')
                    ->where('user_id', $request->userId)
                    ->get();
                // print_r($product[0]->product_name);
                $totalAmt = 0;
                foreach ($product as $val) {
                    $val->name = $val->product_name;
                    $val->description = $val->product_desc;
                    $val->unit_amount['currency_code'] = "USD";
                    $val->unit_amount['value'] = number_format($val->price, 2);
                    $totalAmt += $val->unit_amount['value'] * $val->quantity;
                    unset($val->product_name);
                    unset($val->product_desc);
                    unset($val->id);
                    unset($val->created_at);
                    unset($val->updated_at);
                    unset($val->price);
                }
                $provider = new PayPalClient;
                $provider->setApiCredentials(config('paypal'));
                $provider->setCurrency('USD');
                $provider->getAccessToken();
                $items = json_encode($product->toArray());
                $data = json_decode('{
                    "intent": "CAPTURE",
                    "purchase_units": [
                      {
                        "items":' . $items . ',
                        "amount": {
                            "currency_code": "USD",
                            "value": "' . $totalAmt . ".00" . '",
                            "breakdown": {
                                "item_total": {
                                    "currency_code": "USD",
                                    "value": "' . $totalAmt . ".00" . '"
                                }
                            }
                        }
                      }
                    ]
                }', true);
                $placeOrder = $provider->createOrder($data);
                $getOrderDetails = $provider->showOrderDetails($placeOrder['id']);
                // $payreq = json_decode('{
                //     "payment_source": {
                //       "paypal": {
                //         "name": {
                //           "given_name": "John",
                //           "surname": "Doe"
                //         },
                //         "email_address": "customer@example.com",
                //         "experience_context": {
                //           "payment_method_preference": "IMMEDIATE_PAYMENT_REQUIRED",
                //           "brand_name": "EXAMPLE INC",
                //           "locale": "en-US",
                //           "landing_page": "LOGIN",
                //           "shipping_preference": "SET_PROVIDED_ADDRESS",
                //           "user_action": "PAY_NOW",
                //           "return_url": "https://example.com/returnUrl",
                //           "cancel_url": "https://example.com/cancelUrl"
                //         }
                //       }
                //     }
                //   }', true);
                // $getOrderDetails = $provider->confirmOrder($placeOrder['id'],$payreq);
                // $confirmorder=$provider->authorizePaymentOrder($getOrderDetails['id']);
                // print_r($getOrderDetails);
                // exit;
                $response['Status'] = 'success';
                $response['paymentLink'] = $getOrderDetails;
            } else {
                $response['Status'] = 'failed';
                $response['Message'] = 'User does not exist!';
            }
        } catch (\Throwable $e) {
            \Log::error("Get User Details Failed --->" . $e->getMessage());
        }
        return $response;
    }

    public function userLogin(Request $request)
    {
        try {
            $validated = Validator::make($request->all(), [
                'email' => 'required|email',
                'password' => 'required',
            ]);
            if ($validated->fails()) {
                return response()->json($validated->errors(), 400);
            }
            $checkUserDetails = UserLogin::where('email', $request->email)->where('password', $request->password)->first();
            if ($checkUserDetails) {
                $id = $checkUserDetails['id'];
                $setToken = array('token' => Str::random(20), 'loginTime' => Carbon::now()->toDateTimeString());
                Redis::set('user_' . $id, json_encode($setToken));
                $response['Status'] = 'success';
                $response['Message'] = 'You Logged in successfully !';
                $response['Token'] = $setToken['token'];
            } else {
                $response['Status'] = 'failed';
                $response['Message'] = 'Email Id or Password incorrect !';
            }
        } catch (\Throwable $e) {
            \Log::error("Get User Details Failed --->" . $e->getMessage());
        }
        return $response;
    }

}
