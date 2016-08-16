$(document).ready(function(){
	
	var form = $("#contactForm");
	
	var $id = $('body').attr('data-id');
	
	form.submit(function(){
		if($("#name").val().length < 1 || $("#email").val().length < 1 || $("#subject").val().length < 1 || $("#message").val().length < 1){
			$("#contactMsg").empty();
			$("#contactMsg").append('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>{{ST:error}}!</strong> {{ST:all_field_required}}</div>');
			return false;
		}
		
		var filter = /^[a-zA-Z0-9]+[a-zA-Z0-9_.-]+[a-zA-Z0-9_-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$/;
		if(!filter.test($("#email").val())){
			$("#contactMsg").empty();
			$("#contactMsg").append('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>{{ST:error}}!</strong> {{ST:invalid_email}}</div>');
			return false;
		}
		
		$('#submit').button('loading');
		$.ajax({type:'POST', url: 'send.php?id=$id', data:form.serialize(), dataType: "json", success: function(res) {
    				$('#submit').button('reset');
    				if(res.status == 1){
    					$("#contactMsg").empty();
					$("#contactMsg").append('<div class="alert alert-success"><a class="close" data-dismiss="alert">×</a><strong>{{ST:success}}!</strong> '+ res.error +'</div>');
				}else{
    					$("#contactMsg").empty();
					$("#contactMsg").append('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>{{ST:error}}!</strong> '+ res.error +'</div>');
				}
		}});
		return false
	});
	
	var form2 = $("#contactForm2");
	
	form2.submit(function(){
		if($("#name2").val().length < 1 || $("#email2").val().length < 1 || $("#subject2").val().length < 1 || $("#message2").val().length < 1){
			$("#contactMsg2").empty();
			$("#contactMsg2").append('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>{{ST:error}}!</strong> {{ST:all_field_required}}</div>');
			return false;
		}
		
		var filter = /^[a-zA-Z0-9]+[a-zA-Z0-9_.-]+[a-zA-Z0-9_-]+@[a-zA-Z0-9]+[a-zA-Z0-9.-]+[a-zA-Z0-9]+.[a-z]{2,4}$/;
		if(!filter.test($("#email2").val())){
			$("#contactMsg2").empty();
			$("#contactMsg2").append('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>{{ST:error}}!</strong> {{ST:invalid_email}}</div>');
			return false;
		}
		
		$('#submit2').button('loading');
		$.ajax({type:'POST', url: 'send.php?id=$id', data:form2.serialize(), dataType: "json", success: function(res) {
    				$('#submit2').button('reset');
    				if(res.status == 1){
    					$("#contactMsg2").empty();
					$("#contactMsg2").append('<div class="alert alert-success"><a class="close" data-dismiss="alert">×</a><strong>{{ST:success}}!</strong> '+ res.error +'</div>');
				}else{
    					$("#contactMsg2").empty();
					$("#contactMsg2").append('<div class="alert alert-error"><a class="close" data-dismiss="alert">×</a><strong>{{ST:error}}!</strong> '+ res.error +'</div>');
				}
		}});
		return false
	});
	
	$.get("token.php",function(txt){
		form.append('<input type="hidden" name="ts" value="'+txt+'">');
		form2.append('<input type="hidden" name="ts" value="'+txt+'">');
	});
	
	
	
	
	$("#likeButton").click(function(event) {
  		event.preventDefault();
  		$.get("like.php?id=$id",function(txt){
			if(txt.length > 1){
				$("#found_this_helpful").empty();
				$("#found_this_helpful").append(txt);
			}
		});
	});
});