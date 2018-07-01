<?php defined('InShopNC') or exit('Access Invalid!'); ?>
<style type="text/css">
    .container {
        width: 1010px;
        margin: 0 auto;
    }

    .button {
        border-radius: 2px;
        background: -moz-linear-gradient(center top, #f93, #c60) repeat scroll 0 0 rgba(0, 0, 0, 0);
        border: 1px solid #c93;
        border-radius: 5px;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
        color: #fff !important;
        cursor: pointer;
        display: inline-block;
        font-size: 14px;
        font-weight: bold;
        line-height: normal;
        margin: 0 2px;
        min-width: 80px;
        outline: medium none;
        padding: 5px 13px 6px;
        text-align: center;
        text-decoration: none;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.3);
        transition: all 0.2s linear 0s;
        vertical-align: middle;
        width: auto !important;
    }

    .button:hover {
        background: -moz-linear-gradient(center top, #f90, #960) repeat scroll 0 0 rgba(0, 0, 0, 0);
        color: white;
        text-decoration: none;
    }

    .button:active {
        background: -moz-linear-gradient(center top, #960, #f90) repeat scroll 0 0 rgba(0, 0, 0, 0);
        color: #a9c08c;
        position: relative;
        top: 1px;
    }

    .button:focus {
        box-shadow: 0 0 7px rgba(0, 0, 0, 0.5);
        text-decoration: none;
    }

    .container-bg {
        background: url('<?php echo SHOP_TEMPLATES_URL;?>/images/invite/top.jpg') repeat-x #eee;
        padding-top: 0;
    }

    .invite-bd {
        /*background: url('*/<?php //echo SHOP_TEMPLATES_URL;?>/*/images/invite/center3.jpg') no-repeat;*/
        /*height: 725px;*/
    }

    .invite-form .i-invite-link {
        background-color: #fff;
        border: 1px solid #bbb;
        color: #000;
        padding: 0 4px;
        color: #000;
        font-size: 1.25em;
        height: 45px;
        line-height: 45px;
        vertical-align: middle;
        width: 620px;
    }

    .invite-form .invite-text {
        color: #fd6208;
        margin-left: 25px;
        font-size: 18px;
    }

    .invite-form div {
        margin-left: 25px;
    }


    .invite-share-site {
        alignment: center;
        margin: 20px;
        width: 600px;
    }

    .invite-rebate {
        position: relative;
        top: 420px;
        left: 190px;
        width: 140px;
    }
</style>


</head>
<body>
<div class="container-bg">
    <div class="container">
        <div class="span-24" id="content">
            <div class="invite-bd">
                <div class="invite-share-site clearfix">
                    <a href="<?php echo $output['qrcode']; ?>" download="我的二维码"><img style="border: 0px;" src="<?php echo $output['qrcode']; ?>"/></a>
                    <br />
                    <?php echo $output['myurl'];?>
                </div>
            </div>
        </div>
    </div>
</div>
<!---->