<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GymController;
use App\Http\Controllers\StripePaymentController;
use App\Http\Controllers\CityManagerController;

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

Route::get('/', function () {
    return view('welcome');
});

// Auth::routes();

// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home')->middleware('auth');
Route::get('/admin', [App\Http\Controllers\AdminController::class, 'index'])->name('index')->middleware('auth');
Route::get('/admin/cities', [App\Http\Controllers\CityController::class, 'showCites'])->name('showCites')->middleware('auth')->middleware('role:admin');
Route::get('/admin/allusers', [App\Http\Controllers\AdminController::class, 'showUsers'])->name('showUsers')->middleware('auth')->middleware('role:admin|cityManager|gymManager');


/* Routes for gyms */
Route::prefix('admin')->middleware(['auth', 'role:cityManager'])->group(function () {
    Route::get('/gyms', [GymController::class, 'showGyms'])->name('showGyms');
    Route::get('/addgym', [GymController::class , 'create'])->name('createGym');
    Route::post('/storegym', [GymController::class, 'store'])->name('storeGym');
    Route::get('/deletegym/{id}', [GymController::class, 'delete'])->name('deleteGym');
    Route::get('/gym/{id}', [GymController::class, 'show'])->name('showGym');
    Route::get('/addEditGym/{id}', [GymController::class, 'edit'])->name('gym.addEdit');
    Route::put('/storeEditGym/{id}', [GymController::class, 'editGym'])->name('gym.storeEditGym');
});

/**         Gym Managers         */
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::get('/gymManagers', [App\Http\Controllers\GymManagerController::class, 'showGymManagers'])->name('showGymManagers');
    Route::get('/addGymManager', [App\Http\Controllers\GymManagerController::class, 'create'])->name('gymManager.create');
    Route::post('/gymManagers', [App\Http\Controllers\GymManagerController::class, 'store'])->name('gymManager.store');
    Route::get('/gymManager/{id}', [App\Http\Controllers\GymManagerController::class, 'show'])->name('show');
    Route::get('/gymManagerDel/{id}', [App\Http\Controllers\GymManagerController::class, 'delete'])->name('delete');
    Route::get('/addEditManager/{id}', [App\Http\Controllers\GymManagerController::class, 'edit'])->name('manager.addEdit');
    Route::put('/storeEditManager/{id}', [App\Http\Controllers\GymManagerController::class, 'editManager'])->name('manager.storeEdit');
});
/************************ */
Route::get('/admin/allusers', [App\Http\Controllers\AdminController::class, 'showUsers'])->name('showUsers')->middleware('auth');

/**         Cities         */
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/cities', [App\Http\Controllers\CityController::class, 'showCites'])->name('showCites');

    Route::get('/addCity', [App\Http\Controllers\CityController::class, 'create'])->name('create');
    Route::post('/cities', [App\Http\Controllers\CityController::class, 'store'])->name('city.store');

    Route::get('/addEditCity/{id}', [App\Http\Controllers\CityController::class, 'edit'])->name('city.addEdit');
    Route::put('/storeEditCity/{id}', [App\Http\Controllers\CityController::class, 'editCity'])->name('city.storeEdit');

    Route::get('/cities/{id}', [App\Http\Controllers\CityController::class, 'show'])->name('city.show');
    Route::get('/delCities/{id}', [App\Http\Controllers\CityController::class, 'delete'])->name('city.delete');
});


/**         City Managers         */
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/allCityManagers', [App\Http\Controllers\CityManagerController::class, 'showCityManager'])->name('showCityManager')->middleware('auth');

    Route::get('/addCityManager', [App\Http\Controllers\CityManagerController::class, 'create'])->name('create')->middleware('auth');
    Route::post('/allCityManagers', [App\Http\Controllers\CityManagerController::class, 'store'])->name('cityManager.store')->middleware('auth');

    Route::get('/addEditCityManager/{id}', [App\Http\Controllers\CityManagerController::class, 'edit'])->name('citymanager.addEdit')->middleware('auth');
    Route::put('/storeEditCityManager/{id}', [App\Http\Controllers\CityManagerController::class, 'editCityManager'])->name('citymanager.storeEdit')->middleware('auth');

    Route::get('/allCityManagers/{id}', [App\Http\Controllers\CityManagerController::class, 'show'])->name('cityManager.show')->middleware('auth');
    Route::get('/delCityManagers/{id}', [App\Http\Controllers\CityManagerController::class, 'deleteCityManager'])->name('citymanager.delete')->middleware('auth');
});

/************************* */
/**         Coches         */
Route::prefix('admin')->middleware(['auth', 'role:admin|cityManager|gymManager'])->group(function () {
    Route::get('/allcoaches', [App\Http\Controllers\CoachController::class, 'showCoaches'])->name('showCoaches')->middleware('auth');
    Route::get('/addcoach', [App\Http\Controllers\CoachController::class, 'create'])->name('create')->middleware('auth');
    Route::post('/allcoaches', [App\Http\Controllers\CoachController::class, 'store'])->name('coach.store')->middleware('auth');

    Route::get('/addEditCoach/{id}', [App\Http\Controllers\CoachController::class, 'edit'])->name('coach.addEdit')->middleware('auth');
    Route::put('/storeEditCoach/{id}', [App\Http\Controllers\CoachController::class, 'editCoach'])->name('coach.storeEdit')->middleware('auth');

    Route::get('/allcoaches/{id}', [App\Http\Controllers\CoachController::class, 'show'])->name('coach.show')->middleware('auth');
    Route::get('/delCoaches/{id}', [App\Http\Controllers\CoachController::class, 'delete'])->name('coach.delete')->middleware('auth');
});

/************************* */


////***************************traning packages */
Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/tarning-packages', [App\Http\Controllers\TrainingPackagesController::class, 'showPackages'])->name('showPackages')->middleware('auth');
    Route::get('/addTraningPackage', [App\Http\Controllers\TrainingPackagesController::class, 'create'])->name('traningPackage.create')->middleware('auth');
    // Route::post('/tarning-packages', [App\Http\Controllers\TrainingPackagesController::class, 'store'])->name('traningPackage.store');
    Route::get('/tarning-packages/{id}', [App\Http\Controllers\TrainingPackagesController::class, 'show'])->name('trainingPackeges.show')->middleware('auth');
    Route::get('/delTaraningPackage/{id}', [App\Http\Controllers\TrainingPackagesController::class, 'deletePackage'])->name('trainingPackage.delete')->middleware('auth');
    // Route::post('/storeCity', [App\Http\Controllers\CityController::class, 'store'])->name('store')->middleware('auth');

    Route::get('/addEditPackage/{id}', [App\Http\Controllers\TrainingPackagesController::class, 'edit'])->name('traningPackage.edit')->middleware('auth');
    Route::put('/storeEditPackage/{id}', [App\Http\Controllers\TrainingPackagesController::class, 'editPackage'])->name('traningPackage.storeEdit')->middleware('auth');
});

////***************************traning sessions */
Route::prefix('admin')->middleware(['auth', 'role:admin|cityManager|gymManager'])->group(function () {
    Route::get('/tarning-sessions', [App\Http\Controllers\TrainingController::class, 'showSessions'])->name('showSessions');
    Route::get('/addTraningSession', [App\Http\Controllers\TrainingController::class, 'create'])->name('traningSession.create');
    // Route::post('/tarning-sessions', [App\Http\Controllers\TrainingController::class, 'store'])->name('traningSession.store');
    Route::get('/tarning-sessions/{id}', [App\Http\Controllers\TrainingController::class, 'show'])->name('trainingSession.show');
    Route::get('/delTaraningSession/{id}', [App\Http\Controllers\TrainingController::class, 'deleteSession'])->name('trainingSession.delete');

    Route::get('/addEditSession/{id}', [App\Http\Controllers\TrainingController::class, 'edit'])->name('traningSession.edit');
    Route::put('/storeEditSession/{id}', [App\Http\Controllers\TrainingController::class, 'editSession'])->name('traningSession.storeEdit');
});



//****************************Attendance Table */
Route::get('/admin/attendance', [App\Http\Controllers\AttendanceController::class, 'showAttendance'])->name('showAttendance')->middleware('auth')->middleware('role:admin|cityManager|gymManager');


/**************************** Stripe ********* */
Route::prefix('stripe')->middleware(['auth', 'role:admin|cityManager|gymManager|coach'])->group(function () {
    Route::get('buyPackage', [StripePaymentController::class, 'stripe']);
    Route::post('buyPackage', [StripePaymentController::class, 'stripePost'])->name('stripe.post');
    // Route::get('admin', function () {
    //     return view('admin');
    // });
});





//***************************************Banned Users */
Route::prefix('admin')->middleware(['auth', 'role:admin|cityManager|gymManager'])->group(function () {
    Route::get('/bannedUsers', [App\Http\Controllers\BannedUsersController::class, 'showbannedUsers'])->name('showbannedUsers');
    Route::get('/bannedUsers/{id}', [App\Http\Controllers\BannedUsersController::class, 'UnBanUser'])->name('UnBanUser');
});
