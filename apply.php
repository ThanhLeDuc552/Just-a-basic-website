<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="description" content="Apply for a job at Etech">
		<title>Etech - Apply</title>
		<link rel="stylesheet" href="https://use.typekit.net/ilv8ihq.css">
		<link rel="stylesheet" href="styles/style.css">
		<link rel="stylesheet" href="styles/apply.css">
	</head>
	<body>
		<?php 
		include_once 'header.inc'; 
		if (isset($_SESSION["manager_id"])) {
			header("Location: manage.php");
			exit();
		}
		$errors = isset($_SESSION['form_errors']) ? $_SESSION['form_errors'] : [];
		?>
		<main>
			<section class="form-container">
				<h2>Submit Your Application</h2>
				<form action="process_eoi.php" method="post" novalidate>
					<label for="jobref">Job Reference Number: <p class="err_msg"><?php echo isset($errors['job_ref']) ? $errors["job_ref"] : ''; ?></p></label>
					<select name="job_ref" id="jobref" required>
						<option value="" disabled <?php echo !isset($_GET["job_ref"]) ? "selected" : ''; ?>>Select job</option>
						<?php 
						include "settings.php";
						$cmd = "SELECT DISTINCT JobReferenceNumber FROM jobs";
						$result = mysqli_query($conn, $cmd);
						while ($row = mysqli_fetch_assoc($result)): ?>
						<option value="<?php echo $row['JobReferenceNumber']; ?>" <?php echo (isset($_GET["job_ref"]) && $_GET["job_ref"] === $row['JobReferenceNumber'] ? 'selected' : ''); ?>><?php echo $row['JobReferenceNumber']; ?></option>
						<?php endwhile; ?>
					</select>
					<label for="fname">First Name: <p class="err_msg"><?php echo isset($errors['first_name']) ? $errors["first_name"] : ''; ?></p></label>
					<input type="text" id="fname" name="first_name" pattern="[A-Za-z]{1,20}" required maxlength="20" placeholder="John">
					<label for="lname">Last Name: <p class="err_msg"><?php echo isset($errors['last_name']) ? $errors["last_name"] : ''; ?></p></label>
					<input type="text" id="lname" name="last_name" pattern="[A-Za-z]{1,20}" required maxlength="20" placeholder="Doe">
					<label for="dob">Date of Birth: <p class="err_msg"><?php echo isset($errors['dob']) ? $errors["dob"] : ''; ?></p></label>
					<input type="date" id="dob" name="dob" required>
					<label for="gender">Gender: <p class="err_msg"><?php echo isset($errors['gender']) ? $errors["gender"] : ''; ?></p></label>
					<select id="gender" name="gender" required>
						<option value="" disabled selected>Select Gender</option>
						<option value="male">Male</option>
						<option value="female">Female</option>
						<option value="other">Other</option>
					</select>
					<label for="street">Street Address: <p class="err_msg"><?php echo isset($errors['street_address']) ? $errors["street_address"] : ''; ?></p></label>
					<input type="text" id="street" name="street_address" maxlength="40" required placeholder="123 Tech Lane">
					<label for="suburb">Suburb/Town: <p class="err_msg"><?php echo isset($errors['suburb']) ? $errors["suburb"] : ''; ?></p></label>
					<input type="text" id="suburb" name="suburb" maxlength="40" required placeholder="Melbourne">
					<label for="state">State: <p class="err_msg"><?php echo isset($errors['state']) ? $errors["state"] : ''; ?></p></label>
					<select id="state" name="state" required>
						<option value="" disabled selected>Select State</option>
						<option value="VIC">VIC</option>
						<option value="NSW">NSW</option>
						<option value="QLD">QLD</option>
						<option value="NT">NT</option>
						<option value="WA">WA</option>
						<option value="SA">SA</option>
						<option value="TAS">TAS</option>
						<option value="ACT">ACT</option>
					</select>
					<label for="postcode">Postcode: <p class="err_msg"><?php echo isset($errors['postcode']) ? $errors["postcode"] : ''; ?></p></label>
					<input type="text" id="postcode" name="postcode" pattern="\d{4}" required maxlength="4" placeholder="3000">
					<label for="email">Email: <p class="err_msg"><?php echo isset($errors['email']) ? $errors["email"] : ''; ?></p></label>
					<input type="email" id="email" name="email" required placeholder="you@example.com">
					<label for="phone">Phone: <p class="err_msg"><?php echo isset($errors['phone']) ? $errors["phone"] : ''; ?></p></label>
					<input type="text" id="phone" name="phone" pattern="[\d\s]{8,12}" required placeholder="0412 345 678">
					<div class="skills-container">
						<label class="skills-label">Skills (Select all that apply):</label>
						<div class="checkbox-group">
							<div class="checkbox-item">
								<input type="checkbox" id="skill1" name="skill1" value="HTML">
								<label for="skill1">HTML</label>
							</div>
							<div class="checkbox-item">
								<input type="checkbox" id="skill2" name="skill2" value="PHP">
								<label for="skill2">PHP</label>
							</div>
							<div class="checkbox-item">
								<input type="checkbox" id="skill3" name="skill3" value="SQL">
								<label for="skill3">SQL</label>
							</div>
							<div class="checkbox-item">
								<input type="checkbox" id="skill4" name="skill4" value="CSS">
								<label for="skill4">CSS</label>
							</div>
						</div>
					</div>
					<label for="extra_skills">Other Skills:</label>
					<textarea id="extra_skills" name="extra_skills" rows="4" placeholder="List additional skills here..."></textarea>
					<button type="submit">Apply</button>
				</form>
			</section>
		</main>
		<?php 
		if (isset($_SESSION["form_errors"])) unset($_SESSION["form_errors"]);
		if (isset($errors)) unset($errors);
		include_once 'footer.inc'; 
		?>
	</body>
</html>