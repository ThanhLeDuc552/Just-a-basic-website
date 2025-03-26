		<?php 
		$title = 'Jobs';
		$style = 'jobs.css';
		include_once 'initial_page_settings.inc';
		?>
		<!-- Header -->
		<?php include_once 'header.inc'; ?>
		<?php include 'settings.php' ?>
		<!-- Section 3: Job search -->
		<main>
			<section class="bg-white" id="job-search">
				<div class="container">
					<h2>
						Search Jobs
					</h2>
					<form action="jobs.php" method="get">
						<div class="filter-options">
							<div class="filter-group">
								<label for="loc">Location</label><br>
								<select name="loc" id="loc" class="opt-box">
									<option value="">All</option>
									<?php 
									$cmd = "SELECT DISTINCT Location FROM jobs order by Location";
									$result = mysqli_query($conn, $cmd);
									while ($row = mysqli_fetch_assoc($result)) : ?>
										<option value='<?php echo $row['Location']; ?>' <?php echo (isset($_GET["loc"]) && $_GET["loc"] === $row['Location']) ? 'selected' : ''; ?>><?php echo $row['Location']; ?></option>
									<?php endwhile; ?>
								</select>
							</div>
							<div class="filter-group">
								<label for="contract">Contract</label><br>
								<select id="contract" class="opt-box" name="contract">
									<option value="">All</option>
									<?php 
									$cmd = "SELECT DISTINCT Position FROM jobs order by Position";
									$result = mysqli_query($conn, $cmd);
									while ($row = mysqli_fetch_assoc($result)) : ?>
										<option value='<?php echo $row['Position']; ?>' <?php echo (isset($_GET["contract"]) && $_GET["contract"] === $row['Position']) ? 'selected' : ''; ?>><?php echo $row['Position']; ?></option>
									<?php endwhile; ?>
								</select>
							</div>
							<div class="filter-group">
								<label for="input">Keyword</label><br>
								<input id="input" class="opt-box" placeholder="Search for your position" type="text" value="<?php echo isset($_GET['input']) ? $_GET['input'] : ''; ?>" name="input">
							</div>
							<div class="filter-group"><button type="submit" class="btn btn-general">Filter</button></div>
						</div>
					</form>
				</div>
			</section>
			<!-- Section 4: Jobs -->
			<section class="bg-white" id="job-listing">
				<div class="container">
					<?php 
					$sql = "SELECT * FROM jobs WHERE 1=1";
					$params = [];
					$types = "";
					if (isset($_GET['contract']) && !empty($_GET['contract'])) {
						$sql .= " AND Position = ?";
						$params[] = $_GET['contract'];
						$types .= "s";
					}
			
					if (isset($_GET['loc']) && !empty($_GET['loc'])) {
						$sql .= " AND Location = ?";
						$params[] = $_GET['loc'];
						$types .= "s";
					}
			
					if (isset($_GET['input']) && !empty($_GET['input'])) {
						$sql .= " AND (Title LIKE ? OR Description LIKE ?)";
						$keyword = "%" . $_GET['input'] . "%";
						$params[] = $keyword;
						$params[] = $keyword;
						$types .= "ss";
					}

					$stmt = mysqli_prepare($conn, $sql);
					if (!empty($params)) {
						mysqli_stmt_bind_param($stmt, $types, ...$params);
					}
			
					mysqli_stmt_execute($stmt);
					$result = mysqli_stmt_get_result($stmt);

					if (mysqli_num_rows($result) == 0): 
					?>
					<h2>No jobs found</h2>
					<?php else: ?> 
					<div class="card-align">
					<?php while ($row = mysqli_fetch_assoc($result)): ?>
						<div class="job-card">
							<div class="job-card-header">
								<h2 class="job-title"><?php echo $row["Title"]; ?></h2>
							</div>
							<div class="job-card-body">
								<div class="job-detail">
									<div class="detail-label">Location:</div>
        							<div class="detail-value"><?php echo $row["Location"];?></div>
      							</div>
      							<div class="job-detail">
        							<div class="detail-label">Salary Range:</div>
        							<div class="detail-value"><?php echo $row["Salary"]; ?></div>
      							</div>
      							<div class="job-detail">
        							<div class="detail-label">Reference Code:</div>
        							<div class="detail-value"><?php echo $row["JobReferenceNumber"]; ?></div>
      							</div>
    						</div>
    						<div class="job-card-footer">
      							<a href="<?php echo isset($_SESSION['manager_id']) ? "manage.php" : "apply.php?job_ref=" . $row['JobReferenceNumber']; ?>"><button class="btn btn-general">Apply Now</button></a>
								<a href="job.php?job_ref=<?php echo $row["JobReferenceNumber"]; ?>"><button class="btn btn-general">Discover</button></a>
    						</div>
  						</div>
						<?php endwhile; ?>
					</div>
					<?php 
					endif; 
					mysqli_close($conn);
					?>
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