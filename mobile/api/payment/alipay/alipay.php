<?php 
/**
 * 支付接口//zmr>v30
 * 
 *
 */
defined('InShopNC') or exit('Access Invalid!');

require_once("lib/alipay_submit.class.php");
require_once("AlipayService.class.php");
class alipay {

	
	/**************************调用授权接口alipay.wap.trade.create.direct获取授权码token**************************/
		
	//返回格式
	private  $format = "";
	//必填，不需要修改
	
	//返回格式
	private $v = "";
	//必填，不需要修改
	
	//请求号
	private $req_id = "";
	//必填，须保证每次请求都是唯一
	
	//**req_data详细信息**
	
	//服务器异步通知页面路径
	private $notify_url = "";
	//需http://格式的完整路径，不允许加?id=123这类自定义参数
	
	//页面跳转同步通知页面路径
	private $call_back_url = "";
	//需http://格式的完整路径，不允许加?id=123这类自定义参数
	
	//卖家支付宝账户
	private $seller_email = "";
	//必填
	
	//商户订单号
	private $out_trade_no = "";
	//商户网站订单系统中唯一订单号，必填
	
	//订单名称
	private $subject = "";
	//必填
	
	//付款金额
	private $total_fee = "";
	//必填
	
	//请求业务参数详细
	private $req_data = "";
	//必填
	
	//配置
	private $alipay_config = array();
	
	/************************************************************/
	
	public function submit($param){
		$this->format	= 'xml';
		$this->v		= '2.0';
		$this->req_id	= date('Ymdhis');
		$this->notify_url		= MOBILE_SITE_URL.'/api/payment/alipay/notify_url.php';
		$this->call_back_url	= MOBILE_SITE_URL.'/api/payment/alipay/call_back_url.php';
		$this->seller_email		= $param['alipay_account'];
		//v3-b10
		$this->out_trade_no		= $param['order_sn'].'-'.$param['order_type'];
		$this->subject			= $param['order_sn'];
		$this->total_fee		= $param['order_amount'];
		$this->alipay_config 	= array(
			'partner' => $param['alipay_partner'],
			'key' => $param['alipay_key'],
			'private_key_path' => 'key/rsa_private_key.pem',
			'ali_public_key_path' => 'key/alipay_public_key.pem',
			'sign_type' => 'MD5',
			'input_charset' => 'utf-8',
			'cacert' => getcwd().'\\cacert.pem',
			'transport' => 'http'
		);



        /*** 请填写以下配置信息 ***/
        $appid = '2018062760449267';  //https://open.alipay.com 账户中心->密钥管理->开放平台密钥，填写添加了电脑网站支付的应用的APPID
        $returnUrl =$this->call_back_url;     //付款成功后的同步回调地址
        $notifyUrl = $this->notify_url;     //付款成功后的异步回调地址
        $outTradeNo = $this->out_trade_no;    //你自己的商品订单号
        $payAmount = $this->total_fee;         //付款金额，单位:元
        $orderName = $this->subject;    //订单标题
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
        $sHtml = $aliPay->doPay();
        echo $sHtml;

return;


		//请求业务参数详细
		$req_data			= '<direct_trade_create_req><notify_url>' . $this->notify_url . '</notify_url><call_back_url>' . $this->call_back_url . '</call_back_url><seller_account_name>' . $this->seller_email . '</seller_account_name><out_trade_no>' . $this->out_trade_no . '</out_trade_no><subject>' . $this->subject . '</subject><total_fee>' . $this->total_fee . '</total_fee></direct_trade_create_req>';
		//必填
		
		//构造要请求的参数数组，无需改动
		$para_token = array(
				"service" => "alipay.wap.trade.create.direct",
				"partner" => trim($this->alipay_config['partner']),
				"sec_id" => trim($this->alipay_config['sign_type']),
				"format"	=> $this->format,
				"v"	=> $this->v,
				"req_id"	=> $this->req_id,
				"req_data"	=> $req_data,
				"_input_charset"	=> trim(strtolower($this->alipay_config['input_charset']))
		);
		
		//建立请求
		$alipaySubmit = new AlipaySubmit($this->alipay_config);
		$html_text = $alipaySubmit->buildRequestHttp($para_token);
		
		//URLDECODE返回的信息
		$html_text = urldecode($html_text);
		
		//解析远程模拟提交后返回的信息
		$para_html_text = $alipaySubmit->parseResponse($html_text);
		
		//获取request_token
		$request_token = $para_html_text['request_token'];
		
		
		/**************************根据授权码token调用交易接口alipay.wap.auth.authAndExecute**************************/
		
		//业务详细
		$req_data = '<auth_and_execute_req><request_token>' . $request_token . '</request_token></auth_and_execute_req>';
		//必填
		
		//构造要请求的参数数组，无需改动
		$parameter = array(
				"service" => "alipay.wap.auth.authAndExecute",
				"partner" => trim($this->alipay_config['partner']),
				"sec_id" => trim($this->alipay_config['sign_type']),
				"format"	=> $this->format,
				"v"	=> $this->v,
				"req_id"	=> $this->req_id,
				"req_data"	=> $req_data,
				"_input_charset"	=> trim(strtolower($this->alipay_config['input_charset']))
		);
		
		//建立请求
		$alipaySubmit = new AlipaySubmit($this->alipay_config);
		$html_text = $alipaySubmit->buildRequestForm($parameter, 'get', '正在跳转支付页面...');
		return '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html>
				<head>
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
					<title>支付宝即时到账交易接口接口</title>
				</head>'.$html_text.'
				</body>
				</html>';
	}

    /**
     * 获取return信息
     */
    public function getReturnInfo($payment_config) {

        return array(
            //商户订单号
            'out_trade_no' => $_GET['out_trade_no'],
            //支付宝交易号
            'trade_no' => $_GET['trade_no'],
        );




        $verify = $this->_verify('return', $payment_config);

        if($verify) {
            return array(
                //商户订单号
                'out_trade_no' => $_GET['out_trade_no'],
                //支付宝交易号
                'trade_no' => $_GET['trade_no'],
            );
        }

        return false;
    }

    /**
     * 获取notify信息
     */
    public function getNotifyInfo($payment_config) {
        $verify = $this->_verify('notify', $payment_config);

        if($verify) {
            $notify_data = $_POST['notify_data'];
            //解析notify_data
            //注意：该功能PHP5环境及以上支持，需开通curl、SSL等PHP配置环境。建议本地调试时使用PHP开发软件
            $doc = new DOMDocument();
            $doc->loadXML($notify_data);

            if( ! empty($doc->getElementsByTagName( "notify" )->item(0)->nodeValue) ) {
                //商户订单号
                $out_trade_no = $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;
                //支付宝交易号
                $trade_no = $doc->getElementsByTagName( "trade_no" )->item(0)->nodeValue;
                //交易状态
                $trade_status = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue;

                if($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
                    return array(
                        //商户订单号
                        'out_trade_no' => $out_trade_no,
                        //支付宝交易号
                        'trade_no' => $trade_no,
                    );
                }
            }
        }

        return false;
    }

    /**
     * 验证返回信息
     */
    private function _verify($type, $payment_config) {

        if(empty($payment_config)) {
            return false;
        }

		$alipay_config = array(
			'partner' => $payment_config['alipay_partner'],
			'key' => $payment_config['alipay_key'],
			'private_key_path' => 'key/rsa_private_key.pem',
			'ali_public_key_path' => 'key/alipay_public_key.pem',
			'sign_type' => 'MD5',
			'input_charset' => 'utf-8',
			'cacert' => getcwd().'\\cacert.pem',
			'transport' => 'http'
		);

        require_once(BASE_PATH.DS.'api/payment/alipay/lib/alipay_notify.class.php');

		//计算得出通知验证结果
		$alipayNotify = new AlipayNotify($alipay_config);

        switch ($type) {
            case 'notify':
                $verify_result = $alipayNotify->verifyNotify();
                break;
            case 'return':
                $verify_result = $alipayNotify->verifyReturn();
                break;
            default:
                $verify_result = false;
                break;
        }

        return $verify_result;
    }

}
