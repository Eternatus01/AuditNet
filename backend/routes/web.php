<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-db', function() {
    try {
        DB::connection()->getPdo();
        $dbVersion = DB::select('SELECT VERSION() as version')[0]->version;
        $tables = DB::select('SHOW TABLES');
        
        return response()->json([
            'status' => 'success',
            'message' => 'База данных подключена!',
            'db_version' => $dbVersion,
            'tables_count' => count($tables)
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Ошибка подключения: ' . $e->getMessage()
        ], 500);
    }
});