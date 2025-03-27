		<?php 
		$title = 'HR Manager Dashboard';
		$style = 'manage.css';
		include_once 'initial_page_settings.inc';
		?>
		<?php
			include_once 'header.inc';
			include_once 'settings.php';
			include_once 'functions.inc';
			
			// Check if user is logged in
			if (!isset($_SESSION['manager_id'])) {
			    header("Location: login.php");
			    exit();
			}
			
			// Get filter parameters
			$filter_job = isset($_GET['job_ref']) ? sanitize_input($_GET['job_ref']) : '';
			$filter_name = isset($_GET['applicant_name']) ? sanitize_input($_GET['applicant_name']) : '';
			$filter_status = isset($_GET['status']) ? sanitize_input($_GET['status']) : '';
			$sort_by = isset($_GET['sort']) ? sanitize_input($_GET['sort']) : 'EOInumber';
			$sort_order = isset($_GET['order']) ? sanitize_input($_GET['order']) : 'DESC';
			$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
			$records_per_page = 10;
			
			// Check if viewing details for a specific EOI
			$view_eoi_id = isset($_GET['view_eoi']) ? (int)$_GET['view_eoi'] : 0;
			$eoi_details = null;
			
			if ($view_eoi_id > 0) {
			    $details_sql = "SELECT * FROM eoi WHERE EOInumber = ?";
			    $details_stmt = mysqli_prepare($conn, $details_sql);
			    mysqli_stmt_bind_param($details_stmt, "i", $view_eoi_id);
			    mysqli_stmt_execute($details_stmt);
			    $details_result = mysqli_stmt_get_result($details_stmt);
			    $eoi_details = mysqli_fetch_assoc($details_result);
			}
			
			// Handle actions
			$action_message = '';
			if ($_SERVER["REQUEST_METHOD"] == "POST") {
			    if (isset($_POST['action'])) {
			        // Delete EOIs for a job reference
			        if ($_POST['action'] == 'delete' && isset($_POST['delete_job_ref'])) {
			            $delete_job_ref = sanitize_input($_POST['delete_job_ref']);
			
			            $sql = "DELETE FROM eoi WHERE JobReferenceNumber = ?";
			            $stmt = mysqli_prepare($conn, $sql);
			            mysqli_stmt_bind_param($stmt, "s", $delete_job_ref);
			
			            if (mysqli_stmt_execute($stmt)) {
			                $affected_rows = mysqli_stmt_affected_rows($stmt);
			                $action_message = "$affected_rows application(s) for job reference $delete_job_ref deleted successfully";
			            } else {
			                $action_message = "Error deleting applications: " . mysqli_error($conn);
			            }
			        }
			
			        // Update EOI status
			        if ($_POST['action'] == 'update_status' && isset($_POST['eoi_id']) && isset($_POST['new_status'])) {
			            $eoi_id = (int)$_POST['eoi_id'];
			            $new_status = sanitize_input($_POST['new_status']);
			
			            if (in_array($new_status, ['New', 'Current', 'Final'])) {
			                $sql = "UPDATE eoi SET Status = ? WHERE EOInumber = ?";
			                $stmt = mysqli_prepare($conn, $sql);
			                mysqli_stmt_bind_param($stmt, "si", $new_status, $eoi_id);
			
			                if (mysqli_stmt_execute($stmt)) {
			                    $action_message = "Application status updated successfully";
			                } else {
			                    $action_message = "Error updating status: " . mysqli_error($conn);
			                }
			            } else {
			                $action_message = "Invalid status value";
			            }
			        }
			
			        // Bulk update status
			        if ($_POST['action'] == 'bulk_update' && isset($_POST['selected_eois']) && isset($_POST['bulk_status'])) {
			            $selected_eois = $_POST['selected_eois'];
			            $bulk_status = sanitize_input($_POST['bulk_status']);
			
			            if (in_array($bulk_status, ['New', 'Current', 'Final'])) {
			                $success_count = 0;
			
			                foreach ($selected_eois as $eoi_id) {
			                    $eoi_id = (int)$eoi_id;
			                    $sql = "UPDATE eoi SET Status = ? WHERE EOInumber = ?";
			                    $stmt = mysqli_prepare($conn, $sql);
			                    mysqli_stmt_bind_param($stmt, "si", $bulk_status, $eoi_id);
			
			                    if (mysqli_stmt_execute($stmt)) {
			                        $success_count++;
			                    }
			                }
			
			                $action_message = "$success_count application(s) updated to status '$bulk_status'";
			            } else {
			                $action_message = "Invalid status value";
			            }
			        }
			    }
			}
			
			// Build the base query
			$base_query = "FROM eoi WHERE 1=1";
			$params = [];
			$types = "";
			
			if (!empty($filter_job)) {
			    $base_query .= " AND JobReferenceNumber = ?";
			    $params[] = $filter_job;
			    $types .= "s";
			}
			
			if (!empty($filter_name)) {
			    $base_query .= " AND (FirstName LIKE ? OR LastName LIKE ?)";
			    $name_param = "%" . $filter_name . "%";
			    $params[] = $name_param;
			    $params[] = $name_param;
			    $types .= "ss";
			}
			
			if (!empty($filter_status)) {
			    $base_query .= " AND Status = ?";
			    $params[] = $filter_status;
			    $types .= "s";
			}
			
			// Count total records for pagination
			$count_sql = "SELECT COUNT(*) as total " . $base_query;
			$count_stmt = mysqli_prepare($conn, $count_sql);
			
			if (!empty($params)) {
			    mysqli_stmt_bind_param($count_stmt, $types, ...$params);
			}
			
			mysqli_stmt_execute($count_stmt);
			$count_result = mysqli_stmt_get_result($count_stmt);
			$count_row = mysqli_fetch_assoc($count_result);
			$total_records = $count_row['total'];
			$total_pages = ceil($total_records / $records_per_page);
			
			// Ensure page is within valid range
			if ($page < 1) $page = 1;
			if ($page > $total_pages && $total_pages > 0) $page = $total_pages;
			
			// Calculate offset for pagination
			$offset = ($page - 1) * $records_per_page;
			
			// Build the main query with sorting and pagination
			$valid_sort_columns = ['EOInumber', 'JobReferenceNumber', 'FirstName', 'LastName', 'DOB', 'Status'];
			$valid_sort_orders = ['ASC', 'DESC'];
			
			if (!in_array($sort_by, $valid_sort_columns)) $sort_by = 'EOInumber';
			if (!in_array($sort_order, $valid_sort_orders)) $sort_order = 'DESC';
			
			$main_sql = "SELECT * " . $base_query . " ORDER BY $sort_by $sort_order LIMIT ?, ?";
			$main_stmt = mysqli_prepare($conn, $main_sql);
			
			// Add pagination parameters
			$params[] = $offset;
			$params[] = $records_per_page;
			$types .= "ii";
			
			if (!empty($params)) {
			    mysqli_stmt_bind_param($main_stmt, $types, ...$params);
			}
			
			mysqli_stmt_execute($main_stmt);
			$result = mysqli_stmt_get_result($main_stmt);
			
			// Get unique job references for filter dropdown
			$job_refs_sql = "SELECT DISTINCT JobReferenceNumber FROM eoi ORDER BY JobReferenceNumber";
			$job_refs_result = mysqli_query($conn, $job_refs_sql);
			?>
		<main>
			<div class="container">
				<section class="dashboard-header">
					<h2>HR Manager Dashboard</h2>
					<p>Welcome, <?php echo htmlspecialchars($_SESSION['manager_name']); ?>. Manage job applications from this dashboard.</p>
					<?php if (!empty($action_message)): ?>
					<div class="action-message"> 
						<?php echo htmlspecialchars($action_message); ?>
					</div>
					<?php endif; ?>
				</section>
				<?php if ($eoi_details): ?>
				<!-- Application Details View -->
				<section class="application-details">
					<h3>Application Details</h3>
					<div class="detail-card">
						<h4><?php echo htmlspecialchars($eoi_details['FirstName'] . ' ' . $eoi_details['LastName']); ?></h4>
						<div class="detail-section">
							<div class="detail-row">
								<div class="detail-label">EOI Number:</div>
								<div class="detail-value"><?php echo $eoi_details['EOInumber']; ?></div>
							</div>
							<div class="detail-row">
								<div class="detail-label">Job Reference:</div>
								<div class="detail-value"><?php echo htmlspecialchars($eoi_details['JobReferenceNumber']); ?></div>
							</div>
							<div class="detail-row">
								<div class="detail-label">Email:</div>
								<div class="detail-value"><?php echo htmlspecialchars($eoi_details['Email']); ?></div>
							</div>
							<div class="detail-row">
								<div class="detail-label">Status:</div>
								<div class="detail-value">
									<span class="status-badge status-<?php echo strtolower($eoi_details['Status']); ?>">
									<?php echo $eoi_details['Status']; ?>
									</span>
								</div>
							</div>
							<div class="detail-row">
								<div class="detail-label">DOB:</div>
								<div class="detail-value"><?php echo $eoi_details['DOB']; ?></div>
							</div>
							<div class="detail-row">
								<div class="detail-label">Gender:</div>
								<div class="detail-value"><?php echo $eoi_details['Gender']; ?></div>
							</div>
							<div class="detail-row">
								<div class="detail-label">State:</div>
								<div class="detail-value"><a href="https://auspost.com.au/postcode/<?php echo $eoi_details['State']; ?>"><?php echo $eoi_details['State']; ?></a></div>
							</div>
							<div class="detail-row">
								<div class="detail-label">Phone Number:</div>
								<div class="detail-value"><?php echo $eoi_details['PhoneNumber']; ?></div>
							</div>
							<div class="detail-row">
								<div class="detail-label">Main skills:</div>
								<div class="detail-value">
									<?php
									$skills = [];
									if (isset($eoi_details['Skill1']) && !empty($eoi_details['Skill1'])) {
									    $skills[] = $eoi_details['Skill1'];
									}
									if (isset($eoi_details['Skill2']) && !empty($eoi_details['Skill2'])) {
									    $skills[] = $eoi_details['Skill2'];
									}
									if (isset($eoi_details['Skill3']) && !empty($eoi_details['Skill3'])) {
										$skills[] = $eoi_details['Skill3'];
									}
									if (isset($eoi_details['Skill4']) && !empty($eoi_details['Skill4'])) {
										$skills[] = $eoi_details['Skill4'];
									}
									echo $skills ? implode(', ', $skills) : 'N/A';
									?>
								</div>
							</div> <!-- Skills -->
							<div class="detail-row">
								<div class="detail-label">Other skills:</div>
								<div class="detail-value"><?php echo (isset($eoi_details['OtherSkills']) && !empty($eoi_details['OtherSkills'])) ? $eoi_details['OtherSkills'] : 'N/A'; ?></div>
							</div> <!-- Other Skills -->

						</div>
						<div class="detail-actions">
							<form method="post" action="manage.php" class="inline-form">
								<input type="hidden" name="action" value="update_status">
								<input type="hidden" name="eoi_id" value="<?php echo $eoi_details['EOInumber']; ?>">
								<input type="hidden" name="new_status" value="New">
								<button type="submit" class="btn btn-login">Set as New</button>
							</form>
							<form method="post" action="manage.php" class="inline-form">
								<input type="hidden" name="action" value="update_status">
								<input type="hidden" name="eoi_id" value="<?php echo $eoi_details['EOInumber']; ?>">
								<input type="hidden" name="new_status" value="Current">
								<button type="submit" class="btn btn-login">Set as Current</button>
							</form>
							<form method="post" action="manage.php" class="inline-form">
								<input type="hidden" name="action" value="update_status">
								<input type="hidden" name="eoi_id" value="<?php echo $eoi_details['EOInumber']; ?>">
								<input type="hidden" name="new_status" value="Final">
								<button type="submit" class="btn btn-login">Set as Final</button>
							</form>
							<a href="manage.php?job_ref=<?php echo urlencode($filter_job); ?>&applicant_name=<?php echo urlencode($filter_name); ?>&status=<?php echo urlencode($filter_status); ?>&sort=<?php echo urlencode($sort_by); ?>&order=<?php echo urlencode($sort_order); ?>&page=<?php echo $page; ?>" class="btn btn-register">Back to List</a>
						</div>
					</div>
				</section>
				<?php else: ?>
				<!-- Dashboard Controls and Applications List -->
				<section class="dashboard-controls">
					<div class="filter-section">
						<h3>Filter Applications</h3>
						<form method="get" action="manage.php" class="filter-form">
							<div class="form-row">
								<div class="form-group">
									<label for="job_ref">Job Reference:</label>
									<select name="job_ref" id="job_ref">
										<option value="">All Jobs</option>
										<?php while ($job_ref = mysqli_fetch_assoc($job_refs_result)): ?>
										<option value="<?php echo htmlspecialchars($job_ref['JobReferenceNumber']); ?>"
											<?php echo ($filter_job == $job_ref['JobReferenceNumber']) ? 'selected' : ''; ?>>
											<?php echo htmlspecialchars($job_ref['JobReferenceNumber']); ?>
										</option>
										<?php endwhile; ?>
									</select>
								</div>
								<div class="form-group">
									<label for="applicant_name">Applicant Name:</label>
									<input type="text" name="applicant_name" id="applicant_name"
										value="<?php echo htmlspecialchars($filter_name); ?>">
								</div>
								<div class="form-group">
									<label for="status">Status:</label>
									<select name="status" id="status">
										<option value="">All Statuses</option>
										<option value="New" <?php echo ($filter_status == 'New') ? 'selected' : ''; ?>>New</option>
										<option value="Current" <?php echo ($filter_status == 'Current') ? 'selected' : ''; ?>>Current</option>
										<option value="Final" <?php echo ($filter_status == 'Final') ? 'selected' : ''; ?>>Final</option>
									</select>
								</div>
							</div>
							<input type="hidden" name="sort" value="<?php echo htmlspecialchars($sort_by); ?>">
							<input type="hidden" name="order" value="<?php echo htmlspecialchars($sort_order); ?>">
							<div class="form-actions">
								<button type="submit" class="btn btn-login">Apply Filters</button>
								<a href="manage.php" class="btn btn-register">Reset Filters</a>
							</div>
						</form>
					</div>
					<div class="action-section">
						<h3>Delete Applications</h3>
						<form method="post" action="manage.php">
							<input type="hidden" name="action" value="delete">
							<div class="form-row">
								<div class="form-group">
									<label for="delete_job_ref">Job Reference:</label>
									<select name="delete_job_ref" id="delete_job_ref" required>
										<option value="">Select Job Reference</option>
										<?php
											mysqli_data_seek($job_refs_result, 0); // Reset pointer to beginning
											while ($job_ref = mysqli_fetch_assoc($job_refs_result)):
										?>
										<option value="<?php echo htmlspecialchars($job_ref['JobReferenceNumber']); ?>">
											<?php echo htmlspecialchars($job_ref['JobReferenceNumber']); ?>
										</option>
										<?php endwhile; ?>
									</select>
								</div>
							</div>
							<div class="form-actions">
								<!-- JavaScript consideration -->
								<button type="submit" class="btn btn-logout" id="btn-delete" onclick="return confirm('Are you sure you want to delete all applications for this job reference? This action cannot be undone.');">Delete All</button>
							</div>
						</form>
					</div>
				</section>
				<section class="applications-table">
					<h3>Job Applications</h3>
					<?php if (mysqli_num_rows($result) > 0): ?>
					<form method="post" action="manage.php">
						<input type="hidden" name="action" value="bulk_update">
						<div class="bulk-actions">
							<select name="bulk_status">
								<option value="New">New</option>
								<option value="Current">Current</option>
								<option value="Final">Final</option>
							</select>
							<button type="submit" class="btn btn-login">Update Selected</button>
						</div>
						<div class="table-wrapper">
							<table class="data-table">
								<thead>
									<tr>
										<th><input type="checkbox" onclick="toggleAll(this)"></th>
										<th>
											<a href="manage.php?job_ref=<?php echo urlencode($filter_job); ?>&applicant_name=<?php echo urlencode($filter_name); ?>&status=<?php echo urlencode($filter_status); ?>&sort=EOInumber&order=<?php echo ($sort_by == 'EOInumber' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>">
											EOI #
											<?php if ($sort_by == 'EOInumber'): ?>
											<span class="sort-indicator"><?php echo ($sort_order == 'ASC') ? '▲' : '▼'; ?></span>
											<?php endif; ?>
											</a>
										</th>
										<th>
											<a href="manage.php?job_ref=<?php echo urlencode($filter_job); ?>&applicant_name=<?php echo urlencode($filter_name); ?>&status=<?php echo urlencode($filter_status); ?>&sort=JobReferenceNumber&order=<?php echo ($sort_by == 'JobReferenceNumber' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>">
											Job Ref
											<?php if ($sort_by == 'JobReferenceNumber'): ?>
											<span class="sort-indicator"><?php echo ($sort_order == 'ASC') ? '▲' : '▼'; ?></span>
											<?php endif; ?>
											</a>
										</th>
										<th>
											<a href="manage.php?job_ref=<?php echo urlencode($filter_job); ?>&applicant_name=<?php echo urlencode($filter_name); ?>&status=<?php echo urlencode($filter_status); ?>&sort=FirstName&order=<?php echo ($sort_by == 'FirstName' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>">
											First Name
											<?php if ($sort_by == 'FirstName'): ?>
											<span class="sort-indicator"><?php echo ($sort_order == 'ASC') ? '▲' : '▼'; ?></span>
											<?php endif; ?>
											</a>
										</th>
										<th>
											<a href="manage.php?job_ref=<?php echo urlencode($filter_job); ?>&applicant_name=<?php echo urlencode($filter_name); ?>&status=<?php echo urlencode($filter_status); ?>&sort=LastName&order=<?php echo ($sort_by == 'LastName' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>">
											Last Name
											<?php if ($sort_by == 'LastName'): ?>
											<span class="sort-indicator"><?php echo ($sort_order == 'ASC') ? '▲' : '▼'; ?></span>
											<?php endif; ?>
											</a>
										</th>
										<th>Email</th>
										<th>
											<a href="manage.php?job_ref=<?php echo urlencode($filter_job); ?>&applicant_name=<?php echo urlencode($filter_name); ?>&status=<?php echo urlencode($filter_status); ?>&sort=Status&order=<?php echo ($sort_by == 'Status' && $sort_order == 'ASC') ? 'DESC' : 'ASC'; ?>">
											Status
											<?php if ($sort_by == 'Status'): ?>
											<span class="sort-indicator"><?php echo ($sort_order == 'ASC') ? '▲' : '▼'; ?></span>
											<?php endif; ?>
											</a>
										</th>
										<th>Actions</th>
									</tr>
								</thead>
								<tbody>
									<?php while ($row = mysqli_fetch_assoc($result)): ?>
									<tr class="status-<?php echo strtolower($row['Status']); ?>">
										<td>
											<input type="checkbox" name="selected_eois[]" value="<?php echo $row['EOInumber']; ?>">
										</td>
										<td><?php echo $row['EOInumber']; ?></td>
										<td><?php echo htmlspecialchars($row['JobReferenceNumber']); ?></td>
										<td><?php echo htmlspecialchars($row['FirstName']); ?></td>
										<td><?php echo htmlspecialchars($row['LastName']); ?></td>
										<td><?php echo htmlspecialchars($row['Email']); ?></td>
										<td>
											<span class="status-badge status-<?php echo strtolower($row['Status']); ?>">
											<?php echo $row['Status']; ?>
											</span>
										</td>
										<td>
											<div class="action-buttons">
												<a href="manage.php?view_eoi=<?php echo $row['EOInumber']; ?>&job_ref=<?php echo urlencode($filter_job); ?>&applicant_name=<?php echo urlencode($filter_name); ?>&status=<?php echo urlencode($filter_status); ?>&sort=<?php echo urlencode($sort_by); ?>&order=<?php echo urlencode($sort_order); ?>&page=<?php echo $page; ?>" class="btn btn-small">View</a>
												<!-- EOI Status Modifying --> 
											</div>
					    	            </td>
					    	        </tr>
					    	        <?php endwhile; ?>
					    	    </tbody>
					    	</table>
						</div>
					</form>
					<!-- Pagination -->
					<div class="pagination-container">
						<?php
							$pagination_url = "manage.php?job_ref=" . urlencode($filter_job) . "&applicant_name=" . urlencode($filter_name) . "&status=" . urlencode($filter_status) . "&sort=" . urlencode($sort_by) . "&order=" . urlencode($sort_order);
							echo generate_pagination($total_records, $records_per_page, $page, $pagination_url);
							?>
						<div class="pagination-info">
							Showing <?php echo min(($page - 1) * $records_per_page + 1, $total_records); ?> to
							<?php echo min($page * $records_per_page, $total_records); ?> of
							<?php echo $total_records; ?> applications
						</div>
					</div>
					<?php else: ?>
					<div class="no-data-message">
						<p>No applications found matching your criteria.</p>
					</div>
					<?php endif; ?>
				</section>
				<?php endif; ?>
			</div>
		</main>
		<?php
			mysqli_close($conn);
			include 'footer.inc';
			?>