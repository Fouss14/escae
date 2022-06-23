<?php require 'config.php'; ?>

	<!-- Masthead -->
	<header class="masthead text-white text-center">
		<div class="overlay"></div>
		<div class="container head-container">
		<div class="row">
			<div class="col-xl-9 mx-auto master-heading">
				<h1 class="master-title" class="mb-5">Free Google Page Experience Checker</h1>
			</div>
			<div class="col-md-10 col-lg-8 col-xl-7 mx-auto">
				<form action="/" name="submitform" id="resultForm" method="post">
					<div class="input-group">

					<?php if ( isset( $_POST['url'] ) ) { ?>

					<input type="url" value="<?php echo $_POST['url']; ?>" class="form-control input_url" placeholder="https://fastwpspeed.com/">
					<script>
					window.onload = function(){

						$("#resultForm").submit();
					};
					</script>
				<?php } else { ?>
					<input type="url" class="form-control input_url" placeholder="https://fastwpspeed.com/">
					<?php } ?>

					<div class="input-group-append">
						<button class="btn btn-primary" type="submit" id="input_btn">RUN TEST</button>
					</div>
					</div>
				</form>
			</div>
		</div>
		<div class="row ">
			<div class="col-md-12">
				<br>
				<div class="loading-progress progress-text">
					<div id="loader-wrapper">
					<div class="loader">
						<div class="line"></div>
						<div class="line"></div>
						<div class="line"></div>
						<div class="line"></div>
						<div class="line"></div>
						<div class="line"></div>
						<div class="subline"></div>
						<div class="subline"></div>
						<div class="subline"></div>
						<div class="subline"></div>
						<div class="subline"></div>
						<div class="loader-circle-1"><div class="loader-circle-2"></div></div>
						<div class="needle"></div>
						<div class="loading">Loading</div>
					</div>
					</div>
				</div>
				<!-- <p class="text-center text-danger" id="error_alert"></p> -->
				<div class="alert alert-danger" role="alert" id="invalid_alert">
					<i class="fas fa-exclamation-triangle    "></i> Please Enter a Valid Url
				</div>
			</div>
		</div>

		</div>
		<div class="progress-area loading-progress ">
		<div class="slider">
			<div class="line"></div>
			<div class="subline inc"></div>
			<div class="subline dec"></div>
		</div>
		</div>
	</header>
	<div class="header-platform result-only">
	<ul class="nav nav-tabs platformTablist" role="tablist">
		<li class="nav-item">
			<a class="nav-link active" id="mobile-tab" data-toggle="tab" href="#mobileTab" role="tab" aria-controls="mobileTab" aria-selected="true"><i class="fas fa-mobile-alt    "></i> Mobile</a>
		</li>
		<li class="nav-item">
			<a class="nav-link" id="desktop-tab" data-toggle="tab" href="#desktopTab" role="tab" aria-controls="optimizations" aria-selected="false"><i class="fas fa-desktop    "></i> Desktop</a>
		</li>
		</ul>
	</div>
