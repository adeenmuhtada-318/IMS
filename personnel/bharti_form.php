<?php
/**
 * BHARTI FORM - High-Usability Recruitment Interface
 * Optimized for local data entry operators.
 */
session_start();

if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

require_once '../includes/connection.php';
include '../includes/header.php';
?>

<div class="dashboard-viewport">
    <header class="tactical-header">
        <div>
            <h1 style="letter-spacing: 2px; color: var(--text-primary); font-weight: 800;">FAST SECURITY SERVICES</h1>
            <p style="color: var(--accent-cyan); font-size: 0.75rem; letter-spacing: 4px; font-weight: 600;">BHARTI_DEPARTMENT_PORTAL</p>
        </div>
    </header>

    <!-- NAVIGATION WIZARD TABS -->
    <div class="asset-tabs" style="margin-bottom: 30px; border-bottom: 1px solid var(--border-frost);">
        <button class="tab-btn active" id="tab-step-1" onclick="this_navigate_wizard(1)">1. Profile Details</button>
        <button class="tab-btn" id="tab-step-2" onclick="this_navigate_wizard(2)">2. Experience & Background</button>
        <button class="tab-btn" id="tab-step-3" onclick="this_navigate_wizard(3)">3. Family & Witnesses</button>
        <button class="tab-btn" id="tab-step-4" onclick="this_navigate_wizard(4)">4. Salary & Joining Kit</button>
    </div>

    <!-- MAIN DATA ENTRY FORM -->
    <form id="bharti-wizard-form">
        
        <!-- TAB 01: PROFILE REGISTRY -->
        <section id="wizard-step-1" class="dynamic-field-wrapper glass-panel" style="padding: 40px;">
            <h3 style="color: var(--accent-cyan); margin-bottom: 25px;">Basic Identity and Profile</h3>
            <div class="form-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px;">
                <div class="input-group">
                    <label>Guard Number</label>
                    <input type="text" name="guard_no" class="glass-input" placeholder="Enter assigned number" required>
                </div>
                <div class="input-group">
                    <label>Date of Joining</label>
                    <input type="date" name="joining_date" class="glass-input" required>
                </div>
                <div class="input-group">
                    <label>Full Name</label>
                    <input type="text" name="full_name" class="glass-input" placeholder="As per CNIC" required>
                </div>
                <div class="input-group">
                    <label>Father Name</label>
                    <input type="text" name="father_name" class="glass-input" required>
                </div>
                <div class="input-group">
                    <label>Caste</label>
                    <input type="text" name="caste" class="glass-input">
                </div>
                <div class="input-group">
                    <label>Education</label>
                    <input type="text" name="education" class="glass-input" placeholder="e.g. Matric, FA">
                </div>
                <div class="input-group">
                    <label>Religion</label>
                    <input type="text" name="religion" class="glass-input" value="Islam">
                </div>
                <div class="input-group">
                    <label>CNIC Number</label>
                    <input type="text" name="cnic" class="glass-input" placeholder="00000-0000000-0" required>
                </div>
                <div class="input-group">
                    <label>Date of Birth</label>
                    <input type="date" name="dob" class="glass-input" required>
                </div>
                <div class="input-group">
                    <label>District</label>
                    <input type="text" name="district" class="glass-input">
                </div>
                <div class="input-group">
                    <label>Phone Number</label>
                    <input type="text" name="phone_number" class="glass-input" required>
                </div>
                <div class="input-group">
                    <label>Blood Group</label>
                    <select name="blood_group" class="glass-input">
                        <option value="A+">A+</option><option value="A-">A-</option>
                        <option value="B+">B+</option><option value="B-">B-</option>
                        <option value="O+">O+</option><option value="O-">O-</option>
                        <option value="AB+">AB+</option><option value="AB-">AB-</option>
                    </select>
                </div>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                <div class="input-group">
                    <label>Current Living Address</label>
                    <textarea name="temporary_address" class="glass-input" style="height: 80px; resize: none;"></textarea>
                </div>
                <div class="input-group">
                    <label>Home Village Address</label>
                    <textarea name="permanent_address" class="glass-input" style="height: 80px; resize: none;"></textarea>
                </div>
            </div>
            <div style="margin-top: 30px; text-align: right;">
                <button type="button" class="btn-authorize" onclick="this_navigate_wizard(2)" style="width: 250px;">GO_TO_NEXT_STEP ></button>
            </div>
        </section>

        <!-- TAB 02: SERVICE HISTORY -->
        <section id="wizard-step-2" class="dynamic-field-wrapper glass-panel" style="display: none; opacity: 0; padding: 40px;">
            <h3 style="color: var(--accent-cyan); margin-bottom: 25px;">Army Record and References</h3>
            
            <div class="glass-panel" style="background: rgba(255,255,255,0.02); padding: 25px; margin-bottom: 30px;">
                <label style="display: flex; align-items: center; cursor: pointer; color: var(--accent-cyan); font-size: 0.9rem;">
                    <input type="checkbox" id="ex-army-toggle" name="is_ex_army" style="margin-right: 15px; width: 18px; height: 18px;"> Was this person in the Army? (Check if Yes)
                </label>
                
                <div id="army-details-node" style="display: none; margin-top: 25px; border-top: 1px solid var(--border-frost); padding-top: 25px;">
                    <div class="form-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                        <div class="input-group">
                            <label>Army Joining Date</label>
                            <input type="date" name="army_joining_date" class="glass-input">
                        </div>
                        <div class="input-group">
                            <label>Army Leaving Date</label>
                            <input type="date" name="army_discharge_date" class="glass-input">
                        </div>
                        <div class="input-group">
                            <label>Reason for Leaving Army</label>
                            <input type="text" name="army_discharge_reason" class="glass-input" placeholder="e.g. Retirement">
                        </div>
                    </div>
                </div>
            </div>

            <h4 style="color: var(--text-primary); margin-bottom: 15px; font-size: 0.85rem;">Relatives in Government Service</h4>
            <div class="form-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                <div class="input-group">
                    <label>Relative Name</label>
                    <input type="text" name="govt_relative_name" class="glass-input">
                </div>
                <div class="input-group">
                    <label>Designation</label>
                    <input type="text" name="govt_relative_designation" class="glass-input">
                </div>
                <div class="input-group">
                    <label>Department</label>
                    <input type="text" name="govt_relative_department" class="glass-input">
                </div>
            </div>

            <div class="input-group" style="margin-top: 25px;">
                <label>Past Experience and References</label>
                <textarea name="previous_experience_ref" class="glass-input" style="height: 100px; resize: none;" placeholder="Details of previous jobs..."></textarea>
            </div>

            <div style="margin-top: 30px; display: flex; justify-content: space-between;">
                <button type="button" class="btn-authorize" onclick="this_navigate_wizard(1)" style="width: 200px; border-color: var(--alert-orange); color: var(--alert-orange);">< GO_BACK</button>
                <button type="button" class="btn-authorize" onclick="this_navigate_wizard(3)" style="width: 250px;">GO_TO_NEXT_STEP ></button>
            </div>
        </section>

        <!-- TAB 03: WITNESSES -->
        <section id="wizard-step-3" class="dynamic-field-wrapper glass-panel" style="display: none; opacity: 0; padding: 40px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
                <div>
                    <h3 style="color: var(--accent-cyan); margin-bottom: 25px;">Emergency Contact (Kin)</h3>
                    <div class="input-group">
                        <label>Next of Kin Mobile</label>
                        <input type="text" name="next_of_kin_mobile" class="glass-input" required>
                    </div>
                    <div class="input-group" style="margin-top: 20px;">
                        <label>Next of Kin Name and Address</label>
                        <textarea name="next_of_kin_name_address" class="glass-input" style="height: 120px; resize: none;" required></textarea>
                    </div>
                </div>

                <div>
                    <h3 style="color: var(--accent-cyan); margin-bottom: 25px;">Trustworthy Witness 1</h3>
                    <div class="input-group">
                        <label>Witness Full Name</label>
                        <input type="text" name="witness_1_name" class="glass-input" required>
                    </div>
                    <div class="input-group" style="margin-top: 15px;">
                        <label>Witness Mobile Number</label>
                        <input type="text" name="witness_1_phone" class="glass-input" required>
                    </div>
                    <div class="input-group" style="margin-top: 15px;">
                        <label>Witness Complete Address</label>
                        <textarea name="witness_1_address" class="glass-input" style="height: 80px; resize: none;" required></textarea>
                    </div>
                </div>
            </div>

            <div style="margin-top: 40px; border-top: 1px solid var(--border-frost); padding-top: 30px;">
                <h3 style="color: var(--accent-cyan); margin-bottom: 25px;">Trustworthy Witness 2</h3>
                <div class="form-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                    <div class="input-group">
                        <label>Witness Full Name</label>
                        <input type="text" name="witness_2_name" class="glass-input" required>
                    </div>
                    <div class="input-group">
                        <label>Witness Mobile Number</label>
                        <input type="text" name="witness_2_phone" class="glass-input" required>
                    </div>
                    <div class="input-group">
                        <label>Witness Complete Address</label>
                        <input type="text" name="witness_2_address" class="glass-input" required>
                    </div>
                </div>
            </div>

            <div style="margin-top: 30px; display: flex; justify-content: space-between;">
                <button type="button" class="btn-authorize" onclick="this_navigate_wizard(2)" style="width: 200px; border-color: var(--alert-orange); color: var(--alert-orange);">< GO_BACK</button>
                <button type="button" class="btn-authorize" onclick="this_navigate_wizard(4)" style="width: 250px;">GO_TO_NEXT_STEP ></button>
            </div>
        </section>

        <!-- TAB 04: SALARY & KIT -->
        <section id="wizard-step-4" class="dynamic-field-wrapper glass-panel" style="display: none; opacity: 0; padding: 40px;">
            <h3 style="color: var(--accent-cyan); margin-bottom: 25px;">Salary and Joining Kit</h3>
            
            <div class="form-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <div class="input-group">
                    <label>Starting Monthly Salary (PKR)</label>
                    <input type="number" name="base_salary" class="glass-input" required value="25000">
                </div>
                <div class="input-group">
                    <label>Police Verification Sheet Number</label>
                    <input type="text" name="police_verification_ref_no" class="glass-input" placeholder="Pending / Verified">
                </div>
            </div>

            <div class="glass-panel" style="margin-top: 40px; padding: 30px; border: 1px solid var(--accent-cyan);">
                <h4 style="color: var(--accent-cyan); margin-bottom: 20px;">Items Given to Guard on Joining</h4>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="kit_shirt_trousers" style="margin-right: 12px; width: 18px; height: 18px;"> Shirt and Trousers
                    </label>
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="kit_cap" style="margin-right: 12px; width: 18px; height: 18px;"> Cap
                    </label>
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="kit_belt" style="margin-right: 12px; width: 18px; height: 18px;"> Belt
                    </label>
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="kit_boots" style="margin-right: 12px; width: 18px; height: 18px;"> Boots
                    </label>
                    <label style="display: flex; align-items: center; cursor: pointer;">
                        <input type="checkbox" name="kit_jersey" style="margin-right: 12px; width: 18px; height: 18px;"> Jersey
                    </label>
                </div>
            </div>

            <div style="margin-top: 50px; display: flex; justify-content: space-between;">
                <button type="button" class="btn-authorize" onclick="this_navigate_wizard(3)" style="width: 200px; border-color: var(--alert-orange); color: var(--alert-orange);">< GO_BACK</button>
                <button type="submit" class="btn-authorize" style="width: 350px;">FINISH_AND_SAVE_RECRUIT</button>
            </div>
        </section>

    </form>
</div>

<script src="../assets/js/guard_controller.js"></script>
<?php include '../includes/footer.php'; ?>
