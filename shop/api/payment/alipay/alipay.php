<?php
/**
 * 支付宝接口类
 *
 * 
 
 */
defined('InShopNC') or exit('Access Invalid!');
require_once("AlipayService.class.php");

class alipay{
	/**
	 *支付宝网关地址（新）
	 */
	private $alipay_gateway_new = 'https://mapi.alipay.com/gateway.do?';
	/**
	 * 消息验证地址
	 *
	 * @var string
	 */
	private $alipay_verify_url = 'https://mapi.alipay.com/gateway.do?service=notify_verify&';
	/**
	 * 支付接口标识
	 *
	 * @var string
	 */
    private $code      = 'alipay';
    /**
	 * 支付接口配置信息
	 *
	 * @var array
	 */
    private $payment;
     /**
	 * 订单信息
	 *
	 * @var array
	 */
    private $order;
    /**
	 * 发送至支付宝的参数
	 *
	 * @var array
	 */
    private $parameter;
    /**
     * 订单类型
     * @var unknown
     */
    private $order_type;

    public function __construct($payment_info = array(),$order_info = array()){
    	if (!extension_loaded('openssl')) $this->alipay_verify_url = 'http://notify.alipay.com/trade/notify_query.do?';
    	if(!empty($payment_info) and !empty($order_info)){
    		$this->payment	= $payment_info;
    		$this->order	= $order_info;
    	}
    }

    public function submit(){
        /*** 请填写以下配置信息 ***/
        $appid = '2018062760449267';			//https://open.alipay.com 账户中心->密钥管理->开放平台密钥，填写添加了电脑网站支付的应用的APPID
        $returnUrl = SHOP_SITE_URL."/api/payment/alipay/return_url.php?extra_common_param=".$this->order['order_type'];     //付款成功后的同步回调地址
        $notifyUrl = SHOP_SITE_URL."/api/payment/alipay/notify_url.php";     //付款成功后的异步回调地址
        $outTradeNo = $this->order['pay_sn'];     //你自己的商品订单号，不能重复
        $payAmount = $this->order['api_pay_amount'];          //付款金额，单位:元
        $orderName = $this->order['subject'];    //订单标题
        $signType = 'RSA2';			//签名算法类型，支持RSA2和RSA，推荐使用RSA2
        $rsaPrivateKey='MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQCtrfjFtfycw2if6Xf9fri0+krGVWKA7L+zwDhj6PXqtMWOwh3qN2Wdti4pC2BHa6LNUZ7aCQ3vwePlcDNRxZ0+Z/HMxCMVWRgidEIUclVN2hWaOk2M3x31fztgubHI/yAJKYYIc9JMlPJtyIJJPn8N4X8c6TFWzbyvHinfNHVchrCJ5A05OLcnLgnz3x02rBlS/o/Uvx+q1djB2GzSbG5/oWtwqS3gdFv23nBa4o/5DewrqooA+sUt8mY+OuXtQw+1X5+WljKAcceVkUeI4iuP5qK6HQ5W0Ier0+r72tpGth9mVhYVaonVoKCLjTm88T3M1gXloj6H4ilU0xpz2JRPAgMBAAECggEAasqDIcaRyi5ZIaKjtgn6NsWDvsn3pIBuK29w/PXcZ/G5W9s+G6ruX3gKkBA/pgqn8wlR3I0etaKJp6VC2O/ijOHLCvY7AEBlF6JWk03t583F5KwezRTPzgjqkMH9cDJ3F0sh8AaPc6lOG3Tjr3evFfDmgPdd8BrW/vJUGrudwkLGH/xfIkfDpHyipMN6eUE197Hc4Wbp0L2eDN28XhI9NhfMn80phQlmIErcXUv1HUvuSPx1cdrytzWzL8YWBQbcZQP/uLe1ZzKItEw7Ozc8wNB/SCq3+YqF6AViFy/cgLU0kdDEzl9mVG/MdWnEqQ5thh+iEVdFO696/V3Z+Tj3IQKBgQDtWBM8a4nGfKfb7W/7UALp6XYdtYXh+pZk83DToH9OBjh6hDfRm7wf4I273bppC7e5AUpnd6ncNOq4RAEhAaSIZ6pq2R9K7Qx7C05udOoB5lnX39Ss9kO4S5g/emUcth1aU99Px8iLbdBvJP+G7CjslNBqONiyZ0SOTtogRT2hPwKBgQC7VNEOaLfuCnGaJdQ62AuGjfryD1fD0wbgGZKeSO0PwGI4u3A9g1vtU6VDbTPtdtFqaQddM+mXINWNodZFwux833vV1KMJdLzCT1bK4Ggmj9HFU+095RrjZYXtbodfHrsA4kaNfS6yuazNURea8vdtGRBF9nV7quFmD427pPI48QKBgG8A4UZB2VcOmAdA/j2ghyxVNxvf+PTemRYv0RX5G3EncaTDT8PlvU9/W2qA4h0dENki5GSNz9CgoyJ5E7oXJZdyPH7qezs0sMCfYhhA7+zhiiVvlu0p5DQ+jr8phD6wYfwL/AY5Hu8u9ev1dtjofJ9hXjQ/0AFoUOTpthSfrZpLAoGAFk3b8kY/mAUAT6UvZq4weR3Qgh+XiIZIrEf/L/9o0lZKm4ydqYVJXbF23NdUtnJOLshAizVSG59aLdnWBEpYE+ob+XKu0sJmcxA1OkSLwgOfq0n51kO/9tEwp/tf/NBQ3aMTWWdNNxRqYavFDrrdAM1aJapZhJbs7VTuwCe8e1ECgYEAg1kp7bJu4FaJkIdVc3elL++G/Kr+xg2hw5bCw9m6PS3mtpFfBIbkADrjNRTTlxIaHpKxMEZsmZxs7R6DMzeoufhxm8Rm0nWE8hT8rrHlXgNfHmqVOIq3nEHyhgBWwb7ppNs8NIrLoHA+bhG+bXEiY4akRWTNBDVblQrj3Yy7Klw=';		//商户私钥，填写对应签名算法类型的私钥，如何生成密钥参考：https://docs.open.alipay.com/291/105971和https://docs.open.alipay.com/200/105310
        /*** 配置结束 ***/
        $aliPay = new AlipayService();
        $aliPay->setAppid($appid);
        $aliPay->setReturnUrl($returnUrl);
        $aliPay->setNotifyUrl($notifyUrl);
        $aliPay->setRsaPrivateKey($rsaPrivateKey);
        $aliPay->setTotalFee($payAmount);
        $aliPay->setOutTradeNo($outTradeNo);
        $aliPay->setOrderName($orderName);
        $aliPay->setExtra_common_param($this->order['order_type']);
        $sHtml = $aliPay->doPay();
        echo $sHtml;

    }

    /**
     * 获取支付接口的请求地址
     *
     * @return string
     */
    public function get_payurl(){
    	$this->parameter = array(
            'service'		    => 'create_direct_pay_by_user',	//服务名
            'partner'		    => $this->payment['payment_config']['alipay_partner'],	//合作伙伴ID
            'key'               => $this->payment['payment_config']['alipay_key'],
            '_input_charset'	=> CHARSET,					//网站编码
            'notify_url'	    => SHOP_SITE_URL."/api/payment/alipay/notify_url.php",	//通知URL
            'sign_type'		    => 'MD5',				//签名方式
            'return_url'	    => SHOP_SITE_URL."/api/payment/alipay/return_url.php",	//返回URL
            'extra_common_param'=> $this->order['order_type'],
            'subject'		    => $this->order['subject'],	//商品名称
            'body'			    => $this->order['pay_sn'],	//商品描述
            'out_trade_no'	    => $this->order['pay_sn'],		//外部交易编号
            'payment_type'	    => 1,							//支付类型
            'logistics_type'    => 'EXPRESS',					//物流配送方式：POST(平邮)、EMS(EMS)、EXPRESS(其他快递)
            'logistics_payment'	=> 'BUYER_PAY',				     //物流费用付款方式：SELLER_PAY(卖家支付)、BUYER_PAY(买家支付)、BUYER_PAY_AFTER_RECEIVE(货到付款)
            'receive_name'		=> $_SESSION['member_name'],//收货人姓名
            'receive_address'	=> 'N',	//收货人地址
            'receive_zip'		=> 'N',	//收货人邮编
            'receive_phone'		=> 'N',//收货人电话
            'receive_mobile'	=> 'N',//收货人手机
            'seller_email'		=> $this->payment['payment_config']['alipay_account'],	//卖家邮箱
            'price'             => $this->order['api_pay_amount'],//订单总价
            'quantity'          => 1,//商品数量
            'total_fee'         => 0,//物流配送费用
            'extend_param'      => "isv^sh32",
        );
        $this->parameter['sign']	= $this->sign($this->parameter);
        return $this->create_url();
    }

	/**
	 * 通知地址验证
	 *
	 * @return bool
	 */
	public function notify_verify() {
		$param	= $_POST;
		$param['key']	= $this->payment['payment_config']['alipay_key'];
		$veryfy_url = $this->alipay_verify_url. "partner=" .$this->payment['payment_config']['alipay_partner']. "&notify_id=".$param["notify_id"];
		$veryfy_result  = $this->getHttpResponse($veryfy_url);
		$mysign = $this->sign($param);
		if (preg_match("/true$/i",$veryfy_result) && $mysign == $param["sign"])  {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * 返回地址验证
	 *
	 * @return bool
	 */
	public function return_verify() {
		$param	= $_GET;
		//将系统的控制参数置空，防止因为加密验证出错
		$param['act']	= '';
		$param['op']	= '';
		$param['payment_code'] = '';
		$param['key']	= $this->payment['payment_config']['alipay_key'];
		$veryfy_url = $this->alipay_verify_url. "partner=" .$this->payment['payment_config']['alipay_partner']. "&notify_id=".$param["notify_id"];
		$veryfy_result  = $this->getHttpResponse($veryfy_url);
		$mysign = $this->sign($param);
		if (preg_match("/true$/i",$veryfy_result) && $mysign == $param["sign"])  {
            return true;
		} else {
			return false;
		}
	}

	/**
	 * 
	 * 取得订单支付状态，成功或失败
	 * @param array $param
	 * @return array
	 */
	public function getPayResult($param){
		return $param['trade_status'] == 'TRADE_SUCCESS';
	}

	/**
	 * 
	 *
	 * @param string $name
	 * @return 
	 */
	public function __get($name){
	    return $this->$name;
	}

	/**
	 * 远程获取数据
	 * $url 指定URL完整路径地址
	 * @param $time_out 超时时间。默认值：60
	 * return 远程输出的数据
	 */
	private function getHttpResponse($url,$time_out = "60") {
		$urlarr     = parse_url($url);
		$errno      = "";
		$errstr     = "";
		$transports = "";
		$responseText = "";
		if($urlarr["scheme"] == "https") {
			$transports = "ssl://";
			$urlarr["port"] = "443";
		} else {
			$transports = "tcp://";
			$urlarr["port"] = "80";
		}
		$fp=@fsockopen($transports . $urlarr['host'],$urlarr['port'],$errno,$errstr,$time_out);
		if(!$fp) {
			die("ERROR: $errno - $errstr<br />\n");
		} else {
			if (trim(CHARSET) == '') {
				fputs($fp, "POST ".$urlarr["path"]." HTTP/1.1\r\n");
			} else {
				fputs($fp, "POST ".$urlarr["path"].'?_input_charset='.CHARSET." HTTP/1.1\r\n");
			}
			fputs($fp, "Host: ".$urlarr["host"]."\r\n");
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs($fp, "Content-length: ".strlen($urlarr["query"])."\r\n");
			fputs($fp, "Connection: close\r\n\r\n");
			fputs($fp, $urlarr["query"] . "\r\n\r\n");
			while(!feof($fp)) {
				$responseText .= @fgets($fp, 1024);
			}
			fclose($fp);
			$responseText = trim(stristr($responseText,"\r\n\r\n"),"\r\n");
			return $responseText;
		}
	}

    /**
     * 制作支付接口的请求地址
     *
     * @return string
     */
    private function create_url() {
		$url        = $this->alipay_gateway_new;
		$filtered_array	= $this->para_filter($this->parameter);
		$sort_array = $this->arg_sort($filtered_array);
		$arg        = "";
		while (list ($key, $val) = each ($sort_array)) {
			$arg.=$key."=".urlencode($val)."&";
		}
		$url.= $arg."sign=" .$this->parameter['sign'] ."&sign_type=".$this->parameter['sign_type'];
		return $url;
	}

	/**
	 * 取得支付宝签名
	 *
	 * @return string
	 */
	private function sign($parameter) {
		$mysign = "";
		
		$filtered_array	= $this->para_filter($parameter);
		$sort_array = $this->arg_sort($filtered_array);
		$arg = "";
        while (list ($key, $val) = each ($sort_array)) {
			$arg	.= $key."=".$this->charset_encode($val,(empty($parameter['_input_charset'])?"UTF-8":$parameter['_input_charset']),(empty($parameter['_input_charset'])?"UTF-8":$parameter['_input_charset']))."&";
		}
		$prestr = substr($arg,0,-1);  //去掉最后一个&号
		$prestr	.= $parameter['key'];
        if($parameter['sign_type'] == 'MD5') {
			$mysign = md5($prestr);
		}elseif($parameter['sign_type'] =='DSA') {
			//DSA 签名方法待后续开发
			die("DSA 签名方法待后续开发，请先使用MD5签名方式");
		}else {
			die("支付宝暂不支持".$parameter['sign_type']."类型的签名方式");
		}
		return $mysign;

	}

	/**
	 * 除去数组中的空值和签名模式
	 *
	 * @param array $parameter
	 * @return array
	 */
	private function para_filter($parameter) {
		$para = array();
		while (list ($key, $val) = each ($parameter)) {
			if($key == "sign" || $key == "sign_type" || $key == "key" || $val == "")continue;
			else	$para[$key] = $parameter[$key];
		}
		return $para;
	}

	/**
	 * 重新排序参数数组
	 *
	 * @param array $array
	 * @return array
	 */
	private function arg_sort($array) {
		ksort($array);
		reset($array);
		return $array;

	}

	/**
	 * 实现多种字符编码方式
	 */
	private function charset_encode($input,$_output_charset,$_input_charset="UTF-8") {
		$output = "";
		if(!isset($_output_charset))$_output_charset  = $this->parameter['_input_charset'];
		if($_input_charset == $_output_charset || $input == null) {
			$output = $input;
		} elseif (function_exists("mb_convert_encoding")){
			$output = mb_convert_encoding($input,$_output_charset,$_input_charset);
		} elseif(function_exists("iconv")) {
			$output = iconv($_input_charset,$_output_charset,$input);
		} else die("sorry, you have no libs support for charset change.");
		return $output;
	}
}