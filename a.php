<?php
header('content-type:text/html;charset=utf-8');
 
define("TOKEN", "weixin");
define("appID", "xxxxxxxxxxxxx");
define("appSecret", "xxxxxxxxxxxxxxxxxxxxxxxxxxxx");
$wx = new wechatCallbackapiTest();

if($_GET['echostr']){
	$wx->valid();
}else{
	$wx->responseMsg();
}


class wechatCallbackapiTest{

	public function valid(){

		$echoStr = $_GET["echostr"];
		if($this->checkSignature()){ //调用验证字段
			echo $echoStr;
			exit;
		}
 	}
 
	public function responseMsg(){
	 
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"]; //接收微信发来的XML数据
	
		if(!empty($postStr)){
			//解析post来的XML为一个对象$postObj
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			
			$fromUsername = $postObj->FromUserName;
			$toUsername = $postObj->ToUserName;
			$keyword = trim($postObj->Content); 
			$time = time(); 
			$msgtype = 'text';
			$textTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Content><![CDATA[%s]]></Content>
						</xml>";
		
			if($keyword == 'hello'){
				$contentStr = 'hello world!!!';
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgtype, $contentStr);
				echo $resultStr;
				exit();            
			}elseif($keyword == 'pic'){
				$imgtype = 'image';
				$image_id = $this->upLoadImage();
				$textImg = "<xml>
							<ToUserName><![CDATA[%s]]></ToUserName>
							<FromUserName><![CDATA[%s]]></FromUserName>
							<CreateTime>%s</CreateTime>
							<MsgType><![CDATA[%s]]></MsgType>
							<Image>
							<MediaId><![CDATA[%s]]></MediaId>
							</Image>
							</xml>";
				$resultStr = sprintf($textImg, $fromUsername, $toUsername, $time, $imgtype, $image_id);
				echo $resultStr;
				exit();
			}else{
				$contentStr = '我不是 AI 啊';
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgtype, $contentStr);
				echo $resultStr;
				exit();
			}
		
		}else {
			echo "success";
			exit;
		}
	}
	
 	private function checkSignature(){
 
		$signature = $_GET["signature"];
		$timestamp = $_GET["timestamp"];
		$nonce = $_GET["nonce"]; 
		
		$token = TOKEN;
		$tmpArr = array($token, $timestamp, $nonce);
		sort($tmpArr);
		$tmpStr = implode( $tmpArr );
		$tmpStr = sha1( $tmpStr );
			
		if( $tmpStr == $signature ){
			return true;
		}else{
			return false;
		}
	}

	private function getAccessToken(){
		$url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".appID."&secret=".appSecret;
		$data = json_decode(file_get_contents($url),true);
		if($data['access_token']){
			return $data['access_token'];
		}
	}

	public function upLoadImage(){
		$url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token=".$this->getAccessToken()."&type=image";
		if (class_exists('\CURLFile')) {
		$josn = array('media' => new \CURLFile(realpath("../api/QR.png")));
		} else {
			$josn = array('media' => '@' . realpath("../api/QR.png"));
		}
		$ret = $this->curlPost($url,$josn);
		$row = json_decode($ret);//对JSON格式的字符串进行编码
		$mediaID = $row->media_id;
		//echo $mediaID;
		return $mediaID;
	}

	private function curlPost($url, $data = null){
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $url);
		if (!empty($data)){
        	curl_setopt($curl, CURLOPT_POST, 1);
        	curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    	}
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$output = curl_exec($curl);

		curl_close($curl);
		return $output;
	}
}

?>