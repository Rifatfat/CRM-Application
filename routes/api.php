<?php

// routes/api.php
// CRM Backend - API Routes
// Classes: Client, CommunicationLog, Service, Contract
// Framework: Laravel | Database: MySQL

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\CommunicationLogController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ContractController;

/*
|--------------------------------------------------------------------------
| CLIENT ROUTES
|--------------------------------------------------------------------------
| Class: Client
| Fields: id, company_name, industry, address, notes
*/
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
*/
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
*/
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
*/
Route::prefix('communication-logs')->group(function () {
    Route::get('/',             [CommunicationLogController::class, 'index']);    // GET    /api/communication-logs
    Route::post('/',            [CommunicationLogController::class, 'store']);    // POST   /api/communication-logs
    Route::get('/{id}',         [CommunicationLogController::class, 'show']);     // GET    /api/communication-logs/{id}
    Route::put('/{id}',         [CommunicationLogController::class, 'update']);   // PUT    /api/communication-logs/{id}
    Route::delete('/{id}',      [CommunicationLogController::class, 'destroy']);  // DELETE /api/communication-logs/{id}
    Route::get('/{id}/client',  [CommunicationLogController::class, 'getClient']); // GET  /api/communication-logs/{id}/client
    Route::get('/{id}/user',    [CommunicationLogController::class, 'getUser']);   // GET  /api/communication-logs/{id}/user
});