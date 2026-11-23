<?php

/**
 * Updates the MyFatoorah library once a day from the copy published on the portal.
 *
 * The update never rewrites this file, and the published copy is stripped of its
 * comments, so this is the notice that survives an update. The main MyFatoorah
 * class carries a second copy, for an installation that has the update turned off.
 * Keeping either one satisfies the licence.
 *
 * API documentation     https://myfatoorah.readme.io/docs
 * Library documentation https://myfatoorah.readme.io/docs/php-library
 *
 * @author    MyFatoorah Plugin Team <tech@myfatoorah.com>
 * @author    Rasha Saeed <rsaeed@myfatoorah.com>
 * @copyright Copyright (c) 2026 MyFatoorah
 * @license   MIT https://opensource.org/licenses/MIT
 */

$mfVersion = '2.2';

/*
 * The self-update routine below overwrites the library with the copy on the
 * portal. To turn it off, create a .mf-env file next to this one holding the
 * word "development"; the routine then stops here. With no such file - the
 * normal case - it runs as usual. A checkout where the library is being
 * worked on carries one, so work in progress is never overwritten.
 */
$mfEnvFile = __DIR__ . '/.mf-env';
$mfEnv     = is_file($mfEnvFile) && is_readable($mfEnvFile) ? file_get_contents($mfEnvFile) : '';
if (trim((string) $mfEnv) === 'development') {
    return;
}

$mfLibFolder = __DIR__ . '/src/';
$mfLibFile   = $mfLibFolder . 'MyFatoorah.php';

if (!is_writable($mfLibFile) || ((time() - filemtime($mfLibFile)) < 86400)) {
    return;
}

// Repeat after 24 hours
touch($mfLibFile);

if (!in_array('curl', get_loaded_extensions())) {
    throw new \Exception('MyFatoorah Library: Kindly install and enable PHP cURL extension in your server.');
}

try {
    $mfCurl = curl_init("https://portal.myfatoorah.com/Files/API/php/library/$mfVersion/MyfatoorahLibrary.txt");
    $option = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 3, // Maximum time to establish the connection (in seconds)
        CURLOPT_TIMEOUT        => 6  // Maximum time allowed for the entire request (in seconds)
    ];
    curl_setopt_array($mfCurl, $option);

    $mfResponse = curl_exec($mfCurl);
    $mfHttpCode = curl_getinfo($mfCurl, CURLINFO_HTTP_CODE);
    $mfCurlErr  = curl_error($mfCurl);

    unset($mfCurl);

    if ($mfCurlErr) {
        throw new \Exception('cURL Error: ' . $mfCurlErr);
    }

    if ($mfHttpCode != 200 || !is_string($mfResponse)) {
        throw new \Exception('Unexpected response format from update server.');
    }

    mfPutFileContent($mfLibFolder, $mfResponse);
} catch (\Exception $ex) {
    // The library on disk keeps the vendor working, so the log is enough. Say how to stop
    // the daily attempt as well, for a server that cannot be made to reach the portal.
    // A message from PHP does not end in a full stop, so one is added here.
    error_log('MyFatoorah Library: ' . rtrim($ex->getMessage(), '.') . '.'
        . ' The library already on disk still works; only the update failed.'
        . ' Fix the cause, or ask MyFatoorah support at tech@myfatoorah.com, or create'
        . ' a .mf-env file next to this one holding the word "development" to stop the update.');
}

/**
 * Dynamically generates and saves MyFatoorah library classes from a combined response.
 *
 * @param string $mfLibFolder Library folder
 * @param string $mfResponse  Library text
 *
 * @return void
 */
function mfPutFileContent($mfLibFolder, $mfResponse)
{
    // Write nothing unless the whole library arrived. An error page served with HTTP 200,
    // or a download that stopped halfway, would otherwise overwrite what is installed, and
    // the next request would die on a parse error with no copy left to fall back on.
    // combine.php strips every comment but this marker and puts it last, so finding it
    // means the file is complete.
    if (strpos($mfResponse, '<?php') !== 0 || strpos($mfResponse, '// End Of Library') === false) {
        throw new \Exception('The update server did not answer with the library.');
    }

    $mfSplitFile = explode('class', $mfResponse);

    $mfNamespace = '<?php namespace MyFatoorah\Library; ';
    $mfClass     = 'class ';

    $useExClass = 'use Exception; ';
    $useMfClass = 'use MyFatoorah\Library\MyFatoorah; ';

    //namespace MyFatoorah\Library
    file_put_contents($mfLibFolder . 'MyFatoorah.php', $mfNamespace . $useExClass . $mfClass . $mfSplitFile[1]);
    file_put_contents($mfLibFolder . 'MyFatoorahHelper.php', $mfNamespace . $useExClass . $mfClass . $mfSplitFile[2]);
    file_put_contents($mfLibFolder . 'MyFatoorahWebhook.php', $mfNamespace . $useExClass . $mfClass . $mfSplitFile[10]);

    //namespace MyFatoorah\Library\API
    $v2Path = $mfLibFolder . 'API/';

    $mfApiNamespace = '<?php namespace MyFatoorah\Library\API; ';
    file_put_contents($v2Path . 'MyFatoorahList.php', $mfApiNamespace . $useMfClass . $useExClass . $mfClass . $mfSplitFile[3]);
    file_put_contents($v2Path . 'MyFatoorahRefund.php', $mfApiNamespace . $useMfClass . $mfClass . $mfSplitFile[4]);
    file_put_contents($v2Path . 'MyFatoorahShipping.php', $mfApiNamespace . $useMfClass . $mfClass . $mfSplitFile[5]);
    file_put_contents($v2Path . 'MyFatoorahSupplier.php', $mfApiNamespace . $useMfClass . $mfClass . $mfSplitFile[6]);

    //namespace MyFatoorah\Library\API\Payment
    $v2Path .= 'Payment/';

    $mfApiPaymentNamespace = '<?php namespace MyFatoorah\Library\API\Payment; ';
    $useMfListClass        = 'use MyFatoorah\Library\API\MyFatoorahList; ';
    file_put_contents($v2Path . 'MyFatoorahPayment.php', $mfApiPaymentNamespace . $useMfClass . $useExClass . $mfClass . $mfSplitFile[7]);
    file_put_contents($v2Path . 'MyFatoorahPaymentEmbedded.php', $mfApiPaymentNamespace . $useMfListClass . $mfClass . $mfSplitFile[8]);
    file_put_contents($v2Path . 'MyFatoorahPaymentStatus.php', $mfApiPaymentNamespace . $useExClass . $mfClass . $mfSplitFile[9]);

    //namespace MyFatoorah\Library\V3
    $v3Path = $mfLibFolder . 'V3/';

    $mfV3Namespace = '<?php namespace MyFatoorah\Library\V3; ';
    file_put_contents($v3Path . 'MyFatoorahPayments.php', $mfV3Namespace . $useMfClass . $mfClass . $mfSplitFile[11]);
    file_put_contents($v3Path . 'MyFatoorahSessions.php', $mfV3Namespace . $useMfClass . $mfClass . $mfSplitFile[12]);
}
