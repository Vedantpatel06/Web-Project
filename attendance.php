    <?php 
    include 'config.php'; 
    include 'header.php';
    ?>

    <style>
    .btn-brand {
        background: linear-gradient(45deg, #6a82fb, #fc5c7d);
        border: none;
        color: white;
        padding: 0.55rem 1.3rem;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.4s ease;
    }
    .btn-brand:hover {
        background: linear-gradient(45deg, #fc5c7d, #6a82fb);
    }
    </style>

    <?php
    $err = [];
    $suc = '';

    // Fetch classes for dropdowns
    $classes = $conn->query("SELECT id, class_name FROM classes ORDER BY class_name");

    // Variables for Mark Attendance form
    $selected_class = $_POST['class'] ?? null;
    $dateVal = $_POST['date'] ?? date('Y-m-d');

    $students = [];
    $attData = [];

    // Variables for Show Attendance form
    $show_class = $_POST['show_class'] ?? null;
    $show_date = $_POST['show_date'] ?? null;
    $show_results = [];

    // ----------- AJAX handler for update -------------
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ajax_update'])) {
        $student_id = intval($_POST['student_id']);
        $date = $_POST['date'] ?? '';
        $new_status = $_POST['new_status'] ?? '';

        header('Content-Type: application/json');
        if ($student_id && $date && in_array($new_status, ['Present', 'Absent'])) {
            $stmt = $conn->prepare("UPDATE attendance SET status=? WHERE student_id=? AND attendance_date=?");
            $stmt->bind_param("sis", $new_status, $student_id, $date);
            if ($stmt->execute()) {
                echo json_encode(['success' => true, 'message' => 'Attendance updated']);
            } else {
                echo json_encode(['success' => false, 'message' => 'DB update failed: ' . $stmt->error]);
            }
            $stmt->close();
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid data']);
        }
        exit;
    }
    // -----------------------------------------------

    // Handle update or delete attendance requests (non-AJAX)
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['ajax_update'])) {
        if (isset($_POST['update_attendance'])) {
            $student_id = intval($_POST['student_id']);
            $date = $_POST['date'];
            $new_status = $_POST['new_status'] ?? null;

            if ($new_status && in_array($new_status, ['Present', 'Absent'])) {
                $stmt = $conn->prepare("UPDATE attendance SET status=? WHERE student_id=? AND attendance_date=?");
                $stmt->bind_param("sis", $new_status, $student_id, $date);
                if ($stmt->execute()) {
                    $suc = "Attendance updated.";
                } else {
                    $err[] = "Failed to update attendance: " . $stmt->error;
                }
                $stmt->close();
            } else {
                $err[] = "Invalid status provided.";
            }

        } elseif (isset($_POST['delete_attendance'])) {
            $student_id = intval($_POST['student_id']);
            $date = $_POST['date'];

            $stmt = $conn->prepare("DELETE FROM attendance WHERE student_id=? AND attendance_date=?");
            $stmt->bind_param("is", $student_id, $date);
            if ($stmt->execute()) {
                $suc = "Attendance deleted.";
            } else {
                $err[] = "Failed to delete attendance: " . $stmt->error;
            }
            $stmt->close();
        }
    }

    // Handle Mark Attendance save
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['save_attendance'])) {
        $date = $_POST['date'];
        $today = date('Y-m-d');

        if (!$date) {
            $err[] = 'Date is required.';
        } elseif ($date > $today) {
            $err[] = 'Cannot mark attendance for future date.';
        } elseif (!$selected_class) {
            $err[] = 'Please select a class.';
        } else {
            foreach ($_POST['students'] as $student_id) {
                $status = isset($_POST['absent'][$student_id]) ? 'Absent' : 'Present';

                $stmt = $conn->prepare("
                    INSERT INTO attendance (student_id, attendance_date, status)
                    VALUES (?, ?, ?)
                    ON DUPLICATE KEY UPDATE status = VALUES(status)
                ");
                $stmt->bind_param("iss", $student_id, $date, $status);
                $stmt->execute();
                $stmt->close();
            }
            $suc = "Attendance saved for $date.";
        }
    }

    // Load students for Mark Attendance (after clicking OK)
    if (isset($_POST['select_class']) && $selected_class) {
        $stmt = $conn->prepare("SELECT id, name, roll_number, parents_name FROM students WHERE class_id = ? ORDER BY roll_number");
        $stmt->bind_param("i", $selected_class);
        $stmt->execute();
        $result = $stmt->get_result();
        $students = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Fetch existing attendance for selected date
        if ($students) {
            $student_ids = array_column($students, 'id');
            $placeholders = implode(',', array_fill(0, count($student_ids), '?'));
            $types = str_repeat('i', count($student_ids));
            
            $sql = "SELECT student_id, status FROM attendance WHERE attendance_date = ? AND student_id IN ($placeholders)";
            $stmt = $conn->prepare($sql);

            $bind_names = 's' . $types;
            $bind_params = array_merge([$dateVal], $student_ids);

            $bind_args = [];
            $bind_args[] = &$bind_names;
            foreach ($bind_params as &$param) {
                $bind_args[] = &$param;
            }
            call_user_func_array([$stmt, 'bind_param'], $bind_args);

            $stmt->execute();
            $res = $stmt->get_result();
            while ($row = $res->fetch_assoc()) {
                $attData[$row['student_id']] = $row['status'];
            }
            $stmt->close();
        }
    }

    // Show Attendance feature
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['show_attendance'])) {
        if (!$show_class || !$show_date) {
            $err[] = "Please select both date and class to show attendance.";
        } else {
            // Fetch students for show_class
            $stmt = $conn->prepare("SELECT id, name, roll_number, parents_name FROM students WHERE class_id = ? ORDER BY roll_number");
            $stmt->bind_param("i", $show_class);
            $stmt->execute();
            $res = $stmt->get_result();
            $students_for_show = $res->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            if ($students_for_show) {
                $student_ids = array_column($students_for_show, 'id');
                $placeholders = implode(',', array_fill(0, count($student_ids), '?'));
                $types = str_repeat('i', count($student_ids));
                
                $sql = "SELECT student_id, status FROM attendance WHERE attendance_date = ? AND student_id IN ($placeholders)";
                $stmt = $conn->prepare($sql);

                $bind_names = 's' . $types;
                $bind_params = array_merge([$show_date], $student_ids);

                $bind_args = [];
                $bind_args[] = &$bind_names;
                foreach ($bind_params as &$param) {
                    $bind_args[] = &$param;
                }
                call_user_func_array([$stmt, 'bind_param'], $bind_args);

                $stmt->execute();
                $res2 = $stmt->get_result();
                $attDataShow = [];
                while ($row = $res2->fetch_assoc()) {
                    $attDataShow[$row['student_id']] = $row['status'];
                }
                $stmt->close();

                // Prepare final show_results array with status & student id
                foreach ($students_for_show as $student) {
                    $status = $attDataShow[$student['id']] ?? 'Not Marked';
                    $show_results[] = [
                        'id' => $student['id'],
                        'roll_number' => $student['roll_number'],
                        'name' => $student['name'],
                        'parents_name' => $student['parents_name'],
                        'status' => $status
                    ];
                }
            } else {
                $err[] = "No students found in the selected class.";
            }
        }
    }

    ?>

    <?php if ($err): ?>
        <div class="alert alert-danger"><?= implode('<br>', $err) ?></div>
    <?php endif; ?>
    <?php if ($suc): ?>
        <div class="alert alert-success"><?= $suc ?></div>
    <?php endif; ?>

    <h4 class="mb-3">Mark Attendance</h4>

    <form method="post" class="row gy-3 align-items-end mb-4">
        <div class="col-md-3">
            <label>Date:</label>
            <input name="date" type="date" class="form-control" max="<?= date('Y-m-d') ?>" value="<?= htmlspecialchars($dateVal) ?>" required>
        </div>
        <div class="col-md-4">
            <label>Class:</label>
            <select name="class" class="form-select" required>
                <option value="">Select Class</option>
                <?php 
                $classes->data_seek(0);
                while ($c = $classes->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>" <?= $selected_class == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['class_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button name="select_class" type="submit" class="btn btn-brand">OK</button>
        </div>
    </form>

    <?php if (isset($_POST['select_class']) && $selected_class): ?>
        <?php if ($students): ?>
            <form method="post">
                <input type="hidden" name="class" value="<?= htmlspecialchars($selected_class) ?>">
                <input type="hidden" name="date" value="<?= htmlspecialchars($dateVal) ?>">

                <div class="table-premium">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Roll Number</th>
                                <th>Name</th>
                                <th>Absent</th>
                                <th>Parent's Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student):
                                $isAbsent = isset($attData[$student['id']]) && $attData[$student['id']] === 'Absent';
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($student['roll_number']) ?></td>
                                    <td><?= htmlspecialchars($student['name']) ?></td>
                                    <td class="text-center">
                                        <input type="checkbox" name="absent[<?= $student['id'] ?>]" value="Absent" <?= $isAbsent ? 'checked' : '' ?>>
                                        <input type="hidden" name="students[]" value="<?= $student['id'] ?>">
                                    </td>
                                    <td><?= htmlspecialchars($student['parents_name']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    <button name="save_attendance" type="submit" class="btn btn-brand">Save Attendance</button>
                </div>
            </form>
        <?php else: ?>
            <p>No students found for selected class.</p>
        <?php endif; ?>
    <?php endif; ?>

    <hr>

    <h4 class="mb-3">Show Attendance</h4>

    <form method="post" class="row gy-3 align-items-end mb-4">
        <div class="col-md-3">
            <label>Date:</label>
            <input name="show_date" type="date" class="form-control" value="<?= htmlspecialchars($show_date ?? '') ?>" required>
        </div>
        <div class="col-md-4">
            <label>Class:</label>
            <select name="show_class" class="form-select" required>
                <option value="">Select Class</option>
                <?php 
                $classes->data_seek(0);
                while ($c = $classes->fetch_assoc()): ?>
                    <option value="<?= $c['id'] ?>" <?= ($show_class ?? '') == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['class_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-2">
            <button name="show_attendance" type="submit" class="btn btn-brand">Show</button>
        </div>
    </form>

    <?php if ($show_results): ?>
        <div class="table-premium">
            <table class="table mb-0" id="attendanceTable">
                <thead>
                    <tr>
                        <th>Roll Number</th>
                        <th>Name</th>
                        <th>Parent's Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($show_results as $row): ?>
                    <tr data-studentid="<?= $row['id'] ?>">
                        <td><?= htmlspecialchars($row['roll_number']) ?></td>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['parents_name']) ?></td>
                        <td class="statusCell" data-original="<?= htmlspecialchars($row['status']) ?>"><?= htmlspecialchars($row['status']) ?></td>
                        <td class="actionCell">
                            <?php if ($row['status'] != 'Not Marked'): ?>
                                <button class="btn btn-brand btn-sm editBtn">Update</button>
                            <?php else: ?>
                                <span class="text-muted">No record</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <script>
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('editBtn')) {
            let tr = e.target.closest('tr');
            let statusCell = tr.querySelector('.statusCell');
            let actionCell = tr.querySelector('.actionCell');
            let currentStatus = statusCell.textContent.trim();

            // Replace statusCell with dropdown
            statusCell.innerHTML = `
                <select class="form-select form-select-sm statusSelect">
                    <option value="Present" ${currentStatus === 'Present' ? 'selected' : ''}>Present</option>
                    <option value="Absent" ${currentStatus === 'Absent' ? 'selected' : ''}>Absent</option>
                </select>
            `;
            // Replace action buttons
            actionCell.innerHTML = `
                <button class="btn btn-brand btn-sm updateBtn">OK</button>
                <button class="btn btn-brand btn-sm cancelBtn">Cancel</button>
            `;
        }

        if (e.target.classList.contains('cancelBtn')) {
            let tr = e.target.closest('tr');
            let statusCell = tr.querySelector('.statusCell');
            let actionCell = tr.querySelector('.actionCell');
            let originalStatus = statusCell.getAttribute('data-original');

            statusCell.textContent = originalStatus;
            actionCell.innerHTML = `<button class="btn btn-brand btn-sm editBtn">Update</button>`;
        }

        if (e.target.classList.contains('updateBtn')) {
            let tr = e.target.closest('tr');
            let studentId = tr.getAttribute('data-studentid');
            let statusSelect = tr.querySelector('.statusSelect');
            let newStatus = statusSelect.value;
            let dateInput = document.querySelector('input[name="show_date"]');
            let date = dateInput.value;

            fetch('', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    ajax_update: 1,
                    student_id: studentId,
                    date: date,
                    new_status: newStatus
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    let statusCell = tr.querySelector('.statusCell');
                    let actionCell = tr.querySelector('.actionCell');
                    statusCell.textContent = newStatus;
                    statusCell.setAttribute('data-original', newStatus);
                    actionCell.innerHTML = `<button class="btn btn-brand btn-sm editBtn">Update</button>`;
                } else {
                    alert(data.message);
                }
            })
            .catch(err => alert('Error updating attendance.'));
        }
    });
    </script>

    <?php include 'footer.php'; ?>
