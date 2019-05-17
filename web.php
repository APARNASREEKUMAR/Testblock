<?php
use App\User;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });
Route::get('/','Auth\LoginController@showLoginForm');
Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/homelisting','UserController@index');

Route::group( ['middleware' => ['auth']], function() {  
    Route::resource('admin/users', 'UserController');
    Route::get('admin/export','UserController@export');
    Route::get('admin/show_edit/{id}','UserController@show_edit');
    Route::post('admin/show_edit','UserController@show_edit_save');
    Route::get('admin/user/delete/{id}','UserController@delete');
});
Route::get('/complete-registration', 'UserController@completeRegistration');
Route::post('/2fa', function () {
    // return redirect(URL()->previous());
    // return; 
    if(session('download')==1){
        User::recoverycodes();  
    }
    return redirect('/homelisting');
})->name('2fa')->middleware('2fa');

Route::post('admin/users/search','UserController@search');
Route::get('recoverycodes','UserController@recoverycodes');