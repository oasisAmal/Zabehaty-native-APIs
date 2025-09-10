<?php

/**
 * Response on success data
 *
 * @param array $data
 * @param integer $code
 * @return Response
 */
function responseSuccessData($data = null, $message = null, $code = 200)
{
    return response()->json([
        'status' => 'success',
        'message' => $message,
        'data' => $data,
    ], $code);
}

/**
 * Response on success message
 *
 * @param string $message
 * @param integer $code
 * @return Response
 */
function responseSuccessMessage($message, $code = 200)
{
    return response()->json([
        'status' => 'success',
        'message' => $message,
        'data' => null,
    ], $code);
}

/**
 * Response on failure message
 *
 * @param array $errors
 * @param integer $code
 * @return Response
 */
function responseErrorMessage($messages, $code = 400)
{
    if (!is_array($messages)) {
        $messages = [$messages];
    }

    $message = implode(', ', $messages);

    return response()->json([
        'status' => 'error',
        'message' => $message,
        'data' => null,
    ], $code);
}

/**
 * Response on Validation Errors
 *
 * @param array $errors
 * @param integer $code
 * @return Response
 */
function validationErrors($errors, $code = 400)
{
    $message = implode(', ', $errors);

    return response()->json([
        'status' => 'error',
        'message' => $message,
        'data' => null,
    ], $code);
}

/**
 * Response on paginate
 *
 * @param array $result
 * @param integer $code
 * @return Response
 */
function responsePaginate($result, $append = null, $code = 200)
{
    return response()->json([
        'status' => 'success',
        'message' => null,
        'data' => ($append != null) ? ['append' => $append, 'result' => $result->items()] : $result->items(),
        'pagination' => [
            'hasMorePages' => $result->hasMorePages(),
            'nextPageUrl' => $result->nextPageUrl(),
            'total' => $result->total(),
            'perPage' => $result->perPage(),
            'currentPage' => $result->currentPage(),
        ],
    ], $code);
}
