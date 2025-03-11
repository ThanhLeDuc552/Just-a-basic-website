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
		<?php include 'settings.php' ?>
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
										<option value="">All</option>
										<?php 
										$cmd = "SELECT DISTINCT Location FROM jobs order by Location";
										$result = mysqli_query($conn, $cmd);
										while ($row = mysqli_fetch_assoc($result)) {
											echo "<option value='" . $row['Location'] . "'>" . $row['Location'] . "</option>";
										}
										?>
									</select>
								</div>
								<div class="filter-group">
									<label for="contract">Contract</label><br>
									<select id="contract" class="opt-box" name="contract">
										<option value="">All</option>
										<?php 
										$cmd = "SELECT DISTINCT Position FROM jobs order by Position";
										$result = mysqli_query($conn, $cmd);
										while ($row = mysqli_fetch_assoc($result)) {
											echo "<option value='" . $row['Position'] . "'>" . $row['Position'] . "</option>";
										}
										?>
									</select>
								</div>
								<div class="filter-group">
									<label for="input">Keyword</label><br>
									<input id="input" class="opt-box" placeholder="Search for your position" type="text" value="<?php echo isset($_GET['input']) ? $_GET['input'] : ''; ?>" name="input">
								</div>
								<div class="actions"><button type="submit">Filter</button></div>
							</div>
						</form>
					</div>
				</div>
			</section>
			<!-- Section 4: Jobs -->
			<section class="bg-white job-listings">
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
			
					if (isset($_GET['input']) && !empty($_GET['input']) && $_GET['input'] != "") {
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

					if (mysqli_num_rows($result) == 0) {
						echo "<h2>No jobs found</h2>";
					} else { 
						echo "<div class=\"card-align\">";
						while ($row = mysqli_fetch_assoc($result)) {
							echo "<div class=\"job-card\">";
							echo "<a href=\"job.php?job-ref=" . $row['JobReferenceNumber'] . "\">";
							echo "<h3 class=\"title\">" . $row["Title"] . "</h3>";
							//echo "<img alt=\"random\" src=\"image.png\">";
							echo "<div class=\"location\">";
							echo "<span class=\"tag\">" . $row["Location"]. "</span>";
							echo "<span class=\"jobref\">" . $row["JobReferenceNumber"] . "</span>";
							echo "</div>";
							
							echo "<div class=\"price-instructor\">";
							echo "<div class=\"price\">" . $row["Salary"] . "</div>";
							echo "</div>";
							echo "</a>";
							echo "</div>";
						}
						echo "</div>";
					}

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
	</body>
</html>