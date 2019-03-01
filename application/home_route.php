<?php

use think\Route;

/**
 * 网址大全
 */
// 网址管理

Route::get('home/index','api/index/index');//渲染页面

Route::rule('home/my','api/member/index');
Route::rule('home/xiugai','api/member/xiugai');
Route::rule('home/modifyPhone','api/member/modifyPhone');
Route::rule('home/onPhoneNum','api/member/onPhoneNum');
Route::rule('home/focusMaster','api/member/focusMaster');

Route::rule('home/addressManag','api/address/addressManag');
Route::rule('home/newAddress','api/address/newAddress');
Route::rule('home/addressList','api/address/addressList');
Route::rule('home/addressShi','api/address/addressShi');
Route::rule('home/addressXian','api/address/addressXian');
Route::rule('home/xiugaiAddress','api/address/xiugaiAddress');
Route::rule('home/addressList1','api/address/addressList1');
Route::rule('home/addressShi2','api/address/addressShi2');
Route::rule('home/addressXian3','api/address/addressXian3');




Route::rule('home/ongoing','api/details/vie');

Route::rule('home/delivery','api/details/sign');
Route::rule('home/record','api/details/record');
Route::rule('home/focusStore','api/details/focusStore');

Route::rule('home/bidding','api/my_auction/index');

Route::rule('home/order','api/orders/order');

Route::rule('home/orderDetails','api/orders/orderDetails');

Route::rule('home/weiyue','api/orders/weiyues');

Route::rule('home/daishouhuo','api/orders/daishouhuos');
Route::rule('home/daifukuan','api/orders/daifukuans');
Route::rule('home/daifahuo','api/orders/daifahuos');

