<?php

use Symfony\Component\HttpFoundation\Response;

if (!function_exists('success')) {
     function success($data, $message, $code = Response::HTTP_OK)
     {
          if ($data) {
               return response()->json([
                    'success' => $message ? $message : true,
                    'data' => $data
               ], $code);
          }
          return response()->json([
               'success' => $message ? $message : true,
          ], $code);
     }
}
if (!function_exists('error')) {
     function error($message = null, $error = null, int $code = 401)
     {
          if ($error)
               return response()->json([
                    'message' => $message ?? "An error encountered while performing the operation",
                    'error' => $error,
                    'code' => $code,
               ], $code);
          return response()->json([
               'message' => $message ?? "An error encountered while performing the operation",
               'code' => $code,
          ], $code);
     }
}