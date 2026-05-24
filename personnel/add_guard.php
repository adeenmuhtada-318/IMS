<?php
/**
 * PERSONNEL ENROLLMENT PAGE - TACTICAL IMS
 * Division: Guard Recruitment & Compliance
 */

// 1. SESSION & SECURITY LOCK
session_start();
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

// 2. DATABASE BRIDGE
require_once '../includes/connection.php';

// 3. POST HANDLER (AJAX/JSON)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $cnic = $_POST['cnic'] ?? '';

    try {
        // DUPLICATE CNIC CHECK
        $stmt_check = $pdo->prepare("SELECT cnic FROM guards_personnel WHERE cnic = ?");
        $stmt_check->execute([$cnic]);
        if ($stmt_check->fetch()) {
            echo json_encode(['status' => 'duplicate', 'message' => 'CRITICAL: Guard already registered under this CNIC.']);
            exit;
        }

        // PREPARE INSERTION
        $sql = "INSERT INTO guards_personnel (
            guard_no, full_name, parentage, cnic, dob, guard_phone, caste, education, religion, 
            home_district, permanent_address, temporary_address, heir_name, heir_phone, 
            heir_relation, heir_address, prev_experience_ref, gov_relative_details, 
            is_ex_army, army_enroll_date, army_discharge_date, 
            witness1_name, witness1_phone, witness1_cnic, witness1_address, 
            witness2_name, witness2_phone, witness2_cnic, witness2_address, 
            fingerprint_status, police_verification_status, police_verification_no, special_branch_status
        ) VALUES (
            :guard_no, :full_name, :parentage, :cnic, :dob, :guard_phone, :caste, :education, :religion, 
            :home_district, :permanent_address, :temporary_address, :heir_name, :heir_phone, 
            :heir_relation, :heir_address, :prev_experience_ref, :gov_relative_details, 
            :is_ex_army, :army_enroll_date, :army_discharge_date, 
            :witness1_name, :witness1_phone, :witness1_cnic, :witness1_address, 
            :witness2_name, :witness2_phone, :witness2_cnic, :witness2_address, 
            :fingerprint_status, :police_verification_status, :police_verification_no, :special_branch_status
        )";

        $stmt = $pdo->prepare($sql);
        
        // Sanitize and handle empty dates as NULL
        $data = $_POST;
        foreach ($data as $key => $value) {
            if (in_array($key, ['army_enroll_date', 'army_discharge_date', 'police_verification_no']) && empty($value)) {
                $data[$key] = null;
            }
        }

        // Ensure is_ex_army is boolean/int
        $data['is_ex_army'] = isset($data['is_ex_army']) ? (int)$data['is_ex_army'] : 0;
        if ($data['is_ex_army'] === 0) {
            $data['army_enroll_date'] = null;
            $data['army_discharge_date'] = null;
        }

        $stmt->execute([
            ':guard_no' => $data['guard_no'],
            ':full_name' => $data['full_name'],
            ':parentage' => $data['parentage'],
            ':cnic' => $data['cnic'],
            ':dob' => $data['dob'],
            ':guard_phone' => $data['guard_phone'],
            ':caste' => $data['caste'],
            ':education' => $data['education'],
            ':religion' => $data['religion'],
            ':home_district' => $data['home_district'],
            ':permanent_address' => $data['permanent_address'],
            ':temporary_address' => $data['temporary_address'],
            ':heir_name' => $data['heir_name'],
            ':heir_phone' => $data['heir_phone'],
            ':heir_relation' => $data['heir_relation'],
            ':heir_address' => $data['heir_address'],
            ':prev_experience_ref' => $data['prev_experience_ref'] ?? '',
            ':gov_relative_details' => $data['gov_relative_details'] ?? '',
            ':is_ex_army' => $data['is_ex_army'],
            ':army_enroll_date' => $data['army_enroll_date'],
            ':army_discharge_date' => $data['army_discharge_date'],
            ':witness1_name' => $data['witness1_name'],
            ':witness1_phone' => $data['witness1_phone'],
            ':witness1_cnic' => $data['witness1_cnic'],
            ':witness1_address' => $data['witness1_address'],
            ':witness2_name' => $data['witness2_name'],
            ':witness2_phone' => $data['witness2_phone'],
            ':witness2_cnic' => $data['witness2_cnic'],
            ':witness2_address' => $data['witness2_address'],
            ':fingerprint_status' => $data['fingerprint_status'],
            ':police_verification_status' => $data['police_verification_status'],
            ':police_verification_no' => $data['police_verification_no'],
            ':special_branch_status' => $data['special_branch_status']
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Guard enrolled successfully.', 'redirect' => 'manage_guards.php?status=added']);
        exit;

    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'SYSTEM_ERROR: ' . $e->getMessage()]);
        exit;
    }
}

// 4. UI RENDER
include '../includes/header.php';
?>

<link rel="stylesheet" href="../assets/css/glass_theme.css">

<style>
    .enrollment-container {
        max-width: 1200px;
        margin: 0 auto;
        padding-bottom: 50px;
    }
    .section-title {
        color: var(--accent-cyan);
        font-size: 1.1rem;
        letter-spacing: 2px;
        margin-bottom: 25px;
        border-left: 4px solid var(--accent-cyan);
        padding-left: 15px;
        text-transform: uppercase;
        font-weight: 800;
    }
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
    }
    .form-section {
        margin-bottom: 30px;
        padding: 30px;
    }
    .full-width {
        grid-column: 1 / -1;
    }
    .age-warning {
        color: var(--alert-orange);
        font-size: 0.8rem;
        margin-top: 5px;
        font-weight: 600;
    }
    .hidden {
        display: none !important;
    }
    .radio-group {
        display: flex;
        gap: 20px;
        align-items: center;
        padding: 10px 0;
    }
    .radio-option {
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
    }
    .radio-option input {
        accent-color: var(--accent-cyan);
    }
</style>

<div class="enrollment-container">
    <header style="margin-bottom: 30px;">
        <h1 style="font-weight: 800; letter-spacing: 1px;">Enroll New Guard</h1>
        <p style="color: var(--text-dim); font-size: 0.9rem;">Phase-V Strategic Recruitment Interface</p>
    </header>

    <form id="guard-enrollment-form">
        <!-- SECTION 1: PERSONAL INFO -->
        <div class="glass-panel form-section">
            <h2 class="section-title">1. Personal Information</h2>
            <div class="form-grid">
                <div class="input-group">
                    <label>Guard Number</label>
                    <input type="text" name="guard_no" class="glass-input" placeholder="e.g. FS-001" required>
                </div>
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="glass-input" required>
                </div>
                <div class="input-group">
                    <label>Father/Husband Name</label>
                    <input type="text" name="parentage" class="glass-input" required>
                </div>
                <div class="input-group">
                    <label>CNIC (35201-XXXXXXX-X)</label>
                    <input type="text" name="cnic" id="cnic" class="glass-input" placeholder="35201-1234567-1" required>
                    <span id="cnic-error" class="age-warning hidden">Invalid CNIC format.</span>
                </div>
                <div class="input-group">
                    <label>Date of Birth</label>
                    <input type="date" name="dob" id="dob" class="glass-input" required>
                    <span id="age-warning" class="age-warning hidden">Critical: Personnel must be at least 18 years old.</span>
                </div>
                <div class="input-group">
                    <label>Phone Number</label>
                    <input type="text" name="guard_phone" class="glass-input" required>
                </div>
                <div class="input-group">
                    <label>Caste</label>
                    <input type="text" name="caste" class="glass-input">
                </div>
                <div class="input-group">
                    <label>Education</label>
                    <input type="text" name="education" class="glass-input">
                </div>
                <div class="input-group">
                    <label>Religion</label>
                    <input type="text" name="religion" class="glass-input" value="Islam">
                </div>
                <div class="input-group">
                    <label>Home District</label>
                    <input type="text" name="home_district" class="glass-input" required>
                </div>
                <div class="input-group full-width">
                    <label>Previous Experience / References</label>
                    <textarea name="prev_experience_ref" class="glass-input" style="height: 80px;"></textarea>
                </div>
                <div class="input-group full-width">
                    <label>Government Relative Details (if any)</label>
                    <input type="text" name="gov_relative_details" class="glass-input">
                </div>
            </div>
        </div>

        <!-- SECTION 2: ADDRESS & FAMILY -->
        <div class="glass-panel form-section">
            <h2 class="section-title">2. Address Details</h2>
            <div class="form-grid">
                <div class="input-group full-width">
                    <label>Permanent Address</label>
                    <input type="text" name="permanent_address" class="glass-input" required>
                </div>
                <div class="input-group full-width">
                    <label>Temporary / Current Address</label>
                    <input type="text" name="temporary_address" class="glass-input" required>
                </div>
            </div>
        </div>

        <!-- SECTION 3: HEIR DETAILS -->
        <div class="glass-panel form-section">
            <h2 class="section-title">3. Next of Kin / Heir Details</h2>
            <div class="form-grid">
                <div class="input-group">
                    <label>Heir Name</label>
                    <input type="text" name="heir_name" class="glass-input" required>
                </div>
                <div class="input-group">
                    <label>Heir Phone</label>
                    <input type="text" name="heir_phone" class="glass-input" required>
                </div>
                <div class="input-group">
                    <label>Relation</label>
                    <input type="text" name="heir_relation" class="glass-input" required>
                </div>
                <div class="input-group full-width">
                    <label>Heir Address</label>
                    <input type="text" name="heir_address" class="glass-input" required>
                </div>
            </div>
        </div>

        <!-- SECTION 4: MILITARY BACKGROUND -->
        <div class="glass-panel form-section">
            <h2 class="section-title">4. Military Background</h2>
            <div class="form-grid">
                <div class="input-group">
                    <label>Ex-Army Personnel?</label>
                    <div class="radio-group">
                        <label class="radio-option">
                            <input type="radio" name="is_ex_army" value="1"> Yes
                        </label>
                        <label class="radio-option">
                            <input type="radio" name="is_ex_army" value="0" checked> No
                        </label>
                    </div>
                </div>
                <div id="military-fields" class="form-grid full-width hidden">
                    <div class="input-group">
                        <label>Enrollment Date</label>
                        <input type="date" name="army_enroll_date" class="glass-input">
                    </div>
                    <div class="input-group">
                        <label>Discharge Date</label>
                        <input type="date" name="army_discharge_date" class="glass-input">
                    </div>
                </div>
            </div>
        </div>

        <!-- SECTION 5: WITNESSES -->
        <div class="glass-panel form-section">
            <h2 class="section-title">5. Witnesses (Mandatory)</h2>
            <div class="form-grid">
                <!-- Witness 1 -->
                <div class="full-width" style="margin-bottom: 15px; color: var(--text-dim); font-size: 0.8rem; font-weight: 600;">Witness 01</div>
                <div class="input-group">
                    <label>Name</label>
                    <input type="text" name="witness1_name" class="glass-input" required>
                </div>
                <div class="input-group">
                    <label>Phone</label>
                    <input type="text" name="witness1_phone" class="glass-input" required>
                </div>
                <div class="input-group">
                    <label>CNIC</label>
                    <input type="text" name="witness1_cnic" class="glass-input" required>
                </div>
                <div class="input-group">
                    <label>Address</label>
                    <input type="text" name="witness1_address" class="glass-input" required>
                </div>

                <!-- Witness 2 -->
                <div class="full-width" style="margin-top: 20px; margin-bottom: 15px; color: var(--text-dim); font-size: 0.8rem; font-weight: 600;">Witness 02</div>
                <div class="input-group">
                    <label>Name</label>
                    <input type="text" name="witness2_name" class="glass-input" required>
                </div>
                <div class="input-group">
                    <label>Phone</label>
                    <input type="text" name="witness2_phone" class="glass-input" required>
                </div>
                <div class="input-group">
                    <label>CNIC</label>
                    <input type="text" name="witness2_cnic" class="glass-input" required>
                </div>
                <div class="input-group">
                    <label>Address</label>
                    <input type="text" name="witness2_address" class="glass-input" required>
                </div>
            </div>
        </div>

        <!-- SECTION 6: COMPLIANCE -->
        <div class="glass-panel form-section">
            <h2 class="section-title">6. Compliance & Verification</h2>
            <div class="form-grid">
                <div class="input-group">
                    <label>Fingerprint Status</label>
                    <select name="fingerprint_status" class="glass-input">
                        <option value="Pending">Pending</option>
                        <option value="Enrolled">Enrolled</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Police Verification Status</label>
                    <select name="police_verification_status" class="glass-input">
                        <option value="Pending">Pending</option>
                        <option value="Verified">Verified</option>
                    </select>
                </div>
                <div class="input-group">
                    <label>Police Verification No.</label>
                    <input type="text" name="police_verification_no" class="glass-input">
                </div>
                <div class="input-group">
                    <label>Special Branch Status</label>
                    <select name="special_branch_status" class="glass-input">
                        <option value="Pending">Pending</option>
                        <option value="Verified">Verified</option>
                    </select>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 20px;">
            <button type="button" class="btn-authorize" onclick="window.location.href='manage_guards.php'" style="border-color: var(--text-dim); color: var(--text-dim); max-width: 200px;">Cancel</button>
            <button type="submit" id="submit-btn" class="btn-authorize" style="max-width: 300px;">Enroll Guard</button>
        </div>
    </form>
</div>

<script>
    // 1. AGE VALIDATION
    function calculateAge(dob) {
        const birthday = new Date(dob);
        const ageDifMs = Date.now() - birthday.getTime();
        const ageDate = new Date(ageDifMs);
        return Math.abs(ageDate.getUTCFullYear() - 1970);
    }

    document.getElementById('dob').addEventListener('change', function() {
        const age = calculateAge(this.value);
        const warning = document.getElementById('age-warning');
        const submitBtn = document.getElementById('submit-btn');
        if (age < 18) {
            warning.classList.remove('hidden');
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.5';
            submitBtn.style.cursor = 'not-allowed';
        } else {
            warning.classList.add('hidden');
            submitBtn.disabled = false;
            submitBtn.style.opacity = '1';
            submitBtn.style.cursor = 'pointer';
        }
    });

    // 2. CNIC FORMAT VALIDATION (35201-XXXXXXX-X)
    document.getElementById('cnic').addEventListener('input', function() {
        const pattern = /^\d{5}-\d{7}-\d{1}$/;
        const error = document.getElementById('cnic-error');
        if (!pattern.test(this.value) && this.value !== '') {
            error.classList.remove('hidden');
        } else {
            error.classList.add('hidden');
        }
    });

    // 3. MILITARY TOGGLE
    document.querySelectorAll('input[name="is_ex_army"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const militaryFields = document.getElementById('military-fields');
            if (this.value === '1') {
                militaryFields.classList.remove('hidden');
            } else {
                militaryFields.classList.add('hidden');
            }
        });
    });

    // 4. FORM SUBMISSION (AJAX)
    document.getElementById('guard-enrollment-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const submitBtn = document.getElementById('submit-btn');
        
        // Final CNIC Check before send
        const cnic = document.getElementById('cnic').value;
        const cnicPattern = /^\d{5}-\d{7}-\d{1}$/;
        if (!cnicPattern.test(cnic)) {
            alert('CRITICAL ERROR: Invalid CNIC format. Please follow 35201-XXXXXXX-X');
            return;
        }

        submitBtn.innerText = 'Processing...';
        submitBtn.disabled = true;

        fetch('add_guard.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                window.location.href = data.redirect;
            } else if (data.status === 'duplicate') {
                alert(data.message);
                submitBtn.innerText = 'Enroll Guard';
                submitBtn.disabled = false;
            } else {
                alert('ERROR: ' + data.message);
                submitBtn.innerText = 'Enroll Guard';
                submitBtn.disabled = false;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('CRITICAL SYSTEM FAILURE: Could not connect to enrollment engine.');
            submitBtn.innerText = 'Enroll Guard';
            submitBtn.disabled = false;
        });
    });
</script>

<?php include '../includes/footer.php'; ?>
