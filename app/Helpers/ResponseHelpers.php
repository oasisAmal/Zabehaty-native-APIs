<?php

/**
 * Response on success data
 *
 * @param array $data
 * @param integer $code
 * @return Response
 */
function responseSuccessData($data = null, $code = 200)
{
    return response()->json([
        'status' => 'success',
        'locale' => app()->getLocale(),
        'data' => $data,
    ], $code);
}

/**
 * Response on success message
 *
 * @param array $data
 * @param integer $code
 * @return Response
 */
function responseSuccessMessage($messages, $code = 200)
{
    if (!is_array($messages)) {
        $messages = [$messages];
    }

    return response()->json([
        'status' => 'success',
        'locale' => app()->getLocale(),
        'data' => $messages,
    ], $code);
}

/**
 * Response on success data
 *
 * @param array $data
 * @param integer $code
 * @return Response
 */
function responseErrorData($data, $code = 200)
{
    if (!is_array($data)) {
        $data = [$data];
    }

    return response()->json([
        'status' => 'error',
        'locale' => app()->getLocale(),
        'data' => $data,
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

    return response()->json([
        'status' => 'error',
        'locale' => app()->getLocale(),
        'data' => $messages,
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
    return response()->json([
        'status' => 'validations',
        'locale' => app()->getLocale(),
        'data' => $errors,
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
        'locale' => app()->getLocale(),
        'hasMorePages' => $result->hasMorePages(),
        'nextPageUrl' => $result->nextPageUrl(),
        'total' => $result->total(),
        'perPage' => $result->perPage(),
        'currentPage' => $result->currentPage(),
        'data' => ($append != null) ? ['append' => $append, 'result' => $result->items()] : $result->items(),
    ], $code);
}
