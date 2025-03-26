		<?php 
		$title = 'Innovating the Future of Technology';
		$style = 'index.css';
		include_once 'initial_page_settings.inc';
		?>
		<!-- Navigation Bar -->
		<?php include_once 'header.inc'; ?>
		<!-- Section 1: Introduction -->
		<section class="bg-white" id="introduction">
			<div class="container">
				<!-- Container box -->
				<div>
					<h1>
					Immerse yourself in a world of technologies
					</h1>
					<p>
						Etech drives digital transformation with AI, cloud, and software solutions, optimizing businesses and enhancing customer experiences. With a dynamic culture and focus on learning, we attract top tech talent.            
					</p>
				</div>
				<div class="img">
					<img alt="A smiling entrepeneur" src="styles/images/business/business_man.png">
				</div>
			</div>
		</section>
		<!-- Section 2: nha tai tro -->
		<section class="bg-purple-100" id="technology">
			<div class="container">
				<!-- Container box -->
				<div class="logo-grid">
					<img class="logo-img" alt="HTML5 logo" src="styles/images/logo/html5.svg">
					<img class="logo-img" alt="CSS logo" src="styles/images/logo/css.svg">
					<img class="logo-img" alt="PHP logo" src="styles/images/logo/php.svg">
					<img class="logo-img" alt="MySQL logo" src="styles/images/logo/mysql.svg">
				</div>
			</div>
		</section>
		<!-- Section 3: Job search -->
		<section class="bg-white" id="benefit">
			<div class="container">
				<div class="img">
					<img alt="Working" src="styles/images/business/working.png">
				</div>
				<div class="content">
					<h2><span class="highlight">Benefits</span> When Working in the Company</h2>
					<div class="benefit-item">
						<div class="icon"><img src="styles/images/business/work-life-balance.png" alt="icon"></div>
						<div class="text">
							<h3>Work-Life Balance</h3>
							<p>Flexible schedules and remote work options.</p>
						</div>
					</div>
					<div class="benefit-item">
						<div class="icon"><img src="styles/images/business/career-growth.png" alt="icon"></div>
						<div class="text">
							<h3>Career Growth</h3>
							<p>Training programs and mentorship opportunities.</p>
						</div>
					</div>
					<div class="benefit-item">
						<div class="icon"><img src="styles/images/competitive-salary.png" alt="icon"></div>
						<div class="text">
							<h3>Competitive Salary</h3>
							<p>Above industry-average compensation packages.</p>
						</div>
					</div>
					<div class="benefit-item">
						<div class="icon"><img src="styles/images/business/wellbeing.png" alt="icon"></div>
						<div class="text">
							<h3>Wellbeing</h3>
							<p>Comprehensive insurance and wellness programs.</p>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- Section 6: Reviewer -->
		<section class="bg-purple-100" id="review">
			<div class="container" id="container-one">
				<!-- Container box -->
				<div>
					<h2>
						Employee's Testimonials
					</h2>
					<p>
						Here's what our employees have to say about their transformational working experiences. Real stories, real impact.
					</p>
				</div>
			</div>
			<div class="container">
				<div id="card-align">
					<div class="review-card">
						<img src="styles/images/personal_img/anh.jpg" alt="Lê Kim Anh">
						<h3 class="reviewer-name">Lê Kim Anh</h3>
						<span class="reviewer-job">Web Developer</span>
						<div class="stars">★★★★★</div>
						<p class="reviewer-review">The facilities here are top-notch! Never in my life can I workout while working, all in the same place!</p>
					</div>
					<div class="review-card">
						<img src="styles/images/personal_img/an.jpg" alt="Hoang An">
						<h3 class="reviewer-name">Trịnh Văn Hoàng An</h3>
						<span class="reviewer-job">Cybersecurity Analyst</span>
						<div class="stars">★★★★★</div>
						<p class="reviewer-review">Being able to working on cutting-edge technologies was a game-changer for me. Absolutely transformative experience!</p>
					</div>
					<div class="review-card">
						<img src="styles/images/personal_img/duong.jpg" alt="Tung Duong Do">
						<h3 class="reviewer-name">Đỗ Tùng Dương</h3>
						<span class="reviewer-job">Data Analyst</span>
						<div class="stars">★★★★★</div>
						<p class="reviewer-review">My work-life balance has never been better. The team is supportive, and the work is engaging!</p>
					</div>
					<div class="review-card">
						<img src="styles/images/personal_img/thanh.jpg" alt="Le Duc Thanh">
						<h3 class="reviewer-name">Lê Đức Thành</h3>
						<span class="reviewer-job">Data Engineer</span>
						<div class="stars">★★★★★</div>
						<p class="reviewer-review">The leader is very supportive and encourages us to grow professionally. I feel valued and respected!</p>
					</div>
				</div>
			</div>
		</section>
		
		<!-- Section 5: Application -->
		<section class="bg-white" id="apply">
			<div class="container">
				<!-- Container box -->
				<div id="apply-content">
					<h2>
						Sounds Intriguing Enough ? Come To Us By Applying !
					</h2>
					<p>
						Employees at Etech enjoy a range of benefits designed to foster professional growth and work-life balance.
					</p>
					<ul>
						<li>
							Competitive Salaries & Performance Bonuses
						</li>
						<li>
							Flexible Work Arrangements (Hybrid/Remote Options)
						</li>
						<li>
							Comprehensive Health & Wellness Benefits
						</li>
						<li>
							Ongoing Learning & Development Programs
						</li>
						<li>
							Employee Stock Options & Retirement Plans
						</li>
						<li>
							Tech Stipends & Cutting-Edge Work Tools
						</li>
					</ul>
					<div class="actions">
						<?php if (isset($_SESSION["manager_id"])): ?>
						<a href="manage.php">
						<?php else: ?>
						<a href="apply.php">
						<?php endif; ?>
							<button class="btn btn-general">Apply Now</button>
						</a>
					</div>
				</div>
				<div id="img">
					<img alt="Cheering" src="styles/images/business/cheering.png">
				</div>
			</div>
		</section>
		<?php include_once 'footer.inc'; ?>