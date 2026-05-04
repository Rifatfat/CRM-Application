<?php

// routes/api.php
// CRM Backend - API Routes
// Classes: Client, CommunicationLog, Service, Contract
// Framework: Laravel | Database: MySQL


/*
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\ContractController;
use App\Http\Controllers\API\CommunicationLogController;
use App\Http\Controllers\API\ContactController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\DocumentController;
use App\Http\Controllers\API\UserController;

/*
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommunicationLogController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\ContractController;
// fix eror
*/

/*
|--------------------------------------------------------------------------
| CLIENT ROUTES
|--------------------------------------------------------------------------
| Class: Client
| Fields: id, company_name, industry, address, notes

Route::prefix('clients')->group(function () {
    Route::get('/',             [ClientController::class, 'index']);       // GET    /api/clients
    Route::post('/',            [ClientController::class, 'store']);       // POST   /api/clients
    Route::get('/{id}',         [ClientController::class, 'show']);        // GET    /api/clients/{id}
    Route::put('/{id}',         [ClientController::class, 'update']);      // PUT    /api/clients/{id}
    Route::delete('/{id}',      [ClientController::class, 'destroy']);     // DELETE /api/clients/{id}
    Route::get('/{id}/contacts',    [ClientController::class, 'getContacts']);   // GET /api/clients/{id}/contacts
    Route::get('/{id}/contracts',   [ClientController::class, 'getContracts']);  // GET /api/clients/{id}/contracts
    Route::get('/{id}/documents',   [ClientController::class, 'getDocuments']);  // GET /api/clients/{id}/documents
});

/*
|--------------------------------------------------------------------------
| SERVICE ROUTES
|--------------------------------------------------------------------------
| Class: Service
| Fields: id, name, description, base_price

Route::prefix('services')->group(function () {
    Route::get('/',             [ServiceController::class, 'index']);      // GET    /api/services
    Route::post('/',            [ServiceController::class, 'store']);      // POST   /api/services
    Route::get('/{id}',         [ServiceController::class, 'show']);       // GET    /api/services/{id}
    Route::put('/{id}',         [ServiceController::class, 'update']);     // PUT    /api/services/{id}
    Route::delete('/{id}',      [ServiceController::class, 'destroy']);    // DELETE /api/services/{id}
    Route::get('/{id}/contracts',   [ServiceController::class, 'getContracts']); // GET /api/services/{id}/contracts
});

/*
|--------------------------------------------------------------------------
| CONTRACT ROUTES
|--------------------------------------------------------------------------
| Class: Contract
| Fields: id, client_id, service_id, contract_value, start_date, end_date, status

Route::prefix('contracts')->group(function () {
    Route::get('/',             [ContractController::class, 'index']);     // GET    /api/contracts
    Route::post('/',            [ContractController::class, 'store']);     // POST   /api/contracts
    Route::get('/{id}',         [ContractController::class, 'show']);      // GET    /api/contracts/{id}
    Route::put('/{id}',         [ContractController::class, 'update']);    // PUT    /api/contracts/{id}
    Route::delete('/{id}',      [ContractController::class, 'destroy']);   // DELETE /api/contracts/{id}
    Route::patch('/{id}/status',    [ContractController::class, 'updateStatus']); // PATCH /api/contracts/{id}/status
    Route::get('/{id}/client',      [ContractController::class, 'getClient']);    // GET   /api/contracts/{id}/client
    Route::get('/{id}/service',     [ContractController::class, 'getService']);   // GET   /api/contracts/{id}/service
    Route::get('/{id}/payments',    [ContractController::class, 'getPayments']);  // GET   /api/contracts/{id}/payments
});

/*
|--------------------------------------------------------------------------
| COMMUNICATION LOG ROUTES
|--------------------------------------------------------------------------
| Class: CommunicationLog
| Fields: id, client_id, user_id, communication_type, notes, communication_date


Route::prefix('communication-logs')->group(function () {
    Route::get('/',             [CommunicationLogController::class, 'index']);    // GET    /api/communication-logs
    Route::post('/',            [CommunicationLogController::class, 'store']);    // POST   /api/communication-logs
    Route::get('/{id}',         [CommunicationLogController::class, 'show']);     // GET    /api/communication-logs/{id}
    Route::put('/{id}',         [CommunicationLogController::class, 'update']);   // PUT    /api/communication-logs/{id}
    Route::delete('/{id}',      [CommunicationLogController::class, 'destroy']);  // DELETE /api/communication-logs/{id}
    Route::get('/{id}/client',  [CommunicationLogController::class, 'getClient']); // GET  /api/communication-logs/{id}/client
    Route::get('/{id}/user',    [CommunicationLogController::class, 'getUser']);   // GET  /api/communication-logs/{id}/user
});

/*
|--------------------------------------------------------------------------
| contact ROUTES
|--------------------------------------------------------------------------
| Class: contact
| Fields: id, client_id, name, email, phone, position


Route::prefix('contacts')->group(function () {
    Route::get('/', [ContactController::class, 'index']);       // TAMBAH
    Route::get('/{id}', [ContactController::class, 'show']);    // TAMBAH
    Route::post('/', [ContactController::class, 'store']);
    Route::put('/{id}', [ContactController::class, 'update']);
    Route::delete('/{id}', [ContactController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| PAYMENT ROUTES
|--------------------------------------------------------------------------
| Class: payment
| Fields: id, contract_id, amount, payment_date, payment_method

Route::post('/payments', [PaymentController::class, 'store']);

/*
|--------------------------------------------------------------------------
| DOCUMENT ROUTES
|--------------------------------------------------------------------------
| Class: document
| Fields: id, client_id, file_path, document_type, uploaded_at


Route::prefix('documents')->group(function () {
    Route::post('/', [DocumentController::class, 'store']);
    Route::delete('/{id}', [DocumentController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| USER ROUTES
|--------------------------------------------------------------------------
| Class: user
| Fields: id, name, email, password

Route::post('/login', [UserController::class, 'login']);

*/


use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\ClientController;
use App\Http\Controllers\API\ServiceController;
use App\Http\Controllers\API\ContractController;
use App\Http\Controllers\API\CommunicationLogController;
use App\Http\Controllers\API\ContactController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\DocumentController;
use App\Http\Controllers\API\UserController;

/*
|--------------------------------------------------------------------------
| CLIENT ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('clients')->group(function () {
    Route::get('/', [ClientController::class, 'index']);
    Route::post('/', [ClientController::class, 'store']);
    Route::get('/{client}', [ClientController::class, 'show']);
    Route::put('/{client}', [ClientController::class, 'update']);
    Route::delete('/{client}', [ClientController::class, 'destroy']);

    Route::get('/{client}/contacts', [ClientController::class, 'contacts']);
    Route::get('/{client}/contracts', [ClientController::class, 'contracts']);
    Route::get('/{client}/documents', [ClientController::class, 'documents']);
});

/*
|--------------------------------------------------------------------------
| SERVICE ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('services')->group(function () {
    Route::get('/', [ServiceController::class, 'index']);
    Route::post('/', [ServiceController::class, 'store']);
    Route::get('/{service}', [ServiceController::class, 'show']);
    Route::put('/{service}', [ServiceController::class, 'update']);
    Route::delete('/{service}', [ServiceController::class, 'destroy']);

    Route::get('/{service}/contracts', [ServiceController::class, 'contracts']);
});

/*
|--------------------------------------------------------------------------
| CONTRACT ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('contracts')->group(function () {
    Route::get('/', [ContractController::class, 'index']);
    Route::post('/', [ContractController::class, 'store']);
    Route::get('/{contract}', [ContractController::class, 'show']);
    Route::put('/{contract}', [ContractController::class, 'update']);
    Route::delete('/{contract}', [ContractController::class, 'destroy']);

    Route::patch('/{contract}/status', [ContractController::class, 'updateStatus']);

    Route::get('/{contract}/client', [ContractController::class, 'client']);
    Route::get('/{contract}/service', [ContractController::class, 'service']);
    Route::get('/{contract}/payments', [ContractController::class, 'payments']);
});

/*
|--------------------------------------------------------------------------
| CONTACT ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('contacts')->group(function () {
    Route::get('/', [ContactController::class, 'index']);
    Route::get('/{contact}', [ContactController::class, 'show']);
    Route::post('/', [ContactController::class, 'store']);
    Route::put('/{contact}', [ContactController::class, 'update']);
    Route::delete('/{contact}', [ContactController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| PAYMENT ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('payments')->group(function () {
    Route::get('/', [PaymentController::class, 'index']);
    Route::get('/{payment}', [PaymentController::class, 'show']);
    Route::post('/', [PaymentController::class, 'store']);
    Route::delete('/{payment}', [PaymentController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| DOCUMENT ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('documents')->group(function () {
    Route::get('/', [DocumentController::class, 'index']);
    Route::get('/{document}', [DocumentController::class, 'show']);
    Route::post('/', [DocumentController::class, 'store']);
    Route::delete('/{document}', [DocumentController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| COMMUNICATION LOG ROUTES
|--------------------------------------------------------------------------
*/
Route::prefix('communication-logs')->group(function () {
    Route::get('/', [CommunicationLogController::class, 'index']);
    Route::post('/', [CommunicationLogController::class, 'store']);
    Route::get('/{log}', [CommunicationLogController::class, 'show']);
    Route::put('/{log}', [CommunicationLogController::class, 'update']);
    Route::delete('/{log}', [CommunicationLogController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| AUTH ROUTES
|--------------------------------------------------------------------------
*/
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);