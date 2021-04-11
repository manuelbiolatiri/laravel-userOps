<?php

// namespace App\Traits;
namespace App\Exceptions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

trait HandleResponse
{

  protected function successResponse($data, $message = null, $code = 200)
  {
    return response()->json([
      'status' => 'Success',
      'message' => $message,
      'data' => $data
    ], $code);
  }

  protected function errorResponse($message = null, $code)
  {
    return response()->json([
      'status' => 'Error',
      'error' => $message,
    ], $code);
  }
}
