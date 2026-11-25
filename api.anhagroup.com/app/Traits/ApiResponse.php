<?php

namespace App\Traits;

trait ApiResponse
{
    /*
     * |--------------------------------------------------------------------------
     * | Success Response
     * |--------------------------------------------------------------------------
     */

    protected function successResponse(
        $data = null,
        string $message = 'Success',
        int $code = 200
    ) {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /*
     * |--------------------------------------------------------------------------
     * | Error Response
     * |--------------------------------------------------------------------------
     */

    protected function errorResponse(
        string $message = 'Error',
        int $code = 400,
        $errors = null
    ) {
        $response = [
            'message' => $message,
        ];

        if ($errors) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $code);
    }
}
