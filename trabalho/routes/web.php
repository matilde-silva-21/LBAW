<?php
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

//Extract to controller
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// Home
Route::get('/', 'Auth\LoginController@home');

// Authentication
Route::get('login', 'HomeController@login')->name('login');
Route::post('login', 'Auth\LoginController@login');
Route::get('logout', 'Auth\LoginController@logout')->name('logout');
Route::get('register', 'HomeController@login')->name('register');
Route::post('register', 'Auth\RegisterController@register');

// Home Page
Route::get('home', 'HomeController@home')->name('home.show');

// Events Related
Route::get('event{eventid?}', 'EventController@show')->name('event.show');

Route::get('api/editEvent{eventid?}', 'EventController@edit')->name('event.edit');  //edit the details of an event - display form
Route::post('updateEvent/{event_id?}', 'EventController@update')->name('updateEvent'); //update the details of an event
Route::get('createEvent', 'EventController@create')->name('event.create');  //edit the details of an event - display form
Route::post('storeEvent', 'EventController@store')->name('storeEvent'); //create a new event

Route::post('addEventUsers', 'EventController@addUser')->name('addUser'); //update the details of an event
Route::post('removeEventUsers', 'EventController@removeUser')->name('removeUser'); //update the details of an event

// Static Pages
Route::get('aboutUs', 'PageController@aboutUs')->name('aboutUs.index');
Route::get('faq', 'PageController@faq')->name('faq.index');

Route::get('search','SearchController@search');
Route::get('searchUsers','SearchController@searchUsers')->name('searchUsers');
Route::get('searchAttendees','SearchController@searchAttendees')->name('searchAttendees');
Route::get('searchUsersAdmin','SearchController@searchUsersAdmin')->name('searchUsersAdmin');
Route::get('searchEventsByTag','SearchController@searchEventsByTag')->name('searchEventsByTag');

Route::get('user{userid?}', 'UserController@show')->name('user.show');
Route::post('updateUser/{userid?}', 'UserController@update')->name('updateUser'); //update the details of an event
Route::post('selfAddUser', 'UserController@attendEvent')->name('selfAddUser');
Route::post('selfRemoveUser', 'UserController@leaveEvent')->name('selfRemoveUser');
Route::post('storeUser', 'UserController@store')->name('storeUser');
Route::post('deleteUser', 'UserController@delete')->name('deleteUser');

//testing database data
Route::get('cities', 'CityController@index');
Route::get('countries', 'CountryController@index');
Route::get('events', 'EventController@index');

// Comments
Route::post('storeComment', 'CommentController@store')->name('storeComment');
Route::post('deleteComment', 'CommentController@deleteComment')->name('deleteComment');
Route::get('getComments', 'CommentController@getComments')->name('getComments');
Route::get('getSingleComment', 'CommentController@getSingleComment')->name('getSingleComment');

// Upvotes
Route::post('addUpvote', 'UpvoteController@addUpvote')->name('addUpvote');
Route::post('removeUpvote', 'UpvoteController@removeUpvote')->name('removeUpvote');
// Report Comment
Route::get('getReportedComments', 'ReportController@getReportedComments')->name('getReportedComments');
Route::post('storeReport', 'ReportController@store')->name('storeReport');

//Invites
Route::post('api/invite', 'InvitedController@create')->name('createInvite');
Route::put('api/inviteAccept', 'InvitedController@accept')->name('acceptInvite');
Route::delete('api/inviteReject', 'InvitedController@reject')->name('rejectInvite');
Route::put('api/clearNotifications', 'InvitedController@read')->name('readNotifications');
Route::get('api/numberNotifications', 'InvitedController@numberNotifications')->name('numberNotifications');

Route::put('api/changeBlockStatus', 'UserController@block')->name('changeBlockStatusUser');
Route::post('banUser', 'UserController@ban_user')->name('banUser');

Route::get('auth', 'Auth\LoginController@getUser');

Route::post('transferOwnership', 'EventController@transferOwnership')->name('transferOwnership'); //transfer the ownership of an event
Route::post('deleteEvent', 'EventController@destroy')->name('deleteEvent'); //delete an event


// Password Reset 

Route::get('/forgot-password', function () {
    return view('auth.recoverPass');
})->middleware('guest')->name('password.request');

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
                ? back()->with(['status' => __($status)])
                : back()->withErrors(['email' => __($status)]);
})->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', function ($token) {
    return view('auth.resetPassword', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        }
    );

    return $status === Password::PASSWORD_RESET
                ? redirect()->route('login')->with('status', __($status))
                : back()->withErrors(['email' => [__($status)]]);
})->middleware('guest')->name('password.update');

// $schedule->command('auth:clear-resets')->everyFifteenMinutes();

use App\Http\Controllers\TestController;

Route::get('/send-email', [TestController::class, 'sendEmail']);
