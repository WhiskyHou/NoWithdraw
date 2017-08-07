<?php
header('content-type:text/html;charset=utf-8');
 
define("TOKEN", "weixin"); //define your token
define("appID", "wx79e2cdc216e9280d");
define("appSecret", "f863f8fffa9f8c8e19d47c2b1e9b2905");
$wx = new wechatCallbackapiTest();

if($_GET['echostr']){
	$wx->valid(); //如果发来了echostr则进行验证
}else{
	//$wx->upLoadImage();
	$wx->responseMsg(); //如果没有echostr，则返回消息
}


class wechatCallbackapiTest{

	public function valid(){ //valid signature , option

		$echoStr = $_GET["echostr"];
		if($this->checkSignature()){ //调用验证字段
			echo $echoStr;
			exit;
		}
 	}
 
	public function responseMsg(){
	 
		//get post data, May be due to the different environments
		$postStr = $GLOBALS["HTTP_RAW_POST_DATA"]; //接收微信发来的XML数据
	
		//extract post data
		if(!empty($postStr)){
			//解析post来的XML为一个对象$postObj
			$postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
			
			$fromUsername = $postObj->FromUserName; //请求消息的用户
			$toUsername = $postObj->ToUserName; //"我"的公众号id
			$keyword = trim($postObj->Content); //消息内容
			$time = time(); //时间戳
			$msgtype = 'text'; //消息类型：文本
			$textTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Content><![CDATA[%s]]></Content>
						</xml>";
		
			if($keyword == 'hehe'){
				$contentStr = 'hello world!!!';
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgtype, $contentStr);
				echo $resultStr;
				exit();            
			}elseif($keyword == '防撤回'){
				pclose(popen('../wechat/go.sh &', 'r'));
				sleep(1);
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
				$contentStr = "我不是AI啊\n（逃";
				$resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgtype, $contentStr);
				echo $resultStr;
				exit();
			}
		
		}else {
			echo "success";
			exit;
		}
	}
	
 	//验证字段
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
		$josn = array('media' => new \CURLFile(realpath("../wechat/QR.png")));
		} else {
			$josn = array('media' => '@' . realpath("../wechat/QR.png"));
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
		//执行curl，抓取URL并把它传递给浏览器
		$output = curl_exec($curl);
		//关闭cURL资源，并且释放系统资源
		curl_close($curl);
		return $output;
	}
}

?>