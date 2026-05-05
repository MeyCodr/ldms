<!DOCTYPE html>
<html>

<head>
	<title>Learning and Development Management System</title>
	<link rel="icon" href="">
	<script src="asset/js/jquery-1.10.2.min.js"></script>
	<link rel="stylesheet" href="asset/css/bootstrap.min.css" />
	<script src="asset/js/bootstrap.min.js"></script>
	<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.6.1/css/all.css">
</head>
<style>
	.flexContainer {
		display: flex;
	}

	.inputField {
		flex: 1;
	}

	body {
		background-image: url('asset/image/norfikri@phn.com.gif');
		background-size: cover;
		background-repeat: no-repeat;
		background-position: center;
		background-attachment: fixed;
		zoom: 75%;

	}

	#log-div {
		width: 520px;
		margin-left: 50px;
	}

	#checkpw {
		width: 39.5px;
		height: 38.5px;
	}

	#lndtitle {
		color: white;
		margin-left: 50px;
	}

	#footlnd {
		margin-top: 15px;
		color: white;
		margin-left: 50px;
	}

	#login {
		background-color: #337ab7;
		color: white;
	}

	@media only screen and (min-width: 375px) and (min-height: 812px) and (max-width: 1000px) {
		body {
			background-image: url('asset/image/mobile-bg-new.png');
			background-repeat: no-repeat;
			background-size: 100% 100%;
			height: 100vh;
		}

		#login-div {
			margin-top: 35.3vh;
		}

		#log-div {
			width: 85.3vw;
			height: 31vh;
			font-size: 3.0em;
			margin-left: 6vw;
			background-color: transparent;
			border-color: transparent;
		}

		#checkpw {
			width: 100px;
			height: 100px;
		}

		input[type="text"] {
			height: 100px;
			font-size: 32px;
		}

		input[type="password"] {
			height: 100px;
			font-size: 32px;
		}

		#eyecon {
			font-size: 2.0em;
		}

		#login {
			margin-top: 70px;
			font-size: 1.5em;
			border-radius: 30px;
			height: 130px;
			background-color: transparent;
			border-color: white;
			color: white;
		}

		#lndtitle {
			display: none;
		}

		#footlnd {
			display: none;
		}

		#titlehead {
			display: none;
		}

		label {
			font-weight: 400;
			color: white;
		}
	}
</style>

<body>
	<br />
	<br />
	<br />
	<br />
	<br />
	<br />
	<br />
	<br />
	<br />
	<div class="container-fluid" style="margin-top:130px">
		<div class="row" id="login-div">
			<div class="col-md-8"></div>
			<div class="col-md-4">
				<div class="panel panel-default" id="log-div">
					<div class="panel-heading" id="titlehead">Login</div>
					<div class="panel-body">
						<form method="post" id="login_form">
							<div class="form-group">
								<label>STAFF NUMBER</label>
								<input type="text" name="username" class="form-control" required autocomplete="off" />
							</div>
							<div class="form-group">
								<label>PASSWORD</label>
								<div class="flexContainer">
									<input type="password" name="password" id="password" class="form-control"
										required />
									<button type="button" class="form-control" id="checkpw" name="button"><i id="eyecon"
											class="fa fa-eye"></i></button>
								</div>
							</div>
							<div class="form-group">
								<input type="submit" id="login" name="login" value="Login" class="btn btn-block" />
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</body>
<footer>
	<div class="col-md-8"></div>
	<div class="col-md-4" style="margin-bottom:15px;">
		<div class="row">
			<br>
			<div class="col-md-2"></div>
			<div class="col-md-8">
				<h6 align="center" color: "black">
					Copyright &copy; 2024 PHN Industry Sdn. Bhd.
					Use with Google Chrome or Javascript enabled IE and Firefox.
					</br>All Rights Reserved. | Web design by PHN IT Department
				</h6>
			</div>

			<div class="col-md-2"></div>
		</div>
		<div class="row">
			<br>
			<div class="col-md-2"></div>

			<div class="col-md-8">
				<h6 align="center" color: "black">
					Please contact norfikri@phn.com.my or call ext: 190 (norfikri for any enquiries)
					<!-- </br>All Rights Reserved.  |  Web design by PHN IT Department -->
				</h6>
			</div>

			<div class="col-md-2"></div>
		</div>
	</div>
</footer>
<script>
	$(document).ready(function () {
		$(document).on('submit', '#login_form', function (event) {
			event.preventDefault();
			var form_data = $(this).serialize();
			$.ajax({
				url: "login-action.php",
				method: "POST",
				data: form_data,
				success: function (data) {
					var response = JSON.parse(data)
					if ((response.message) == 'login') {
						swal(
							'Logged In!',
							'You have succesfully logged in.',
							'success'
						).then(function () {
							localStorage.clear();
							window.location = "index.php";
						})
					} else if ((response.message) == 'notlogin') {
						swal(
							'Not Logged In!',
							'You did not succesfully logged in. Please check your username and password.',
							'error'
						)
						$('#login_form')[0].reset();
					}
				}
			})
		});

		$("#checkpw").click(function () {
			icon = $(this).find("i");
			icon.toggleClass("fa-eye fa-eye-slash");
			var input = $("#password");
			input.attr('type') === 'password' ? input.attr('type', 'text') : input.attr('type', 'password')
		});
	});
</script>

</html>