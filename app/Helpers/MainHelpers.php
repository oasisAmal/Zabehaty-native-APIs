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
function discountCalc($old_price, $price)
{
    if ($old_price && $price) {
        return round((($old_price - $price) * 100) / $old_price);
    }
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
 * Generate an array of random close colors.
 *
 * @param int $numberOfColors The number of random close colors to generate.
 * @param int $range The range for random adjustment (0-255).
 * @return array An array of generated close colors in hex format.
 */
function getRandomCloseColors(int $numberOfColors = 5, int $range = 20): array
{
    $baseColor = getRandomBaseColor();
    $colors = [];

    for ($i = 0; $i < $numberOfColors; $i++) {
        $colors[] = getRandomCloseColor($baseColor, $range);
    }

    return $colors;
}

/**
 * Generate a random close color to the given base color.
 *
 * @param string $baseColor The base color in hex format (e.g., '#ff5733').
 * @param int $range The range for random adjustment (0-255).
 * @return string The generated close color in hex format.
 */
function getRandomCloseColor(string $baseColor, int $range = 20): string
{
    // Ensure the base color starts with a '#'
    if ($baseColor[0] !== '#') {
        $baseColor = '#' . $baseColor;
    }

    // Convert hex color to RGB
    $baseColor = ltrim($baseColor, '#');
    $baseRed = hexdec(substr($baseColor, 0, 2));
    $baseGreen = hexdec(substr($baseColor, 2, 2));
    $baseBlue = hexdec(substr($baseColor, 4, 2));

    // Adjust each color component by a random amount within the specified range
    $newRed = max(0, min(255, $baseRed + rand(-$range, $range)));
    $newGreen = max(0, min(255, $baseGreen + rand(-$range, $range)));
    $newBlue = max(0, min(255, $baseBlue + rand(-$range, $range)));

    // Convert RGB back to hex
    $newColor = sprintf("#%02x%02x%02x", $newRed, $newGreen, $newBlue);

    return $newColor;
}

/**
 * Generate a random base color.
 *
 * @return string The base color in hex format.
 */
function getRandomBaseColor(): string
{
    $red = rand(0, 255);
    $green = rand(0, 255);
    $blue = rand(0, 255);

    return sprintf("#%02x%02x%02x", $red, $green, $blue);
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
