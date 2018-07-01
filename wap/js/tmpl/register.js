$(function(){
    var key = '';

    $.ajax({//获取区域列表
        type:'post',
        url:ApiUrl+'/index.php?act=member_address&op=area_list',
        data:{key:key},
        dataType:'json',
        success:function(result){
            // checklogin(result.login);
            var data = result.datas;
            var prov_html = '';
            for(var i=0;i<data.area_list.length;i++){
                prov_html+='<option value="'+data.area_list[i].area_id+'">'+data.area_list[i].area_name+'</option>';
            }
            $("select[name=prov]").append(prov_html);
        }
    });


    $("select[name=prov]").change(function(){//选择省市
        var prov_id = $(this).val();
        $.ajax({
            type:'post',
            url:ApiUrl+'/index.php?act=member_address&op=area_list',
            data:{key:key,area_id:prov_id},
            dataType:'json',
            success:function(result){
                // checklogin(result.login);
                var data = result.datas;
                var city_html = '<option value="">请选择...</option>';
                for(var i=0;i<data.area_list.length;i++){
                    city_html+='<option value="'+data.area_list[i].area_id+'">'+data.area_list[i].area_name+'</option>';
                }
                $("select[name=city]").html(city_html);
                $("select[name=region]").html('<option value="">请选择...</option>');
            }
        });
    });

    $("select[name=city]").change(function(){//选择城市
        var city_id = $(this).val();
        $.ajax({
            type:'post',
            url:ApiUrl+'/index.php?act=member_address&op=area_list',
            data:{key:key,area_id:city_id},
            dataType:'json',
            success:function(result){
                checklogin(result.login);
                var data = result.datas;
                var region_html = '<option value="">请选择...</option>';
                for(var i=0;i<data.area_list.length;i++){
                    region_html+='<option value="'+data.area_list[i].area_id+'">'+data.area_list[i].area_name+'</option>';
                }
                $("select[name=region]").html(region_html);
            }
        });
    });


    $.sValid.init({//注册验证
        rules:{
        	username:"required",
            userpwd:"required",            
            password_confirm:"required",
            // email:{
            // 	required:false,
            // 	email:true
            // }//,
            //vprov:"required",
            //vcity:"required",
            //vregion:"required"
        },
        messages:{
            username:"用户名必须填写！",
            userpwd:"密码必填!", 
            password_confirm:"确认密码必填!",
            // email:{
            // 	required:"邮件必填!",
            // 	email:"邮件格式不正确"
            // },
            // vprov:"省份必填！",
            // vcity:"城市必填！",
            // vregion:"区县必填！"
        },
        callback:function (eId,eMsg,eRules){
            if(eId.length >0){
                var errorHtml = "";
                $.map(eMsg,function (idx,item){
                    errorHtml += "<p>"+idx+"</p>";
                });
                $(".error-tips").html(errorHtml).show();
            }else{
                $(".error-tips").html("").hide();
            }
        }  
    });
	
	$('#loginbtn').click(function(){

	    var inviter_id  = getcookie('spring_inviter_id');

	    var username = $("input[name=username]").val();
		var pwd = $("input[name=pwd]").val();
		var password_confirm = $("input[name=password_confirm]").val();
		var email = $("input[name=email]").val();
		var client = $("input[name=client]").val();

		var province = $("select[name=prov]").val();
		var city = $("select[name=city]").val();
		var area = $("select[name=region]").val();

		debugger;
		
		if($.sValid()){
			$.ajax({
				type:'post',
				url:ApiUrl+"/index.php?act=login&op=register",	
				data:{username:username,password:pwd,password_confirm:password_confirm,email:email,client:client,province:province,city:city,area:area,inviter_id:inviter_id},
				dataType:'json',
				success:function(result){
					if(!result.datas.error){
						if(typeof(result.datas.key)=='undefined'){
							return false;
						}else{
							addcookie('username',result.datas.username);
							addcookie('key',result.datas.key);
							location.href = WapSiteUrl+'/tmpl/member/member.html';
						}
						$(".error-tips").hide();
					}else{
						$(".error-tips").html(result.datas.error).show();
					}
				}
			});			
		}
	});
});