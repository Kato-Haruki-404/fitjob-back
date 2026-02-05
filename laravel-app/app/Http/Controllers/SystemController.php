<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SystemController extends Controller
{
    /**
     * Execute db:seed command.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function seed()
    {
        try {
            // --force flag is required to run in production
            Artisan::call('db:seed', ['--force' => true]);
            
            $output = Artisan::output();
            Log::info('DB Seed executed via API: ' . $output);

            return response()->json([
                'message' => 'Database seeding completed successfully.',
                'output' => $output,
            ]);
        } catch (\Exception $e) {
            Log::error('DB Seed failed via API: ' . $e->getMessage());

            return response()->json([
                'message' => 'Database seeding failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
