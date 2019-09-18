<?php

/**
 * Communication Class
 *
 * @access public
 * @author Daniel Boehmer
 * @copyright 2011 JTL-Software GmbH
 */
class Communication
{
    /**
     * @param $cURL
     * @param $xPostData_arr
     * @param $bPost
     * @return mixed
     * @throws Exception
     */
    private static function doCall($cURL, $xPostData_arr, $bPost = true)
    {
        if (function_exists('curl_init')) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_POST, $bPost);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xPostData_arr);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows; U; Windows NT 5.1; rv:1.7.3) Gecko/20041001 Firefox/0.10.1');
            curl_setopt($ch, CURLOPT_URL, $cURL);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_ENCODING, 'UTF-8');
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

            $cContent = curl_exec($ch);

            curl_close($ch);
        } else {
            throw new Exception("Die PHP Funktion curl_init existiert nicht!");
        }

        return $cContent;
    }

    /**
     * @param       $cURL
     * @param array $xData_arr
     * @param bool  $bPost
     * @return mixed|string
     * @throws Exception
     */
    public static function postData($cURL, $xData_arr = array(), $bPost = true)
    {
        if (strlen($cURL) > 0 && is_array($xData_arr)) {
            try {
                return self::doCall($cURL, $xData_arr, $bPost);
            } catch (Exception $exc) {
                throw $exc;
            }
        }

        return '';
    }

    /**
     * @param      $cURL
     * @param      $cFile
     * @param bool $bDeleteFile
     * @return mixed|string
     * @throws Exception
     */
    public static function sendFile($cURL, $cFile, $bDeleteFile = false)
    {
        if (file_exists($cFile)) {
            $aData_arr['opt_file'] = '@' . $cFile;

            try {
                $cContent = self::doCall($cURL, $aData_arr);

                if ($bDeleteFile) {
                    @unlink($cFile);
                }

                return $cContent;
            } catch (Exception $exc) {
                throw $exc;
            }
        }

        return '';
    }
}
