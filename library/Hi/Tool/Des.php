<?php
class Hi_Tool_Des
{
    /**
     * 加密
     *
     * @param string $key 密钥
     * @param string $text 待加密明文
     * @param string $mode 加密模式，ecb和cbc可选
     * @return string base64编码的加密结果
     */
    public static function encode ($key, $text, $mode = 'ecb')
    {
        $mode = strtolower($mode);
        if ($mode != 'cbc' && $mode != 'ecb') {
            throw new Hi_Tool_Exception('加密模式必须为ecb或者cbc');
        }
        $y = self::_pad($text);
        $td = mcrypt_module_open(MCRYPT_DES, '', $mode, '');
        mcrypt_enc_get_key_size($td);
        mcrypt_generic_init($td, $key, $key);
        $encrypted = mcrypt_generic($td, $y);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return base64_encode($encrypted);
    }
    /**
     * 解密
     *
     * @param string $key 密钥
     * @param string $encrypted 密文
     * @param string $mode 加密模式，ecb或者cbc
     * @return string
     */
    public static function decode ($key, $encrypted, $mode = 'ecb')
    {
        $mode = strtolower($mode);
        if ($mode != 'cbc' && $mode != 'ecb') {
            throw new Hi_Tool_Exception('加密模式必须为ecb或者cbc');
        }
        $encrypted = base64_decode($encrypted);
        $td = mcrypt_module_open(MCRYPT_DES, '', $mode, '');
        mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
        mcrypt_enc_get_key_size($td);
        mcrypt_generic_init($td, $key, $key);
        $decrypted = mdecrypt_generic($td, $encrypted);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $y = self::_unPad($decrypted);
        return $y;
    }
    /**
     * 字符串填充
     *
     * @param string $text
     * @param string $len
     * @return string
     */
    private static function _pad ($text, $len = 8)
    {
        $pad = $len - (strlen($text) % $len);
        return $text . str_repeat(chr($pad), $pad);
    }
    /**
     * 删除字符串填充
     *
     * @param string $text
     * @return string
     */
    private static function _unPad ($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return $text;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return $text;
        }
        return substr($text, 0, - 1 * $pad);
    }
}

