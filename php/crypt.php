<?php

class Crypt{
	static private function mysql_aes_key($key){
		$new_key = str_repeat(chr(0), 16);
		for($i=0,$len=strlen($key);$i<$len;$i++){
			$new_key[$i%16] = $new_key[$i%16] ^ $key[$i];
		}
		return $new_key;
	}
	
	static public function Encrypt($val){
		$key = self::mysql_aes_key('ti2FTPvuXi2HCIxbc6DiAgrBEu1xxMjwGzO');
		$pad_value = 16-(strlen($val) % 16);
		$val = str_pad($val, (16*(floor(strlen($val) / 16)+1)), chr($pad_value));
		return mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $val, MCRYPT_MODE_ECB, mcrypt_create_iv( mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_DEV_URANDOM));
	}

	static public function Decrypt($val){
		$key = self::mysql_aes_key('ti2FTPvuXi2HCIxbc6DiAgrBEu1xxMjwGzO');
		$val = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $val, MCRYPT_MODE_ECB, mcrypt_create_iv( mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB), MCRYPT_DEV_URANDOM));
		return rtrim($val, "\0..\16");
	}
	
	
	static public function Hasher($info){
		$strength = "08";
		//make a salt and hash it with input, and add salt to end
		$salt = "";
		for ($i = 0; $i < 22; $i++) {
			$salt .= substr("./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789", mt_rand(0, 63), 1);
		}
		//return 82 char string (60 char hash & 22 char salt)
		return crypt($info, "$2a$".$strength."$".$salt).$salt;
	}
	
	static public function CheckHash($info, $encdata){
  		$strength = "08";
		if (substr($encdata, 0, 60) == crypt($info, "$2a$".$strength."$".substr($encdata, 60))) {
			return true;
		}else{
			return false;
    		}
 
	}
	
}
