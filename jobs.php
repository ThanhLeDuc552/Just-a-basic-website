<html lang="en">
	<head>
		<meta charset="utf-8"/>
		<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
		<title>
			Etech: Innovating the Future of Technology
		</title>
		<link rel="stylesheet" href="https://use.typekit.net/ilv8ihq.css">
		<link rel="stylesheet" href="styles/style.css">
		<!-- General styling -->
		<link rel="stylesheet" href="styles/jobs.css">
		<!-- Jobs page styling -->
	</head>
	<body>
		<!-- Header -->
		<?php include_once 'header.inc'; ?>
		<!-- Section 3: Job search -->
		<main>
			<section class="bg-white">
				<div class="container">
					<h2>
						Search Jobs
					</h2>
					<div class="bg-white">
						<form action="jobs.php" method="get">
							<div class="filter-options">
								<div class="filter-group">
									<label for="loc">Location</label><br>
									<select name="loc" id="loc" class="opt-box">
										<option value="all">All</option>
										<option value="danang">Da Nang City</option>
										<option value="hanoi">Ha Noi City</option>
										<option value="hochiminh">Ho Chi Minh City</option>
									</select>
								</div>
								<div class="filter-group">
									<label for="contract">Contract</label><br>
									<select id="contract" class="opt-box">
										<option value="all">All</option>
										<option value="Full-time">Full-time</option>
										<option value="Contract">Contract</option>
									</select>
								</div>
								<div class="filter-group">
									<label for="input">Keyword</label><br>
									<input id="input" class="opt-box" placeholder="Search for your position" type="text">
								</div>
								<div class="actions"><button type="submit">Filter</button></div>
							</div>
						</form>
					</div>
				</div>
			</section>
			<!-- Section 4: Jobs -->
			<section class="bg-white" >
				<div class="container">
					<div class="card-align">
						<div class="job-card">
							<a href="page1.php">
								<img alt="cybersecurity analyst" src="styles/images/cybersecurity.webp">
								<div class="location">
									<span class="tag">Da Nang City</span>
									<span class="jobref">ICA123</span>
								</div>
								<h3 class="title">
									Cybersecurity Analyst
								</h3>
								<div class="price-instructor">
									<div class="price">$205.000 - $300.000</div>
								</div>
							</a>
						</div>
						<div class="job-card">
							<a href="page2.php">
								<img alt="software engineer" src="styles/images/software-engineer.png">
								<div class="location">
									<span class="tag">Ho Chi Minh City</span>
									<span class="jobref">SE567</span>
								</div>
								<h3 class="title">
									Software Engineer
								</h3>
								<div class="price-instructor">
									<div class="price">$100.500 - $250.000</div>
								</div>
							</a>
						</div>
						<div class="job-card">
							<a href="page3.php">
								<img alt="AI engineer" src="styles/images/artificial-intelligence.png">
								<div class="location">
									<span class="tag">Ha Noi City</span>
									<span class="jobref">AI647</span>
								</div>
								<h3 class="title">
									AI Engineer
								</h3>
								<div class="price-instructor">
									<div class="price">$130.000 - $203.600</div>
								</div>
							</a>
						</div>
					</div>
				</div>
			</section>
			<div class="bg-purple-100 more">
				<div class="section">
					<input type="checkbox" id="toggle-checkbox-1" hidden>
					<label for="toggle-checkbox-1" class="toggle-label">YOUR LIFE AT ETECH</label>
					<div class="toggle-content">
						<p>In a world where technology never stands still, we understand that, dedication to our clients success, innovation that matters, and trust and personal responsibility in all our relationships, lives in what we do as ETechers as we strive to be the catalyst that makes the world work better.
							Being an ETech means you’ll be able to learn and develop yourself and your career, you’ll be encouraged to be courageous and experiment everyday, all whilst having continuous trust and support in an environment where everyone can thrive whatever their personal or professional background.
						</p>
					</div>
				</div>
				<div class="section">
					<input type="checkbox" id="toggle-checkbox-2" hidden>
					<label for="toggle-checkbox-2" class="toggle-label">OTHER RELEVANT JOB DETAILS</label>
					<div class="toggle-content">
						<p>When applying to jobs of your interest, we recommend that you do so for those that match your experience and expertise. Our recruiters advise that you apply to not more than 3 roles in a year for the best candidate experience. For additional information about location requirements, please discuss with the recruiter following submission of your application.</p>
					</div>
				</div>
			</div>
		</main>
		<?php include_once 'footer.inc'; ?>
	</body>
</html>