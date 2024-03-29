<?php
include_once 'secrets.php';

if($_GET["key"] && $_GET["key"] == $secretKey) {
  $hourString = date("G", time());
  $todayString = $hourString >= 20 ? date("Y-m-d", (time() + 86400)) : date("Y-m-d", time());
  $yearAgo = time() - 31557600;

  $newAmount = 0;
  $oldAmount = 0;
  $updateAmount = false;

  $curlInstance = curl_init();

  curl_setopt($curlInstance, CURLOPT_AUTOREFERER, false);
  curl_setopt($curlInstance, CURLOPT_DNS_SHUFFLE_ADDRESSES, true);
  curl_setopt($curlInstance, CURLOPT_FRESH_CONNECT, true);
  curl_setopt($curlInstance, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curlInstance, CURLOPT_HEADEROPT, CURLHEADER_UNIFIED);
  curl_setopt($curlInstance, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
  curl_setopt($curlInstance, CURLOPT_HTTPHEADER,  array('Accept: application/json, text/plain, */*'));
  curl_setopt($curlInstance, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curlInstance, CURLOPT_URL, 'https://rijkswaterstaatstrooit.nl/api/statistics');
  curl_setopt($curlInstance, CURLOPT_FOLLOWLOCATION, TRUE);

  $json = curl_exec($curlInstance);
  $jsonError = false;
  $sqlError = false;

  if (!curl_error($curlInstance) && !empty($json)) {
    $json_object = json_decode($json);
    if (json_last_error() == JSON_ERROR_NONE) {
      if (array_key_exists('dailyDistance', $json_object) && array_key_exists('dailySaltUsed', $json_object) && array_key_exists('seasonalDistance', $json_object) && array_key_exists('seasonalSaltUsed', $json_object)) {
        $json_dDist = $json_object->{"dailyDistance"};
        $json_dUsed = $json_object->{"dailySaltUsed"};
        $json_sDist = $json_object->{"seasonalDistance"};
        $json_sUsed = $json_object->{"seasonalSaltUsed"};
        if (is_numeric($json_dDist) && is_numeric($json_dUsed) && is_numeric($json_sDist) && is_numeric($json_dUsed)) {
          if ($json_sDist >= $json_dDist && $json_sUsed >= $json_dUsed) {
            $newAmount =  $json_dDist > 100 && $json_dUsed > 2500 ? $json_dUsed : 0;
          } else {
            $jsonError = "Value error";
          }
        } else {
          $jsonError = "Not numeric";
        }
      } // Silently fail if we're missing one of the variables to avoid reporting during summertime
    } else {
      switch (json_last_error()) {
        case JSON_ERROR_DEPTH:
            $jsonError = "Maximum stack depth exceeded";
            break;
        case JSON_ERROR_STATE_MISMATCH:
            $jsonError = "Underflow or the modes mismatch";
            break;
        case JSON_ERROR_CTRL_CHAR:
            $jsonError = "Unexpected control character found";
            break;
        case JSON_ERROR_SYNTAX:
            $jsonError = "Syntax error, malformed JSON";
            break;
        case JSON_ERROR_UTF8:
            $jsonError = "Malformed UTF-8 characters, possibly incorrectly encoded";
            break;
        default:
            $jsonError = "Unknown error";
            break;
      }
    }
  } else {
    $jsonError = "No JSON found";
  }

  curl_close($curlInstance);

  if ($newAmount > 0) {
    $conn = mysqli_connect($dbhost, $dbuser, $dbpass, $dbname);
    if ($conn) {
      $selectSQL = "SELECT date, amount FROM data";
      $result = mysqli_query($conn, $selectSQL);

      if (mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) {
          $rowDate = $row["date"];
          if ($yearAgo > strtotime($rowDate)) {
            $removeSQL = "DELETE FROM data WHERE date='$rowDate'";
            mysqli_query($conn, $removeSQL);
          } elseif ($todayString == $rowDate) {
            $updateAmount = true;
            $oldAmount = $row["amount"];
          }
        }
      }

      if ($newAmount > $oldAmount) {
        if ($updateAmount) {
          $sql = "UPDATE data SET amount='$newAmount' WHERE date='$todayString'";
        } else {
          $sql = "INSERT INTO data (date, amount) VALUES ('$todayString', '$newAmount')";
        }
        $sqlError =  mysqli_query($conn, $sql) ? false : "SQL failed: " . mysqli_error($conn);
      }

      mysqli_close($conn);
    } else {
      $sqlError = "Connection failed: " . mysqli_connect_error();
    }
  }

  if ($sqlError || $jsonError) {
    $recipient = $myName . " <" . $myMail . ">";
    $headers = "From: " . $myName . " <" . $myMail . ">\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=ISO-8859-1\r\n";
    mail($recipient, "Salty error", "SQL: " . $sqlError . "Json: " . $jsonError, $headers);
  }

}

?>
