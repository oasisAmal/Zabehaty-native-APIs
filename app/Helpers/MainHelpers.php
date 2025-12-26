<?php

use Carbon\Carbon;
use App\Enums\Common;
use App\Enums\MobileRegex;
use Illuminate\Support\Str;
use Illuminate\Support\Number;
use Shivella\Bitly\Facade\Bitly;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Modules\Countries\App\Models\Country;
use Propaganistas\LaravelPhone\PhoneNumber;

/**
 * Helper for format date for 12 Hours
 *
 * @param string $date
 * @return string value
 */
function format_date_12($date)
{
    return Carbon::parse($date)->format(Common::DATE_FORMAT_12);
}

/**
 * Helper for format date for 24 Hours
 *
 * @param string $date
 * @return string value
 */
function format_date_24($date)
{
    return Carbon::parse($date)->format(Common::DATE_FORMAT_24);
}

/**
 * Helper for format date
 *
 * @param string $date
 * @return string value
 */
function format_date($date)
{
    return Carbon::parse($date)->format(Common::DATE_FORMAT);
}

/**
 * Helper for format date for 24 Hours
 * @param string $date
 * @return string
 */
function format_humans_date($date)
{
    return Carbon::parse($date)->diffForHumans();
}


/**
 * Helper for parse format date for 24 Hours
 *
 * @param string $date
 * @return string value
 */
function parse_format_date_24($date)
{
    return Carbon::parse($date)->format(Common::DATE_FORMAT_24_TO_SAVE_DATABASE);
}

/**
 * Helper for parse format date
 *
 * @param string $date
 * @return string value
 */
function parse_format_date($date)
{
    return Carbon::parse($date)->format(Common::DATE_FORMAT_TO_SAVE_DATABASE);
}

/**
 * Helper for parse date to NPHIES format
 *
 * @param string $date
 * @return string value
 */
function parse_date_to_nphies($date)
{
    return Carbon::parse($date)->format('Y-m-d\TH:i:s.vP');
}

/**
 * Helper for format money
 *
 * @param string $total
 * @param string $currency
 * @return string value
 */
function format_money($total, $currency = 'SAR')
{
    return ($total) ? Number::currency($total, $currency, app()->getLocale()) : $currency . ' 0';
}

/**
 * Helper for handle string to title
 *
 * @param string $string
 * @return string value
 */
function format_string($string)
{
    return Str::title(Str::replace('_', ' ', $string));
}

/**
 * Helper for format numbers
 *
 * @param string $date
 * @return string value
 */
function format_numbers($total)
{
    return ($total) ? Number::format($total) : 0;
}

/**
 * Helper for Generate Random Numbers
 *
 * @param int $length
 * @return int value
 */
function generateRandomNumber($length)
{
    $result = '';

    for ($i = 0; $i < $length; $i++) {
        $result .= mt_rand(0, 9);
    }

    return $result;
}

/**
 *
 * Generate a unique random string of characters
 * uses str_random() helper for generating the random string
 *
 * @param     $table - name of the table
 * @param     $col - name of the column that needs to be tested
 * @param int $chars - length of the random string
 *
 * @return string
 */
function uniqueRandomCode($table, $col, $length = 16)
{
    $number = generateRandomNumber($length);
    $isUsed = DB::table($table)->where($col, $number)->first();
    if ($isUsed) {
        return uniqueRandomCode($table, $col, $length);
    }
    return $number;
}

/**
 * Helper for Calc Discount
 *
 * @param float $old_price
 * @param float $price
 *
 * @return float value
 */
function discountCalc($old_price = 0, $price = 0)
{
    if ($old_price > 0 && $price >= 0) {
        return round((($old_price - $price) * 100) / $old_price);
    }

    return 0;
}

/**
 * Helper for Get Discount Amount
 *
 * @param float $old_price
 * @param float $discount
 *
 * @return float value
 */
function getDiscountAmount($old_price, $discount)
{
    if ($old_price && $discount) {
        return ($old_price / 100) * $discount;
    }
}

/**
 * Helper for Expiry Date
 *
 * @param string $date
 * @param string $type
 * @param string $num
 */
function expiryDate($date, $type, $num)
{
    $startDateObj = new DateTime($date);
    $startDateObj->modify("+$num $type s");
    $expiryDate = $startDateObj->format('Y-m-d');
    return $expiryDate;
}

/**
 * Helper for Expiry Date Time
 *
 * @param string $date
 * @param string $type
 * @param string $num
 */
function expiryDateTime($date, $type, $num)
{
    $startDateObj = new DateTime($date);
    $startDateObj->modify("+$num $type s");
    $expiryDateTime = $startDateObj->format('Y-m-d h:i:s');
    return $expiryDateTime;
}

/**
 * Get Class Basename
 *
 * @param object $model
 */
function getClassBasename($model)
{
    return basename(str_replace('\\', '/', get_class($model)));
}

/**
 * Helper for format mobile number to database
 *
 * @param string $number
 * @param string $country_code
 */
function format_mobile_number_to_database($number, $country_code = null)
{
    try {
        $phone = new PhoneNumber($number, $country_code);
        $formatted = $phone->formatForMobileDialingInCountry($country_code);
        return format_mobile_number($formatted);
    } catch (\Throwable $th) {
        return format_mobile_number($number);
    }
}

/**
 * Helper for format mobile number with plus
 *
 * @param string $number
 * @param string $country_code
 */
function format_mobile_number_with_plus($number, $country_code = null)
{
    try {
        $phone = new PhoneNumber($number, $country_code);
        return $phone->formatE164();
    } catch (\Throwable $th) {
        return $number;
    }
}

/**
 * Helper for format mobile number without plus
 *
 * @param string $number
 * @param string $country_code
 */
function format_mobile_number_without_plus($number, $country_code = null)
{
    $phone = new PhoneNumber($number, $country_code);
    return str_replace('+', '', $phone->formatE164());
}

/**
 * Generate Url
 *
 * @param integer $id
 * @param string $route
 * @param string $days
 * @param boolean $is_short_link
 */
function genarate_signed_url($id, $route, $days, $is_short_link = true)
{
    $url = URL::temporarySignedRoute(
        $route,
        now()->addDays($days),
        [
            'id' => $id
        ]
    );
    if ($is_short_link) {
        return genarate_short_url($url);
    }
    return $url;
}

/**
 * Helper For remove 0 from the beging number
 *
 * @param string $mobileNumber
 *
 * @return string
 */
function format_mobile_number($mobileNumber)
{
    // Check if the mobile number starts with '0'
    if (strpos($mobileNumber, '0') === 0) {
        // Remove the leading '0'
        $mobileNumber = substr($mobileNumber, 1);
    }

    return $mobileNumber;
}

/**
 * Get the month name from the month number.
 *
 * @param int $monthNumber
 * @return string
 * @throws Exception
 */
function getMonthName(int $monthNumber): string
{
    if ($monthNumber < 1 || $monthNumber > 12) {
        throw new Exception('Invalid month number. It must be between 1 and 12.');
    }

    $date = DateTime::createFromFormat('!m', $monthNumber);
    return $date->format('F');
}

/**
 * Get value based on percentage value
 * @param double $total
 * @param float $percentage
 *
 * @return float
 */
function getPercentageValue($total, $percentage)
{
    return ($total * $percentage) / 100;
}

/**
 * Convert a duration string like "42 minutes" or "2 hours 30 minutes" to the total minutes.
 *
 * @param string $duration
 * @return int Total minutes
 */
function convertDurationToMinutes($duration)
{
    $hours = 0;
    $minutes = 0;

    // Extract hours
    if (preg_match('/(\d+)\s*hours?/', $duration, $hourMatches)) {
        $hours = (int) $hourMatches[1];
    }

    // Extract minutes
    if (preg_match('/(\d+)\s*minutes?/', $duration, $minuteMatches)) {
        $minutes = (int) $minuteMatches[1];
    }

    // Convert total time to minutes
    $totalMinutes = ($hours * 60) + $minutes;

    return $totalMinutes;
}

/**
 * Get DateTime based on the given datetime and duration string, and plus duration to given datetime.
 *
 * @param string $datetime
 * @param string $duration
 *
 * @return string
 */
function addDurationToDateTime($datetime, $duration)
{
    $totalMinutes = convertDurationToMinutes($duration);
    return Carbon::parse($datetime)->addMinutes($totalMinutes)->format('Y-m-d H:i:s');
}

/**
 * Get Mobile Regex based on the given country code
 *
 * @param string $country_code
 * @return string
 */
function getMobileRegexBasedOnCountryCode($country_code)
{
    $mobileRegex = MobileRegex::ALL_MOBILE_REGEX;
    $country_code = strtoupper($country_code);
    $regex = $mobileRegex[$country_code] ?? MobileRegex::AE;
    return '/' . $regex . '/';
}

/**
 * Handle media video or image
 *
 * @param string $media
 * @return array
 */
function handleMediaVideoOrImage($media)
{
    if (is_string($media)) {
        $media = json_decode($media, true) ?? [];
    }
    if (!is_array($media)) {
        return [];
    }

    return array_values(array_filter(array_map(function ($entry) {
        if (!is_string($entry) || $entry === '') {
            return null;
        }

        return [
            'media_type' => isVideo($entry) ? 'video' : 'image',
            'media_url' => $entry,
        ];
    }, $media)));
}

/**
 * Determine if media entry is a video based on extension.
 *
 * @param string $entry
 * @return bool
 */
function isVideo($entry)
{
    $extension = strtolower(pathinfo(parse_url($entry, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
    return in_array($extension, ['mp4', 'mov', 'avi', 'mkv', 'webm'], true);
}
