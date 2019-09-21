<?php
$APP_ID = "<<APP_ID>>";
$APP_SECRET = "<<APP_SECRET>>";
$File_PATH = "Tokens.txt";    // This will create file on root folder, if you need to change the location give full path here and allow rea/write permission.

if(isset($_GET['ut'])){
	echo "here";
   $User_ID = "";
   $User_Name = "";
   $Token = "";
   if (isset($_GET['id'])){
	   $User_ID = htmlspecialchars($_GET["id"]);
	   
   }
   if (isset($_GET['u'])){
	   $User_Name = htmlspecialchars($_GET["u"]);
	   
   }
   if (isset($_GET['t'])){
	   $Token = htmlspecialchars($_GET["t"]);
	   
   }
   // Step 1: Get Long live Token for user;
	$url = 'https://graph.facebook.com/oauth/access_token?grant_type=fb_exchange_token';
  
	$TUrl = urlencode($url) . '&client_id=' . urlencode($APP_ID) .'&client_secret=' . urlencode($APP_SECRET) . '&fb_exchange_token=' . $Token;
  
	$contents = file_get_contents(urldecode($TUrl));
	$j = json_decode($contents);
	
	$User_Long_Token = $j->access_token;
   
   // Get Page Accounts For Long Live Token and that should reutrn never expire tokens for page.
   
    $url = 'https://graph.facebook.com/v4.0/'. urlencode($User_ID).'/accounts?access_token=' . urlencode($User_Long_Token);

	$contents = file_get_contents(urldecode($url));

	if($contents !== false){
		 $myfile = fopen($File_PATH, "a") or die("Unable to open file!");
		 $txt = "*******************************Token For Page**************************\r\n";
		 $txt .= 'User_Name: ' . $User_Name ;
		 $txt .= "\r\n";
		 $txt .= 'Created On: ' . date("Y/m/d h:i:sa");
         $txt .= "\r\n";
         $txt .= 'Page Tokens: ' ;
		 $txt .= "\r\n";
         $txt .= "\r\n";
		 $txt .= $contents;
		 $txt .= "\r\n";        
		 $txt .= "*************************************************************************\r\n\r\n";
         fwrite($myfile, $txt);

        fclose($myfile);
	}
	
	exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
 <title>Facebook Post</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <style>
        .dropdown-menu>li>a {
    display: block;
    padding: 3px 20px;
    clear: both;
    font-weight: 400;
    line-height: 1.42857143;
    color: #333;
    white-space: nowrap;
}
    </style>
</head>

<body>
    
    
<div class="container" style="max-width:1000px; padding-top:10px">
	<div class="logout btn-group float-right" style="padding-left: 10px">
		<button type="button" class="btn btn-info btn-sm float-right">
			<span id="spnUserName">Welcome</span><span class="caret"></span>
		</button>
	</div>
	<div class="fblogin float-right">
		<fb:login-button width="200px" size="large" data-size="large" scope="public_profile,email,manage_pages,publish_pages" onlogin="checkLoginState();"></fb:login-button>
	</div>
	<h2>Show Post</h2>
    <div class="form-group">
		<label for="ddlPage">Select Page:</label>
		<select  class="form-control" id="ddlPage" name="ddlPage"> 
		</select>
    </div>
	
	<div class="form-group">
		<button type="button" class="btn btn-secondary btn-lg" style="display:none">Back</button>
		<button type="button" id="btnSubmit" class="btn btn-primary btn-lg" onclick="ShowPosts();" style="display:none">Show Posts</button>
		<button class="btn btn-primary btn-lg" type="button" id="btnLoading" disabled style="display: none">
			<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
			Posting...
		</button>
   </div>
   <div class="form-group">
   <ul id="liPost" class="list-group">
	<li class="list-group-item">
	 <h6>Posts</h6>
	</li>
</ul>
</div>
   <div  id="dvLoadMore" class="form-group text-center">
   
</div>
<a href="#" id="back-to-top" title="Back to top" style="display:none">&uarr; Back to top</a>
</div>
 <input type="hidden" id="hdnNext" name="hdnNext" Value="" />
<script>

$(document).ready(function() {
   $('#back-to-top').on('click', function (e) {
        e.preventDefault();
        $('html,body').animate({
            scrollTop: 0
        }, 700);
    });
	
	   
});
function ShowPosts(){

	var PageID = $("#ddlPage option:selected").attr("attr-Pageid");
    var FbTokenID = $("#ddlPage option:selected").val();
	var target = '/'+ PageID +'/posts';
	var Html = "";
	var after = "";
	after = document.getElementById("hdnNext").value;
	if (after != ""){
		after = "&after=" + after;
	}
	
	 $.ajax({
                url: 'https://graph.facebook.com/v4.0/'+ PageID +'/posts?access_token=' + FbTokenID + '&fields=created_time,message,full_picture&limit=30' + after,
                type: 'GET',
                success:function (response) {
                   
					var myObj = response.data;
					
					$.each(myObj, function(propName, propVal) {
					var id = propVal.id;
					var message ="";
					var pic ="";
					//console.log(typeof propVal.message);
					
					if (typeof propVal.message != "undefined"){
						message = propVal.message;
						
					}
					if(typeof propVal.full_picture != "undefined"){
						pic = "<img style='width:200px' src=" + propVal.full_picture + " />";
					}
					var createdDate = propVal.Date;
					
					var liHtml = "<li class='list-group-item'><div class='d-flex w-100 justify-content-between'><small class='mb-1'>id: " +  propVal.id +" </small><small>"+ new Date(propVal.created_time) +"</small></div><p class='mb-1'>" +  message + "</p>" + pic + "</li>"
					
					$("#liPost li:last").after(liHtml);
					console.log(propVal.id);
				
				

				}); 
				var loadMore = "";
				
				if (response.data.length > 0 && typeof response.paging.cursors.after != "undefined"){
				   document.getElementById("hdnNext").value = response.paging.cursors.after;
					loadMore = '<a href="javascript:void(0);" id="load-next" title="Load More" onclick="ShowPosts();">Show More</a>'
					$("#dvLoadMore").html(loadMore);
				}
				else{
					$("#dvLoadMore").html("");
				}
				
				$("#back-to-top").show();
				
			},
                error:function (data) {
                   alert("There is a problem in in fetching posts. Please try again.")
                },
		});
}

function statusChangeCallback(response) {
	
    if (response.status === 'connected') {  
		var User_Short_Token = response.authResponse.accessToken;
		 
		
        GetUserInfo(User_Short_Token);
        GetPages();
		$(".fblogin").hide();
	} 
	else {
      $(".fblogin").show();
    }
  }
  
  function checkLoginState() {
	FB.getLoginStatus(function(response) {
      statusChangeCallback(response);
    });
  }
  
  window.fbAsyncInit = function() {
    FB.init({
      appId      :   '<?php echo $APP_ID ?>',  //Change FacebookAPP ID here
      cookie     : true,  // enable cookies to allow the server to access 
      xfbml      : true,  // parse social plugins on this page
      version    : 'v3.3' // The Graph API version to use for the call
    });
  
    FB.getLoginStatus(function(response) {
      statusChangeCallback(response);
    });
};

(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "https://connect.facebook.net/en_US/sdk.js";
    fjs.parentNode.insertBefore(js, fjs);
  }(document, 'script', 'facebook-jssdk'));
  
  function GetPages() {
	  
	  
	FB.api('/me/accounts', function(response) {
    var Pages="";
    var myObj = response.data;
    var ddlHtml = ""
     
	$.each(myObj, function(propName, propVal) {
		var AccessToken = propVal.access_token;//'document.getElementById("hdnToken").value;
		var PageName = propVal.name;
		var PageID = propVal.id;
		$('#ddlPage')
			.append($("<option></option>")
                    .attr("value",propVal.access_token)
                    .attr("attr-Pageid",propVal.id)
                    .attr("attr-fbTokenID",'1')
                    .text(propVal.name)); 

		});
	});
	$("#btnSubmit").show();
  }
    function GetUserInfo(User_Short_Token) {
		FB.api('/me','GET',{"fields":"id,name"},function(response) {
			
			var userName = response.name;
			var userID = response.id;
			$(".fblogin").hide();
			$("#spnUserName").html("Hi " + userName + "");
			$(".logout").show();
			// Save Tokens
			var xhr = new XMLHttpRequest();
			xhr.open("GET", "Post_Revamp.php?t=" + User_Short_Token +"&u="+ userName +"&id="+ userID +"&ut=1", true);
			xhr.send() ;
	  }
	);
    }     
</script>
</body>
</html>
