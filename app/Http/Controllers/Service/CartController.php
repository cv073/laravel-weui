<?php

namespace App\Http\Controllers\Service;


use App\Entity\CartItem;
use App\Entity\Product;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\M3Result;

class CartController extends Controller
{
   public function addCart(Request $request,$product_id){

       $m3_result = new M3Result;
       $m3_result->status = 0;
       $m3_result->message = '添加成功';
       //如果当前已经登录
       $member = $request->session()->get('member','');
       if($member != ''){

           $cart_items = CartItem::where('member_id', $member->id)->get();
           $exist = false;
           foreach ($cart_items as $cart_item) {
               if($cart_item->product_id == $product_id) {
                   $cart_item->count ++;
                   $cart_item->save();
                   $exist = true;
                   break;
               }
           }




           if($exist == false) {
               $cart_item = new CartItem;

               $cart_item->product_id = $product_id;

               $cart_item->count = 1;
               $cart_item->member_id = $member->id;
               $cart_item->save();

           }
           return $m3_result->toJson();
       }


      $bk_cart = $request->cookie('bk_cart');

       $bk_cart_arr = ( $bk_cart !=null ? explode(',',$bk_cart) : array());

       $count = 1;  //这个没什么用，只是用来定义的
       foreach ($bk_cart_arr as &$value){
           $index = strpos($value,':');
           if(substr($value,0,$index) == $product_id){
              $count =((int) substr($value,$index+1)) +1;
              $value = $product_id . ':' .$count;
              break;
           }
       }

       if($count == 1){
           array_push($bk_cart_arr,$product_id.':'.$count);
       }




       return response($m3_result->toJson())->withCookie('bk_cart',implode(',',$bk_cart_arr));
   }
    public function deleteCart(Request $request){
         $m3_result = new M3Result;
         $m3_result->status = 0;
         $m3_result->message ='删除成功';

         $product_ids =$request->input('product_ids','');
         if($product_ids == ''){
             $m3_result->status =1;
             $m3_result->message = '书籍ID为空';
             return $m3_result->toJson();
         }
         $product_ids_arr = explode(',',$product_ids);

         $member = $request->session()->get('member','');
         if($member != ''){
             CartItem::whereIn('product_id',$product_ids_arr)->delete();
             return $m3_result->toJson();
         }

        $product_ids = $request->input('product_ids','');
         if($product_ids == '')
         {
             $m3_result->status = 1;
             $m3_result->message = '书籍ID为空';
             return $m3_result->toJson();
         }



         //未登录
         $bk_cart = $request->cookie('bk_cart');
         $bk_cart_arr = ($bk_cart != null ? explode(',',$bk_cart):array());
         foreach($bk_cart_arr as $key => $value){
             $index = strpos($value,':');
             $product_id = substr($value,0,$index);//$product_id对应要删除的商品id，arr是要删除的列表
             if(in_array($product_id,$product_ids_arr)){
                 array_splice($bk_cart_arr,$key,1);//把整个key这个对象删除，我现在终于明白key是个对象
                 continue;
             }
         }

         return response($m3_result->toJson())->withCookie('bk_cart',implode(',',$bk_cart_arr));

    }

}
