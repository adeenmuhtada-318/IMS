<?php
/**
 * GUARD REGISTRATION TERMINAL (BHARTI FORM)
 * Specialized high-stakes personnel enrollment interface.
 */
session_start();

// Security Boundary: Ensure operator session is valid
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    header("Location: ../login.php");
    exit();
}

require_once '../includes/connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Guard Registration | Fast Security IMS</title>
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="DarkMode">

    <div id="MainLayoutWrapper">
        
        <!-- SIDEBAR NAVIGATION PANEL -->
        <aside id="LeftSidebarPanel">
            <div class="SidebarBrandingArea">
                <div class="BrandingTitle">FAST SECURITY IMS</div>
                <button id="SidebarToggleAction">
                    <i class="fa-solid fa-bars"></i>
                </button>
            </div>

            <nav class="NavigationLinkList">
                <div class="NavigationItem">
                    <a href="../dashboard.php" class="NavigationAnchor">
                        <span class="MenuIconNode">📊</span>
                        <span class="MenuTextLabel">Dashboard</span>
                    </a>
                </div>
                <div class="NavigationItem">
                    <a href="onboarding.php" class="NavigationAnchor ActiveMenuItem">
                        <span class="MenuIconNode">👥</span>
                        <span class="MenuTextLabel">Human Resource Portal</span>
                    </a>
                </div>
            </nav>

            <div class="UserStatusComponent">
                <div class="OperatorAccountHeader">Operator Account</div>
                <span class="SystemActiveFlag">SYSTEM ACTIVE</span>
            </div>
        </aside>

        <!-- MAIN WORKSPACE VIEWPORT -->
        <main id="RightSideViewport">
            
            <div class="ThemeModeToggle" id="ThemeToggleBtn">
                <i class="fa-solid fa-circle-half-stroke"></i>
                <span>Switch Theme</span>
            </div>

            <div class="PortalIdentityBlock">
                <h1 class="HubTitleHeading">Guard Registration Terminal</h1>
                <p class="HubSubText">(Bharti Form) System-wide personnel enrollment and verification gateway.</p>
            </div>

            <!-- MAIN DATA ENTRY FORM -->
            <form id="BhartiWizardForm" method="POST" action="process_registration.php">
                
                <!-- CLUSTER 1: BASIC IDENTITY & PROFILE -->
                <div class="FormClusterCard">
                    <h3 class="SectionTitle">Basic Identity and Profile</h3>
                    <div class="FormGrid">
                        <div class="InputGroup">
                            <label class="InputLabel">Guard Number</label>
                            <input type="text" name="guard_no" class="ModernInput" placeholder="Enter assigned number" required>
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">Date of Joining</label>
                            <input type="date" name="joining_date" class="ModernInput" required>
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">Full Name</label>
                            <input type="text" name="full_name" class="ModernInput" placeholder="As per CNIC" required>
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">Father Name</label>
                            <input type="text" name="father_name" class="ModernInput" required>
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">CNIC Number</label>
                            <input type="text" name="cnic" class="ModernInput" placeholder="00000-0000000-0" required>
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">Date of Birth</label>
                            <input type="date" name="dob" class="ModernInput" required>
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">Phone Number</label>
                            <input type="text" name="phone_number" class="ModernInput" required>
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">Blood Group</label>
                            <select name="blood_group" class="ModernInput">
                                <option value="A+">A+</option><option value="A-">A-</option>
                                <option value="B+">B+</option><option value="B-">B-</option>
                                <option value="O+">O+</option><option value="O-">O-</option>
                                <option value="AB+">AB+</option><option value="AB-">AB-</option>
                            </select>
                        </div>
                    </div>
                    <div class="FormGrid" style="margin-top: 24px; grid-template-columns: 1fr 1fr;">
                        <div class="InputGroup">
                            <label class="InputLabel">Current Living Address</label>
                            <textarea name="temporary_address" class="ModernInput" style="height: 100px; resize: none;"></textarea>
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">Home Village Address</label>
                            <textarea name="permanent_address" class="ModernInput" style="height: 100px; resize: none;"></textarea>
                        </div>
                    </div>
                </div>

                <!-- CLUSTER 2: SERVICE HISTORY & GOVERNMENT RECORD -->
                <div class="FormClusterCard">
                    <h3 class="SectionTitle">Army Record and References</h3>
                    <div style="margin-bottom: 24px; padding: 20px; background: rgba(0, 240, 255, 0.05); border-radius: 12px; border: 1px solid rgba(0, 240, 255, 0.1);">
                        <label style="display: flex; align-items: center; cursor: pointer; color: var(--VoltCyan); font-weight: 600; gap: 15px;">
                            <input type="checkbox" name="is_ex_army" id="ExArmyToggle" class="CustomCheckboxNode"> Was this person in the Army? (Check if Yes)
                        </label>
                    </div>
                    
                    <div id="ArmyDetailsNode" style="display: none; margin-bottom: 32px;">
                        <div class="FormGrid">
                            <div class="InputGroup">
                                <label class="InputLabel">Army Joining Date</label>
                                <input type="date" name="army_joining_date" class="ModernInput">
                            </div>
                            <div class="InputGroup">
                                <label class="InputLabel">Army Leaving Date</label>
                                <input type="date" name="army_discharge_date" class="ModernInput">
                            </div>
                            <div class="InputGroup">
                                <label class="InputLabel">Reason for Leaving Army</label>
                                <input type="text" name="army_discharge_reason" class="ModernInput" placeholder="e.g. Retirement">
                            </div>
                        </div>
                    </div>

                    <h4 class="InputLabel" style="margin-bottom: 16px; color: var(--TextPrimary);">Relatives in Government Service</h4>
                    <div class="FormGrid">
                        <div class="InputGroup">
                            <label class="InputLabel">Relative Name</label>
                            <input type="text" name="govt_relative_name" class="ModernInput">
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">Designation</label>
                            <input type="text" name="govt_relative_designation" class="ModernInput">
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">Department</label>
                            <input type="text" name="govt_relative_department" class="ModernInput">
                        </div>
                    </div>
                </div>

                <!-- CLUSTER 3: EMERGENCY CONTACTS & WITNESSES -->
                <div class="FormClusterCard">
                    <h3 class="SectionTitle">Emergency Contact & Witnesses</h3>
                    <div class="FormGrid" style="grid-template-columns: 1fr 1fr;">
                        <div class="InputGroup">
                            <label class="InputLabel">Next of Kin Mobile</label>
                            <input type="text" name="next_of_kin_mobile" class="ModernInput" required>
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">Next of Kin Name and Address</label>
                            <textarea name="next_of_kin_name_address" class="ModernInput" style="height: 80px; resize: none;" required></textarea>
                        </div>
                    </div>
                    
                    <div style="margin-top: 32px; border-top: 1px solid var(--BorderDeep); padding-top: 24px;">
                        <h4 class="InputLabel" style="margin-bottom: 16px; color: var(--TextPrimary);">Trustworthy Witness 1</h4>
                        <div class="FormGrid">
                            <div class="InputGroup">
                                <label class="InputLabel">Witness Name</label>
                                <input type="text" name="witness_1_name" class="ModernInput" required>
                            </div>
                            <div class="InputGroup">
                                <label class="InputLabel">Witness Phone</label>
                                <input type="text" name="witness_1_phone" class="ModernInput" required>
                            </div>
                            <div class="InputGroup">
                                <label class="InputLabel">Witness Address</label>
                                <input type="text" name="witness_1_address" class="ModernInput" required>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 24px;">
                        <h4 class="InputLabel" style="margin-bottom: 16px; color: var(--TextPrimary);">Trustworthy Witness 2</h4>
                        <div class="FormGrid">
                            <div class="InputGroup">
                                <label class="InputLabel">Witness Name</label>
                                <input type="text" name="witness_2_name" class="ModernInput" required>
                            </div>
                            <div class="InputGroup">
                                <label class="InputLabel">Witness Phone</label>
                                <input type="text" name="witness_2_phone" class="ModernInput" required>
                            </div>
                            <div class="InputGroup">
                                <label class="InputLabel">Witness Address</label>
                                <input type="text" name="witness_2_address" class="ModernInput" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CLUSTER 4: SALARY & OPERATIONAL KIT -->
                <div class="FormClusterCard">
                    <h3 class="SectionTitle">Salary and Joining Kit</h3>
                    <div class="FormGrid" style="grid-template-columns: 1fr 1fr;">
                        <div class="InputGroup">
                            <label class="InputLabel">Starting Monthly Salary (PKR)</label>
                            <input type="number" name="base_salary" class="ModernInput" required value="25000">
                        </div>
                        <div class="InputGroup">
                            <label class="InputLabel">Police Verification Sheet Number</label>
                            <input type="text" name="police_verification_ref_no" class="ModernInput" placeholder="Pending / Verified">
                        </div>
                    </div>

                    <div style="margin-top: 32px; padding: 24px; border: 1px solid var(--VoltCyan); border-radius: 12px; background: var(--VoltGlow);">
                        <h4 class="SectionTitle" style="font-size: 1rem; margin-bottom: 16px;">Items Issued on Joining</h4>
                        <div class="FormGrid" style="grid-template-columns: repeat(3, 1fr);">
                            <label style="display: flex; align-items: center; cursor: pointer; gap: 12px;">
                                <input type="checkbox" name="kit_shirt_trousers" class="CustomCheckboxNode"> Shirt and Trousers
                            </label>
                            <label style="display: flex; align-items: center; cursor: pointer; gap: 12px;">
                                <input type="checkbox" name="kit_cap" class="CustomCheckboxNode"> Cap
                            </label>
                            <label style="display: flex; align-items: center; cursor: pointer; gap: 12px;">
                                <input type="checkbox" name="kit_belt" class="CustomCheckboxNode"> Belt
                            </label>
                            <label style="display: flex; align-items: center; cursor: pointer; gap: 12px;">
                                <input type="checkbox" name="kit_boots" class="CustomCheckboxNode"> Boots
                            </label>
                            <label style="display: flex; align-items: center; cursor: pointer; gap: 12px;">
                                <input type="checkbox" name="kit_jersey" class="CustomCheckboxNode"> Jersey
                            </label>
                        </div>
                    </div>
                </div>

                <!-- SUBMISSION SECTOR -->
                <div style="padding: 40px 0; text-align: right;">
                    <button type="submit" class="PrimaryActionButton" style="width: 100%; max-width: 400px;">
                        FINISH_AND_SAVE_RECRUIT <i class="fa-solid fa-save" style="margin-left: 12px;"></i>
                    </button>
                </div>

            </form>
        </main>
    </div>

    <script>
        // SIDEBAR TOGGLE MECHANISM
        const toggleBtn = document.getElementById('SidebarToggleAction');
        const mainWrapper = document.getElementById('MainLayoutWrapper');

        toggleBtn.addEventListener('click', () => {
            mainWrapper.classList.toggle('SidebarCollapsed');
        });

        // THEME ENGINE
        const themeBtn = document.getElementById('ThemeToggleBtn');
        const body = document.body;

        themeBtn.addEventListener('click', () => {
            if (body.classList.contains('DarkMode')) {
                body.classList.remove('DarkMode');
                body.classList.add('LightMode');
                localStorage.setItem('ThemePreference', 'LightMode');
            } else {
                body.classList.remove('LightMode');
                body.classList.add('DarkMode');
                localStorage.setItem('ThemePreference', 'DarkMode');
            }
        });

        // INITIALIZE THEME
        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('ThemePreference');
            if (savedTheme === 'LightMode') {
                body.classList.remove('DarkMode');
                body.classList.add('LightMode');
            }
        });

        // ARMY STATUS TOGGLE LOGIC
        const armyToggle = document.getElementById('ExArmyToggle');
        const armyNode = document.getElementById('ArmyDetailsNode');
        armyToggle.addEventListener('change', () => {
            armyNode.style.display = armyToggle.checked ? 'block' : 'none';
        });
    </script>
</body>
</html>
