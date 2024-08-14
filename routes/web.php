<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {
    $router->post('/generateOtp', 'OtpController@generateOtp');
    $router->post('/verifyOtp', 'OtpController@verifyOtp');
    $router->post('/commentOnPost', 'PostController@commentOnPost');
    $router->post('/getAllPosts', 'PostController@getAllPosts');
    $router->post('/addSubComment', 'PostController@addSubComment');
    $router->post('/createPost', 'PostController@createPost');
    $router->post('/likePost', 'PostController@likePost');
    $router->post('/addFollowing', 'PostController@addFollowing');
    $router->post('/dashboard', 'PostController@dashboard');
    $router->post('/fromArrayValidation', 'OtpController@fromArrayValidation');
    $router->post('/userLogin', 'ProductController@userLogin');
    $router->post('/xmlRequest', 'OtherServiceController@xmlRequest');
    $router->post('/saveFileFormat', 'OtherServiceController@saveFileFormat');
    $router->post('/createCustomer', ['middleware' => 'eligibility', 'uses' => 'ChitController@createCustomer']);
    $router->patch('/createChit', 'ChitController@createChit');
});

$router->group(['prefix' => 'api', 'middleware' => ['userToken', 'checkRole']], function ($router) {
    $router->post('/getProducts', 'ProductController@getProducts');
    $router->post('/addToCart', 'ProductController@addToCart');
    $router->post('/createPayment', 'ProductController@createPayment');
    $router->post('/getUserDetails', 'ProductController@getUserDetails');
});

// $router->group(['prefix' => 'api', 'middleware' => 'eligibility'], function ($router) {
//     $router->post('/createCustomer', 'ChitController@createCustomer');
// });